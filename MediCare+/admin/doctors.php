<?php
require_once __DIR__ . '/helpers.php';
admin_start('Doctors');
if (!table_exists($conn,'doctors')) { flash_message('The doctors table was not found. Import database_backend_setup.sql and check your database.',true); admin_end(); exit; }
$cols=columns_for($conn,'doctors'); $message='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 $action=$_POST['action']??'';
 if($action==='save'){
  $id=(int)($_POST['id']??0); $name=trim($_POST['name']??''); $department=trim($_POST['department']??'');
  if($name===''||$department==='') $message='Enter the doctor name and department.';
  elseif($id>0){
   $st=$conn->prepare('UPDATE doctors SET name=?, department=? WHERE id=?');
   if($st){$st->bind_param('ssi',$name,$department,$id);$st->execute();$st->close();$message='Doctor updated.';} else $message='Could not update. Check that the doctors table has name and department columns.';
  } else {
   $st=$conn->prepare('INSERT INTO doctors (name, department) VALUES (?, ?)');
   if($st){$st->bind_param('ss',$name,$department);if($st->execute())$message='Doctor added.';else $message='Could not add doctor. Check duplicate or required fields.';$st->close();} else $message='Could not add. Check that the doctors table has name and department columns.';
  }
 } elseif($action==='delete' && in_array('id',$cols,true)){
  $id=(int)($_POST['id']??0); $st=$conn->prepare('DELETE FROM doctors WHERE id=?');
  if($st){$st->bind_param('i',$id);if($st->execute())$message='Doctor deleted.';else $message='Could not delete this doctor. Existing appointments may refer to them.';$st->close();}
 }
}
$edit=null;if(isset($_GET['edit'])){$id=(int)$_GET['edit'];$st=$conn->prepare('SELECT * FROM doctors WHERE id=?');$st->bind_param('i',$id);$st->execute();$edit=$st->get_result()->fetch_assoc();$st->close();}
$rows=fetch_all_rows($conn,'SELECT * FROM doctors ORDER BY id DESC');
?>
<section class="panel"><h1>Manage Doctors</h1><?php if($message)flash_message($message,str_contains(strtolower($message),'could not')||str_contains(strtolower($message),'enter')); ?>
<form method="post" class="formgrid"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?=h($edit['id']??0)?>"><label>Doctor name<input name="name" required value="<?=h($edit['name']??'')?>"></label><label>Department<input name="department" required placeholder="e.g. Cardiology" value="<?=h($edit['department']??'')?>"></label><div style="align-self:end"><button class="btn" type="submit"><?= $edit?'Update Doctor':'Add Doctor' ?></button> <?php if($edit): ?><a class="btn" href="doctors.php">Cancel</a><?php endif; ?></div></form><p class="muted small">This form uses the existing name and department columns. If your table requires other fields, add them to the form to match your schema.</p></section>
<section class="panel"><h2>Doctor List</h2><?php if(!$rows): ?><p>No doctors found.</p><?php else: ?><div class="tablewrap"><table><thead><tr><?php
$displayCols = ['id', 'name', 'department'];

foreach ($displayCols as $c) {
    if (in_array($c, $cols, true)) {
        echo '<th>' . h(ucwords($c)) . '</th>';
    }
}
?><th>Actions</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><?php
foreach ($displayCols as $c) {
    if (in_array($c, $cols, true)) {
        echo '<td>' . h($r[$c] ?? '') . '</td>';
    }
}
?><td class="actions"><a class="btn" href="?edit=<?= (int)$r['id'] ?>">Edit</a><form method="post" onsubmit="return confirm('Delete this doctor?');"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button class="btn danger" type="submit">Delete</button></form></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
<?php admin_end(); ?>