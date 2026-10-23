<?php
require_once __DIR__ . '/helpers.php'; admin_start('Laboratory Bookings');
if(!table_exists($conn,'lab_bookings')){flash_message('The lab_bookings table was not found. Import database_backend_setup.sql.',true);admin_end();exit;}
$cols=columns_for($conn,'lab_bookings');$message='';
if($_SERVER['REQUEST_METHOD']==='POST'&&in_array('status',$cols,true)){
 $id=(int)($_POST['id']??0);$status=$_POST['status']??'Pending';
 if(in_array($status,['Pending','Confirmed','Completed','Cancelled'],true)){$st=$conn->prepare('UPDATE lab_bookings SET status=? WHERE id=?');$st->bind_param('si',$status,$id);$st->execute();$st->close();$message='Laboratory booking status updated.';}
}
$rows=fetch_all_rows($conn,'SELECT * FROM lab_bookings ORDER BY id DESC');
?><section class="panel"><h1>Laboratory Bookings</h1><?php if($message)flash_message($message);if(!$rows):?><p>No laboratory bookings yet.</p><?php else:?><div class="tablewrap"><table><thead><tr><?php foreach($cols as $c)echo '<th>'.h(ucwords(str_replace('_',' ',$c))).'</th>';if(in_array('status',$cols,true))echo '<th>Update</th>';?></tr></thead><tbody><?php foreach($rows as $r):?><tr><?php foreach($cols as $c)echo '<td>'.h($r[$c]??'').'</td>';if(in_array('status',$cols,true)):?><td><form method="post"><input type="hidden" name="id" value="<?= (int)$r['id']?>"><select name="status"><?php foreach(['Pending','Confirmed','Completed','Cancelled'] as $s):?><option value="<?=h($s)?>" <?=($r['status']===$s?'selected':'')?>><?=h($s)?></option><?php endforeach;?></select><button class="btn">Save</button></form></td><?php endif;?></tr><?php endforeach;?></tbody></table></div><?php endif;?></section><?php admin_end();?>