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

                $devicesSRC = yaml_parse( file_get_contents("../src/Devices.yml") );

                //test if category exists
                $filters = array( "name"  => $_POST["addDeviceCat"]) ;
                $cat = DataList_devices_categories::GET($db, $user, false, $filters);
    
                if(empty($cat)){
                    
                    if(array_key_exists($_POST["addDeviceCat"], $devicesSRC["categories"]))
                    {
                        $catValues = array(
                            "name"  => $_POST["addDeviceCat"],
                            "display_name"  => $devicesSRC["categories"][ $_POST["addDeviceCat"] ][ "display_name" ] 
                        );

                        $cat_id = DataList_devices_categories::POST($db, $user, $catValues);
                        $cat_name = $_POST["addDeviceCat"];
                    }else{
                        return "unknowed device category";
                    }
                }else{
                   $cat_id = $cat[0]["id"];
                   $cat_name = $cat[0]["name"];
                }
        
                //generate config JSON
                if(!isset($devicesSRC["categories"][$cat_name]["models"][$_POST["addDeviceModel"]])){
                    return "unknowed device model";
                }
                $neededToConfigure = $devicesSRC["categories"][$cat_name]["models"][$_POST["addDeviceModel"]]["needed-to-configure"];
                $configuration = array();
                foreach( array_keys( $neededToConfigure ) as $keyToConfigure)
                {
                    $configuration[ $keyToConfigure ] = $_POST[ $keyToConfigure ];

                }
                $devicesValues = ['display_name'    =>  $_POST["addDeviceName"]
                                 ,'category_id'     => $cat_id
                                 ,'description'     => ''
                                 ,'configuration'   => json_encode($configuration)
                                 ];
                DataList_devices::POST($db, $user, $devicesValues);

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