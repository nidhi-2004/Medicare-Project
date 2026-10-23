<?php
require_once __DIR__ . '/helpers.php'; admin_start('Appointments');
if(!table_exists($conn,'appointments')){flash_message('The appointments table was not found. Check database setup.',true);admin_end();exit;}
$cols=columns_for($conn,'appointments');$message='';
if($_SERVER['REQUEST_METHOD']==='POST' && in_array('status',$cols,true)){
 $id=(int)($_POST['id']??0);$status=$_POST['status']??'Pending';
 if(in_array($status,['Pending','Approved','Completed','Cancelled'],true)){
  $st=$conn->prepare('UPDATE appointments SET status=? WHERE id=?');$st->bind_param('si',$status,$id);$st->execute();$st->close();$message='Appointment status updated.';
 }
}
$rows=fetch_all_rows($conn,'SELECT * FROM appointments ORDER BY id DESC');
?>
<section class="panel"><h1>Manage Appointments</h1><p class="muted">Review appointments submitted from the website and update their status.</p><?php if($message)flash_message($message);if(!in_array('status',$cols,true))flash_message('Your appointments table has no status column. Run the supplied setup SQL to add it.',true);if(!$rows):?><p>No appointments found yet.</p><?php else:?><div class="tablewrap"><table><thead><tr><?php foreach($cols as $c)echo '<th>'.h(ucwords(str_replace('_',' ',$c))).'</th>';if(in_array('status',$cols,true))echo '<th>Update</th>';?></tr></thead><tbody><?php foreach($rows as $r):?><tr><?php foreach($cols as $c)echo '<td>'.h($r[$c]??'').'</td>';if(in_array('status',$cols,true)):?><td><form method="post"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><select name="status"><?php foreach(['Pending','Approved','Completed','Cancelled'] as $s):?><option <?=($r['status']===$s?'selected':'')?> value="<?=h($s)?>"><?=h($s)?></option><?php endforeach;?></select><button class="btn" type="submit">Save</button></form></td><?php endif;?></tr><?php endforeach;?></tbody></table></div><?php endif;?></section><?php admin_end(); ?>