<?php

$base_directory = dirname ( get_included_files()[0] );
chdir($base_directory);

require_once ("../classes/Loader.php");

//local db (mysql)

$params = include("../config/config.php");
try {
    $db = new PDO($params->sql_params_local_db["dsn"], $params->sql_params_local_db["user"], $params->sql_params_local_db["password"]);
} catch (PDOException $e) {
    echo 'Connexion échouée : ' . $e->getMessage();
}

User_Manager::create_local_tables($db);
DataList_devices_categories::create_list_table($db);
DataList_devices_models::create_list_table($db);
DataList_devices::create_list_table($db);

User_Sql::create_user(  $db
                        ,User_Manager::get_table_users_str()
                        ,'admin'
                        ,'admin'
                        ,'changeme'
                        , true
                        , true
                    );
