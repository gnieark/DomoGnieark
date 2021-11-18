<?php

class DevicesManager extends Route{


    static private function get_devicesSRC(){
        static $devicesSRC = array();
        if(empty($devicesSRC)){
            $devicesSRC =  yaml_parse( file_get_contents("../src/Devices.yml") );
        }
        return $devicesSRC;
    }
     

    static public function get_content_html(PDO $db, User $user)
    {
        $tpl = new TplBlock();

        $tpl->addVars(array(
            "formAddActionUrl"  => "/DevicesManager/addDevice"
        ));
        $devicesSRC = self::get_devicesSRC();
        foreach( $devicesSRC["categories"] as $catName => $cat ){
            
            $tplCatDevices = new TplBlock("catDevices");
            $tplCatDevices->addVars(array(
                "value"     => $catName,
                "caption"   => $cat["display_name"]
            ));
            $tpl->addSubBlock($tplCatDevices);
        }
        


        $devicesList = DataList_devices::GET($db, $user);

        foreach($devicesList as $device)
        {
            $tplDevice = new TplBlock("devices");
            $tplDevice->addVars($device);

            //customconfig
            $configs = json_decode($device["configuration"]);
            foreach( $configs as $key => $value)
            {
                $tplConf = new TplBlock("customconfig");
                if( isset($devicesSRC["categories"]
                                     [ $device["category_name"] ]
                                     ["models"]
                                     [ $device["model_name"] ]
                                     ["needed-to-configure"]
                                     [$key]
                                     ["dont_show"]
                )){
                    
                    $dontShow = $devicesSRC["categories"]
                                            [ $device["category_name"] ]
                                            ["models"]
                                            [ $device["model_name"] ]
                                            ["needed-to-configure"]
                                            [$key]
                                            ["dont_show"];
                }else{
                    $dontShow = false;
                }

                    
                

                $tplConf->addVars(array(
                    "key"   => $key,
                    "value" => ($dontShow && !empty($value))? "*****" : $value
                ));
                $tplDevice->addSubBlock($tplConf);
            }
            $tpl->addSubBlock($tplDevice);

        }
        
        return $tpl->applyTplFile("../templates/DevicesManager.html");
    }


    static private function get_device_class( $cat_name,$model_name ){
        $devicesSRC = self::get_devicesSRC();
        if(isset($devicesSRC["categories"][$cat_name]["models"][$model_name]["PHPClass"])){
            return $devicesSRC["categories"][$cat_name]["models"][$model_name]["PHPClass"];
        }
        return false;
    }

    /*
    *   Load the devices data frmo database
    *   Construct the good object for each devices
    *   return an array of devices objects
    *
    * 
    */
    static public function get_devices_objects(PDO $db, User $user)
    {
        $devicesObjects = array();
        $devicesList = DataList_devices::GET($db, $user);
        foreach($devicesList as $deviceData){
            $devicesObjects[$deviceData["id"]] = self::get_device_object_by_data($deviceData);
        }
        return $devicesObjects;
    }
    static public function get_device_object_by_id(PDO $db, User $user, $id)
    {
        $devicesList = DataList_devices::GET($db, $user,array("id" => $id));
        return self::get_device_object_by_data($devicesList[0]);
    }
    static private function get_device_object_by_data($deviceData)
    {
        $phpClass = self::get_device_class($deviceData["category_name"],$deviceData["model_name"] );
        $params = json_decode( $deviceData["configuration"],true );
        $params["device_id"] = $deviceData["id"];
        $params["device_name"] =   $deviceData["display_name"];
        return new $phpClass($params);
    }
    /*
    * Return the id of a device model. Create it on database if is defined on devices.yaml 
    * but not on the models table
    */
    static private function get_device_model_id_by_name(PDO $db, User $user, string $catName, string $modelName)
    {
        $devicesSRC = self::get_devicesSRC();

        //test category, create it if needed
        $filters = array( "name"  => $catName) ;
        $cat = DataList_devices_categories::GET($db, $user, false, $filters);

        if(empty($cat)){
            if(array_key_exists($catName, $devicesSRC["categories"]))
            {
                $catValues = array(
                    "name"  => $catName,
                    "display_name"  => $devicesSRC["categories"][ $catName ][ "display_name" ] 
                );

                $cat_id = DataList_devices_categories::POST($db, $user, $catValues);
            }else{
                return false;
            }
        }else{
            $cat_id = $cat[0]["id"];
        }
        //test model, create it if needed

        $filtersModels = array( "name"   => $modelName, "category_id"    => $cat_id);
        $models = DataList_devices_models::GET($db, $user, false, $filtersModels);
        if( empty($models) ){
            if(!isset($devicesSRC["categories"][$catName]["models"][$modelName])){
                return false;
            }
            $modelValues = array(
                "name"          => $modelName,
                "display_name"  => $devicesSRC["categories"][$catName]["models"][$modelName]["display_name"],
                "category_id"   => $cat_id
            );
            return DataList_devices_models::POST($db, $user, $modelValues);
        }else{
            return $models[0]["id"];
        }

    }

    static public function apply_post(PDO $db, User $user)
    {
        switch( $_SERVER['REQUEST_URI'] ){
            case "/DevicesManager/addDevice":

                $devicesSRC = self::get_devicesSRC();

                $neededToConfigure = $devicesSRC["categories"][$_POST["addDeviceCat"]]["models"][$_POST["addDeviceModel"]]["needed-to-configure"];
                $configuration = array();
                
                foreach( array_keys( $neededToConfigure ) as $keyToConfigure)
                {
                    $configuration[ $keyToConfigure ] = $_POST[ $keyToConfigure ];

                }

                $devicesValues = ['display_name'    =>  $_POST["addDeviceName"]
                                 ,'model_id'        => self:: get_device_model_id_by_name($db, $user, $_POST["addDeviceCat"], $_POST["addDeviceModel"])
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
        return "<style type=\"text/css\">\n" . file_get_contents("../templates/DevicesManager.css") . "\n</style>";
    }

}