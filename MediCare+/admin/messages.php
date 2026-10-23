
<?php
require_once __DIR__ . '/helpers.php';
admin_start('Messages');

$table = table_exists($conn, 'contacts')
    ? 'contacts'
    : (table_exists($conn, 'contact_messages')
        ? 'contact_messages' : null);

if (!$table) {
    flash_message('No contact message table was found.', true);
    admin_end();
    exit;
}

$cols = columns_for($conn, $table);
$message = '';

if (!in_array('id', $cols, true)) {
    flash_message('The message table has no ID column.', true);
    admin_end();
    exit;
}

// Delete a message only after confirmation.
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && ($_POST['action'] ?? '') === 'delete') {

    $id = (int)($_POST['id'] ?? 0);

    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM `$table` WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param('i', $id);
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $message = 'Message deleted successfully.';
            } else {
                $message = 'Message could not be deleted.';
            }
            $stmt->close();
        }
    }
}

// View one message.
$selected = null;
if (isset($_GET['view'])) {
    $id = (int)$_GET['view'];
    $stmt = $conn->prepare("SELECT * FROM `$table` WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $selected = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}

$rows = fetch_all_rows(
    $conn,
    "SELECT * FROM `$table` ORDER BY id DESC"
);
?>

<section class="panel">
    <h1>Contact Messages</h1>
    <p class="muted">Source table: <?= h($table) ?></p>

    <?php if ($message): ?>
        <?php flash_message($message); ?>
    <?php endif; ?>

    <?php if ($selected): ?>
        <section class="panel">
            <h2>Message Details</h2>

            <?php foreach ($cols as $col): ?>
                <p>
                    <strong>
                        <?= h(ucwords(str_replace('_', ' ', $col))) ?>:
                    </strong><br>
                    <?= nl2br(h($selected[$col] ?? '')) ?>
                </p>
            <?php endforeach; ?>

            <a class="btn" href="messages.php">Back to Messages</a>
        </section>
    <?php endif; ?>

    <?php if (!$rows): ?>
        <p>No messages received yet.</p>
    <?php else: ?>
        <div class="tablewrap">
            <table>
                <thead>
                    <tr>
                        <?php foreach ($cols as $col): ?>
                            <th><?= h(ucwords(str_replace('_', ' ', $col))) ?></th>
                        <?php endforeach; ?>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <?php foreach ($cols as $col): ?>
                                <td>
                                    <?= h($row[$col] ?? '') ?>
                                </td>
                            <?php endforeach; ?>

                            <td class="actions">
                                <a class="btn"
                                   href="?view=<?= (int)$row['id'] ?>">
                                    View Details
                                </a>

                                <form method="post"
                                      onsubmit="return confirm('Permanently delete this message?');">
                                    <input type="hidden"
                                           name="action" value="delete">
                                    <input type="hidden"
                                           name="id"
                                           value="<?= (int)$row['id'] ?>">
                                    <button class="btn danger" type="submit">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php admin_end(); ?>
