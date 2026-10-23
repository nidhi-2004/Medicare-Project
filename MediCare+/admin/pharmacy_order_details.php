<?php
require_once __DIR__ . '/helpers.php'; admin_start('Order Items');
$id=(int)($_GET['id']??0);
?><section class="panel"><h1>Order Items #<?= $id ?></h1><p><a class="btn" href="pharmacy_orders.php">Back to Orders</a></p><?php
if(!table_exists($conn,'pharmacy_order_items')) flash_message('Order items table was not found.',true);
else{$st=$conn->prepare('SELECT medicine_name,price,quantity,(price*quantity) AS subtotal FROM pharmacy_order_items WHERE order_id=?');$st->bind_param('i',$id);$st->execute();$rows=$st->get_result()->fetch_all(MYSQLI_ASSOC);$st->close();if(!$rows)echo '<p>No item details found for this order.</p>';else{echo '<div class="tablewrap"><table><thead><tr><th>Medicine</th><th>Price</th><th>Quantity</th><th>Subtotal</th></tr></thead><tbody>';foreach($rows as $r)echo '<tr><td>'.h($r['medicine_name']).'</td><td>₹'.h($r['price']).'</td><td>'.h($r['quantity']).'</td><td>₹'.h($r['subtotal']).'</td></tr>';echo '</tbody></table></div>';}}
?></section><?php admin_end();?>