<?php
/*
* Srcipt à utiliser en mode console permettant de créer un utilisateur
* 
*/

$base_directory = dirname ( get_included_files()[0] );
chdir($base_directory);

$arguments = array();
try {
    for($i = 1; $i < count($argv); $i++ )
    {
        list($key,$value) = explode("=",$argv[$i]);
        $arguments[$key] = $value;
    }
} catch (Throwable $e) {
    echo "usage: php create_user.php login=blahblah password=azerty admin=1";
    die();
}
foreach(array("login","password","admin") as $wanted_key ){
    if(!in_array($wanted_key, array_keys($arguments))){
        echo "a mandatory parameter is missing.\n";
        echo "usage: php create_user.php login=blahblah password=azerty admin=1";
        die();      
    }
}

//autoload classes
spl_autoload_register(function ($class_name) {
    $classFolders = array(  "../classes/", 
                            "../classes/Routes/",
                            "../classes/User/",
                            "../classes/Menus/",
                        );
    foreach($classFolders as $folder)
    {
        if(file_exists( $folder . $class_name . '.php')){
            include $folder . $class_name . '.php';
            return;
        }
    }
});


//local db (mysql)

$params = include("../config/config.php");
try {
    $dbLocal = new PDO($params->sql_params_local_db["dsn"], $params->sql_params_local_db["user"], $params->sql_params_local_db["password"]);
} catch (PDOException $e) {
    echo 'Connexion échouée : ' . $e->getMessage();
}

User_Sql::create_user(  $dbLocal
                        ,User_Manager::get_table_users_str()
                        ,$arguments['login']
                        ,$arguments['login']
                        ,$arguments['password']
                        ,($arguments['admin'] == 1)? true : false
                        , true
                    );


 echo "l'utilisateur a été créé";