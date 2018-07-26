AccountHub
======

AccountHub is a web application enabling secure self-serve account management. 
Employees can change their password and manage other web apps they have access 
to with the dashboard.

https://netsyms.biz/apps/accounthub

Installing
----------

0. Setup a LAMP server with PHP 7.2, including PHP extensions mbstring, zip, gd, and imagick
1. Copy `settings.template.php` to `settings.php`
2. Import `database.sql` into your database server
3. Edit `settings.php` and fill in your DB info
4. Set the URL of the install
5. Setup "EXTERNAL_APPS" with specifics for your install.
6. Setup the email settings to receive alerts you configure later in ManagePanel
7. Run `composer install` (or `composer.phar install`) to install dependency libraries
8. Edit the database table `apikeys` and add some API keys for the other apps to use
9. From a web browser, visit `http://apps/url` (or whatever your setup is).  If you did everything right, you should see a login screen.
10. Now go to `http://apps/url/setup.php` and create an admin account.
11. Install [ManagePanel](https://source.netsyms.com/Business/ManagePanel) to setup additional user accounts.


Upgrading
---------

1. Run `git pull` or otherwise update the code
2. Run `composer install` to update dependencies
3. Execute the SQL scripts in `database_upgrade` to take you from your current version to the latest version
4. Rewrite your `settings.php` based on the new `settings.template.php`
