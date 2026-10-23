<?php
require_once __DIR__ . '/helpers.php';
admin_start('Dashboard');
$tables = [
 'Patients' => ['users','patients.php','fa-users'],
 'Doctors' => ['doctors','doctors.php','fa-user-doctor'],
 'Appointments' => ['appointments','appointments.php','fa-calendar-check'],
 'Medicines' => [table_exists($conn,'pharmacy_products') ? 'pharmacy_products' : 'medicines','medicines.php','fa-pills'],
 'Pharmacy Orders' => ['pharmacy_orders','pharmacy_orders.php','fa-prescription-bottle-medical'],
 'Lab Bookings' => ['lab_bookings','lab_bookings.php','fa-flask'],
];
?>
<section class="panel"><h1>Admin Dashboard</h1><p class="muted">Manage MediCare+ website data from one place.</p><div class="cards">
    
<?php foreach($tables as $label=>$info):$count='—';if(table_exists($conn,$info[0])){$res=$conn->query("SELECT COUNT(*) AS total FROM `{$info[0]}`");if($res)$count=(int)$res->fetch_assoc()['total'];}?>
<div class="card"><i class="fa-solid <?=h($info[2])?>"></i><p><?=h($label)?></p><strong><?=h($count)?></strong><a href="<?=h($info[1])?>">Manage →</a></div>
<?php endforeach;$msgCount='—';$msgTable=table_exists($conn,'contacts')?'contacts':(table_exists($conn,'contact_messages')?'contact_messages':null);if($msgTable){$res=$conn->query("SELECT COUNT(*) AS total FROM `$msgTable`");if($res)$msgCount=(int)$res->fetch_assoc()['total'];}?>
<div class="card"><i class="fa-solid fa-envelope"></i><p>Contact Messages</p><strong><?=h($msgCount)?></strong><a href="messages.php">View messages →</a></div>
</div></section>
<section class="panel"><h2>Quick Actions</h2><div class="actions"><a class="btn" href="doctors.php">Add Doctor</a><a class="btn" href="medicines.php">Manage Stock</a><a class="btn" href="appointments.php">Review Appointments</a><a class="btn" href="pharmacy_orders.php">Review Orders</a></div></section>
<?php admin_end(); ?>