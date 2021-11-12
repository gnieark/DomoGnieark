<?php

class DevicesManager extends Route{

    static public function get_content_html(PDO $db, User $user)
    {
        $tpl = new TplBlock();

        $tpl->addVars(array(
            "formAddActionUrl"  => "index.php?menu=DevicesManager&amp;act=addDevice"
        ));
        $devicesSRC = yaml_parse( file_get_contents("../src/Devices.yml") );
        foreach( $devicesSRC["categories"] as $catName => $cat ){
            $tplCatDevices = new TplBlock("catDevices");
            $tplCatDevices->addVars(array(
                "value"     => $catName,
                "caption"   => $cat["display_name"]
            ));
        }
        $tpl->addSubBlock($tplCatDevices);
   
        return $tpl->applyTplFile("../templates/DevicesManager.html");
    }


    static public function apply_post(PDO $db, User $user)
    {
        /*
        $act = isset($_GET["act"]) ? $_GET["act"] : "";
        switch ($act){
            case "addDevice":

                break;
            default:
                //400?
                break;
        }
        */
    }

    static public function get_custom_js()
    {
        return "<script>\n" . file_get_contents("../templates/DevicesManager.js") . "\n</script>";
    }
    static public function get_custom_css(PDO $db, User $user)
    {
        return "<style>\n" . file_get_contents("../templates/DevicesManager.css") . "\n</style>";
    }

}