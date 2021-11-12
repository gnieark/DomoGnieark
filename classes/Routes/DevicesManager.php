<?php

class DevicesManager extends Route{

    static public function get_content_html(PDO $db, User $user)
    {
        $tpl = new TplBlock();

        $tpl->addVars(array(
            "formAddActionUrl"  => "/DevicesManager/addDevice"
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
        switch( $_SERVER['REQUEST_URI'] ){
            case "/DevicesManager/addDevice":
                /*
                array(6) {
  ["addDeviceName"]=>
  string(9) "dfjhdfhdf"
  ["addDeviceCat"]=>
  string(12) "SwitchesHTTP"
  ["addDeviceModel"]=>
  string(12) "SonoffMiniR2"
  ["ip"]=>
  string(4) "1566"
  ["port"]=>
  string(2) "80"
  ["scheme"]=>
  string(4) "http"
}
*/
                break;
            default:
                C400::send_content($db,$user);
                die();
                break;
        }
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