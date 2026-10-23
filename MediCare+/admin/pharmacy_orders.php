<?php
require_once __DIR__ . '/helpers.php'; admin_start('Pharmacy Orders');
if(!table_exists($conn,'pharmacy_orders')){flash_message('The pharmacy_orders table was not found. Import database_backend_setup.sql.',true);admin_end();exit;}
$cols=columns_for($conn,'pharmacy_orders');$message='';
if($_SERVER['REQUEST_METHOD']==='POST'&&in_array('status',$cols,true)){
 $id=(int)($_POST['id']??0);$status=$_POST['status']??'Pending';
 if(in_array($status,['Pending','Processing','Completed','Cancelled'],true)){$st=$conn->prepare('UPDATE pharmacy_orders SET status=? WHERE id=?');$st->bind_param('si',$status,$id);$st->execute();$st->close();$message='Order status updated.';}
}
$rows=fetch_all_rows($conn,'SELECT * FROM pharmacy_orders ORDER BY id DESC');
?><section class="panel"><h1>Pharmacy Orders</h1><?php if($message)flash_message($message);if(!$rows):?><p>No pharmacy orders yet.</p><?php else:?><div class="tablewrap"><table><thead><tr><?php foreach($cols as $c)echo '<th>'.h(ucwords(str_replace('_',' ',$c))).'</th>';if(in_array('status',$cols,true))echo '<th>Update status</th>';echo '<th>Items</th>';?></tr></thead><tbody><?php foreach($rows as $r):?><tr><?php foreach($cols as $c)echo '<td>'.h($r[$c]??'').'</td>';if(in_array('status',$cols,true)):?><td><form method="post"><input type="hidden" name="id" value="<?= (int)$r['id']?>"><select name="status"><?php foreach(['Pending','Processing','Completed','Cancelled'] as $s):?><option value="<?=h($s)?>" <?=($r['status']===$s?'selected':'')?>><?=h($s)?></option><?php endforeach;?></select><button class="btn">Save</button></form></td><?php endif;?><td><a class="btn" href="pharmacy_order_details.php?id=<?= (int)$r['id']?>">View items</a></td></tr><?php endforeach;?></tbody></table></div><?php endif;?></section><?php admin_end();?>