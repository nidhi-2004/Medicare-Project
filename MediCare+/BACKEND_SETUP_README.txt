MediCare+ Backend and Admin Panel Setup
=======================================

IMPORTANT
- This is a local college-project demo. Do not use it with real patient data on a public server.
- Keep your existing database backup before running SQL.
- The SQL uses CREATE TABLE IF NOT EXISTS and does not erase existing records.

STEP 1 — Copy project
1. Extract the MediCare+ folder into C:\xampp\htdocs\
2. Your folder should be C:\xampp\htdocs\MediCare+\

STEP 2 — Start XAMPP
1. Open XAMPP Control Panel.
2. Start Apache and MySQL.

STEP 3 — Set up the database
1. Open http://localhost/phpmyadmin
2. Select the database named medicare_db. If it does not exist, create a database named medicare_db.
3. Click the SQL tab.
4. Open database_backend_setup.sql in VS Code, copy all its contents, paste into phpMyAdmin's SQL box, and click Go.
5. If your database already has tables with different columns, the CREATE TABLE IF NOT EXISTS commands will not change those existing tables. Use the actual table structure when troubleshooting.

STEP 4 — Create admin login (only if an admin account does not already exist)
1. Visit http://localhost/MediCare+/admin/create_admin.php
2. If it says the account was created, use:
   Username: admin
   Password: Admin@123
3. If it says the account already exists, this script will not reset the password.
4. IMPORTANT: delete admin/create_admin.php after creating the account.

STEP 5 — Open the admin panel
Visit http://localhost/MediCare+/admin/login.php
Log in and use the navigation links to manage Patients, Doctors, Appointments, Medicines, Pharmacy Orders, Lab Bookings, and Messages.

HOW THE WEBSITE CONNECTS
- Patient registration writes to users.
- Appointment form writes to appointments.
- Doctor selection reads from doctors.
- Pharmacy page reads pharmacy_products through get_medicines.php.
- Pharmacy checkout saves pharmacy_orders and pharmacy_order_items and decreases stock.
- Contact form saves to contacts.
- Laboratory form saves to lab_bookings.
- Admin pages read/update these database records.

IF SOMETHING DOES NOT WORK
- Confirm Apache and MySQL are running.
- Confirm config/db.php uses the correct database name, username, and password.
- Check that the table names and column names in phpMyAdmin match your project.
- For appointments, if an existing appointments table has no status column, run:
  ALTER TABLE appointments ADD COLUMN status VARCHAR(30) NOT NULL DEFAULT 'Pending';
  Only run this if the status column does not already exist.
- The project must be opened through localhost, not by double-clicking HTML files.

Admin pages added:
patients.php, doctors.php, appointments.php, medicines.php, messages.php,
pharmacy_orders.php, pharmacy_order_details.php, lab_bookings.php.
