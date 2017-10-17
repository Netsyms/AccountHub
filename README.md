AccountHub
======

AccountHub is a web application enabling secure self-serve account management. 
Employees can change their password and manage other web apps they have access 
to with the dashboard.

https://biz.netsyms.com/accounthub

Installing
----------

*We're working on a nice script to install it automatically, but until then...*

0. Setup a LAMP server with additional PHP extensions mbstring, zip, ldap, gd, imagick
1. Copy `settings.template.php` to `settings.php`
2. Import `database.sql` into your database server
3. Edit `settings.php` and fill in your DB info
4. Setup LDAP auth, or set "LDAP_ENABLED" to FALSE
5. Set the URL of the install
6. Set the API and HOME values for TaskFloor, Inventory (BinStack), QwikClock, and in the "EXTERNAL_APPS" setting
7. Remove any apps you aren't installing from "EXTERNAL_APPS"
8. Setup the email settings to receive alerts you configure later in ManagePanel
9. Run `composer install` (or `composer.phar install`) to install dependency libraries
10. Edit the database table `apikeys` and add some API keys for the other apps to use
11. From a web browser, visit `http://apps/url` (or whatever your setup is).  If you did everything right, you should see a login screen.
12. Now go to `http://apps/url/setup.php` and create an admin account.
13. Install [ManagePanel](https://source.netsyms.com/Business/ManagePanel) to setup additional user accounts.