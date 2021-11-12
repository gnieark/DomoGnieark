<?php

//autoload classes
spl_autoload_register(function ($class_name) {
    $classFolders = array   ("../classes/"
                            ,"../classes/Devices/"
                            ,"../classes/Template/"
                            ,"../classes/Routes/"
                            ,"../classes/Menus/"
                            ,"../classes/User/"
                            );
    foreach($classFolders as $folder)
    {
        if(file_exists( $folder . $class_name . '.php')){
            include $folder. $class_name . '.php';
            return;
        }
    }
});

$params = include("../config/config.php");

//database connexion
try {
    $db = new PDO($params->sql_params_local_db["dsn"], $params->sql_params_local_db["user"], $params->sql_params_local_db["password"]);
} catch (PDOException $e) {
    echo 'Connexion échouée : ' . $e->getMessage();
}

@session_start();

//logout
if( $_SERVER['REQUEST_URI'] == "/logout" )
{
    unset($_SESSION["user"]);
    header('Location: /'); 
    die();
}

if(isset($_SESSION['user'])){
    //session user déjà instanciée précédement
    $currentUser = unserialize($_SESSION["user"]);
    $currentUser->set_db($db);
}else{
    $currentUser = new User($db);
}

//let pass POST action for  local authentificate
if(isset($_POST['act']) && $_POST["act"] == 'auth'){
    $currentUser = User_Manager::authentificate($db,$_POST['login'], $_POST['password']);
}


$_SESSION["user"] = serialize($currentUser);


if($currentUser->is_connected() === false)
{
    //send authentification form
    $tpl = new TplBlock();
    $tpl->addVars(
        array(
                "InstanceTitle"                     => Config::get_option_value("About","InstanceTitle",true),
                "structure_name_with_br"            => Config::get_option_value("About","entreprise_name_with_br",true),
                "description_on_login_page_html"    => Config::get_option_value("About","description_on_login_page_html",true)
        )
    );
    if(file_exists("../templates/connect.custom.html")){
        echo $tpl->applyTplFile("../templates/connect.custom.html");
    }else{
        echo $tpl->applyTplFile("../templates/connect.html");
    }
    die();
}


//At this point the user is authentificated
//load available menus
//load available menus
$mManager = new Menus_manager();

$mManager->add_menus_items_from_json_file( realpath( __DIR__ . '/../') . '/config/menus.json');

//Apply current Menu:
$currentMenu = $mManager->get_current_menu();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $messages = $currentMenu->apply_post($db,$currentUser);
}

if(!$currentMenu->display_on_page())
{
    // on n'envoie rien d'autre que le content
    $currentMenu->send_content($db,$currentUser);
    die();
}

//show the page

$tpl = new TplBlock();
$tpl->addVars(
    array(
        "headTitle" => $currentMenu->get_name() . " - DomoGnieark",
        "structure_name_with_br"    => Config::get_option_value("About","entreprise_name_with_br",true),
        "userDisplayName"   => $currentUser->get_display_name(),
        "headerTitle" => $currentMenu->get_name(),
        "customJS"  => $currentMenu->get_custom_js($db,$currentUser),
        "customCSS" => $currentMenu->get_custom_css($db,$currentUser),
        "content"   => $currentMenu->get_content_html($db,$currentUser),
        "after_body_tag" => $currentMenu->get_custom_after_body_tag($db,$currentUser)
    )
);

//menu de navigation

$navMenus = $mManager->get_user_menu_list($currentUser,true);
foreach($navMenus as $navItem){
    $tplNav = new TplBlock("navmenus");
    $tplNav ->addVars(
        array(
            "url"  => $navItem->get_url(),
            "caption"  => htmlentities($navItem->get_name())
        )
    );
    $tpl->addSubBlock($tplNav);

}

echo $tpl->applyTplFile("../templates/main.html");

