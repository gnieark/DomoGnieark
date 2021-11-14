# DomoGnieark

A WEB UI and an API to manage some home automation equipment. Work in progress.

# Installation
In order to test, or to participate to dev because, it's not working yet.

* All requests are computed by public/index.php See the sample doc/nginxSample.conf (The same as a symphony framework one)
* file config/config.php must contain database settings
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
* execute the init script to populate the dayabase
    php install/initDatabase.php
A user is created. admin, password : changeme.
The day i'll create the user management interface, you will be able to change the password. for now, you can delete the user on the database and execute the install/create_user.php script.