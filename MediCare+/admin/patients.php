
<?php
require_once __DIR__ . '/helpers.php';
admin_start('Patients');

if (!table_exists($conn, 'users')) {
    flash_message("Users table not found.", true);
    admin_end();
    exit;
}

$cols = columns_for($conn, 'users');
$message = '';

/* Update patient details */
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && ($_POST['action'] ?? '') === 'update') {

    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');

    if ($id < 1 || $name === '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        $mobile === '') {
        $message = "Please enter valid patient details.";
    } else {
        $stmt = $conn->prepare(
            "UPDATE users SET name=?, email=?, mobile=?
             WHERE id=? AND role='patient'"
        );

        $stmt->bind_param('sssi', $name, $email, $mobile, $id);

        if ($stmt->execute() && $stmt->affected_rows >= 0) {
            $message = "Patient details saved successfully.";
        } else {
            $message = "Could not update. Check whether the email or mobile is already used.";
        }

        $stmt->close();
    }
}

/* Find patient to edit */
$editPatient = null;

if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];

    $stmt = $conn->prepare(
        "SELECT id, name, email, mobile
         FROM users WHERE id=? AND role='patient'"
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $editPatient = $result->fetch_assoc();
    $stmt->close();
}

$rows = fetch_all_rows(
    $conn,
    "SELECT id, name, email, mobile, role, created_at
     FROM users WHERE role='patient' ORDER BY id DESC"
);
?>

<section class="panel">
    <h1>Manage Patients</h1>
    <p class="muted">View and update registered patient accounts.</p>

    <?php if ($message !== ''): ?>
        <p><?= h($message) ?></p>
    <?php endif; ?>

    <?php if ($editPatient): ?>
        <h2>Edit Patient</h2>

        <form method="post">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id"
                   value="<?= (int)$editPatient['id'] ?>">

            <p>
                <label>Patient Name</label><br>
                <input name="name" required maxlength="120"
                       value="<?= h($editPatient['name']) ?>">
            </p>

            <p>
                <label>Email</label><br>
                <input type="email" name="email" required maxlength="150"
                       value="<?= h($editPatient['email']) ?>">
            </p>

            <p>
                <label>Mobile</label><br>
                <input name="mobile" required maxlength="10"
                       pattern="[0-9]{10}"
                       value="<?= h($editPatient['mobile']) ?>">
            </p>

            <button type="submit">Save Changes</button>
            <a href="patients.php">Cancel</a>
        </form>

        <hr>
    <?php endif; ?>

    <?php if (!$rows): ?>
        <p>No patient records found.</p>
    <?php else: ?>
        <div class="tablewrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Registered</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><?= (int)$r['id'] ?></td>
                            <td><?= h($r['name']) ?></td>
                            <td><?= h($r['email']) ?></td>
                            <td><?= h($r['mobile']) ?></td>
                            <td><?= h($r['created_at']) ?></td>
                            <td>
                                <a href="patients.php?edit=<?= (int)$r['id'] ?>">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php admin_end(); ?>
