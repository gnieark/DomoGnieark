# DomoGnieark

A WEB UI and an API to manage some home automation equipment. Work in progress.

# Installation
(to test, or to contribute because, it's not working yet.)

PHP 7 with php-mosquitto https://mosquitto-php.readthedocs.io/en/latest/

* All requests are computed by public/index.php . Look at the sample doc/nginxSample.conf (The same as a symphony framework one)
* The file config/config.php must contain database settings
```php
<?php
return (object) array(
        'sql_params_local_db' => array( 
        "dsn"           => "mysql:dbname=domognieark;host=localhost",
        "user"          => "domognieark",
        "password"      => "changeme",
    )
);
```
* Execute the init script to populate the database.
    php install/initDatabase.php
A user is created. admin, password : changeme.

The day i'll create the user management interface, you will be able to change the password. for now, you can delete the user on the database and execute the install/create_user.php script.