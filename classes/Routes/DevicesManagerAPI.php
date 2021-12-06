<?php

class DevicesManagerAPI extends Route{

    protected static $displayOnPage = false;


    static public function send_content(PDO $db, User $user)
    {
        
        if( preg_match("/^\/DevicesManagerAPI\/category\/([a-zA-Z0-9_]*)\/models/", $_SERVER['REQUEST_URI'], $matches ) )
        {
            self::send_json_models_list($matches[1]);
        }
        elseif( preg_match("/^\/DevicesManagerAPI\/category\/([a-zA-Z0-9_]*)\/model\/([a-zA-Z0-9_]*)\/needed-to-configure/", $_SERVER['REQUEST_URI'], $matches ) )
        {
            self::send_json_needed_to_configure($matches[1],$matches[2]);
        }
        elseif( preg_match("/^\/DevicesManagerAPI\/category\/([a-zA-Z0-9_]*)\/model\/([a-zA-Z0-9_]*)\/devices/", $_SERVER['REQUEST_URI'], $matches ) )
        {
            self::send_json_list_of_devices_for_a_model($db,$user,$matches[1],$matches[2]);
        }
        elseif( preg_match("/^\/DevicesManagerAPI\/category\/([a-zA-Z0-9_]*)\/model\/([a-zA-Z0-9_]*)$/", $_SERVER['REQUEST_URI'], $matches ) )
        {
            self::send_json_model($matches[1],$matches[2]);
        }
        elseif( preg_match("/^\/DevicesManagerAPI\/device\/([a-zA-Z0-9_]*)\/status/", $_SERVER['REQUEST_URI'], $matches )  )
        {
           
            $deviceObj = DevicesManager::get_device_object_by_id($db, $user, $matches[1]);
            if($deviceObj === false ){
                self::send_404_json_style();
                die();
            }
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode( $deviceObj->get_status() , true );

        }
        elseif( preg_match( "/^\/DevicesManagerAPI\/AutoDiscover-mqtt$/" , $_SERVER['REQUEST_URI'] ) ){
            //lister les devices de type mttserver
            //$mqttServers = DevicesManager::
            $mqttServers = DevicesManager::get_devices_objects_by_model($db, $user, "mqtt", "mqttServer");
            foreach($mqttServers as $mqttServer){
                //test it
                $mqttServer->findDevices(); 
            }
            die();
        }
        else
        {
            self::send_404_json_style();
        }
        
    }
    static public function apply_post(PDO $db, User $user)
    {
        if(preg_match("/^\/DevicesManagerAPI\/device\/([a-zA-Z0-9_]*)$/",  $_SERVER['REQUEST_URI'] , $matches)){
            $inputData = json_decode(file_get_contents('php://input'), true);
            if(!isset($inputData["status"]) || !in_array($inputData["status"], array("on","off","turn")) ){
                self::send_400_json_style("Request must have a JSON formatted body, containing a status field. Available values are 'on', 'off' and 'turn'");
            }

            $deviceObj = DevicesManager::get_device_object_by_id($db, $user, $matches[1]);

            if($inputData["status"] == "turn")
            {
                if( $deviceObj->get_status()["status"] == "on" ){
                    $targetStatus = "off";
                }else{
                    $targetStatus = "on";
                }
            }else{
                $targetStatus = $inputData["status"];
            }
            header('Content-Type: application/json; charset=utf-8');
            $request = $deviceObj->makeRequest( $targetStatus );
   
            if( $request) {
                echo '{"error":0}';
            }else{
                echo '{"error":1}';
            }


            die();
        }

    }
    static private function send_json_models_list($category)
    {
        $devicesSRC = yaml_parse( file_get_contents("../src/Devices.yml") );
        if( isset( $devicesSRC["categories"][$category] ) ){
            $models = array();
            foreach( $devicesSRC["categories"][$category]["models"] as $modelName => $model ){

                if(isset($model["mqttServer_needed"]) && $model["mqttServer_needed"]){
                    $mqttServer_needed = true;
                }else{
                    $mqttServer_needed = false;
                }
                if( isset($model["autoDiscoverMethod"]) &&  $model["autoDiscoverMethod"] ){
                    $autoDiscoverMethod = true;
                }else{
                    $autoDiscoverMethod = false;
                }

                $models[] = array("name" => htmlentities($modelName)
                                  ,"display_name" => htmlentities($model["display_name"])
                                  ,"mqttServer_needed" => $mqttServer_needed
                                  ,"autoDiscoverMethod" => $autoDiscoverMethod
                                  );
            }
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($models);
        }else{
            self::send_404_json_style();
        }
    }

    static private function send_json_list_of_devices_for_a_model( PDO $db, User $user, $category,$model){
 
        $model_id = DevicesManager::get_device_model_id_by_name( $db, $user, $category, $model );
        $filters = array( "model_id"   => $model_id);
        $devicesList = DataList_devices::GET($db, $user, false, $filters);
        $ret = array();
        foreach( $devicesList as $deviceData )
        {
            $ret[] = array(
                "id"  => $deviceData["id"],
                "display_name"  => $deviceData["display_name"]
            );

        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($ret);

    }

    static private function send_json_needed_to_configure($category,$model){
        $devicesSRC = yaml_parse( file_get_contents("../src/Devices.yml") );
        if(isset($devicesSRC["categories"][$category]["models"][$model] )){
            header('Content-Type: application/json; charset=utf-8');
            
            echo json_encode($devicesSRC["categories"][$category]["models"][$model]["needed-to-configure"], true  );
        }else{
            self::send_404_json_style("Model and / or category not found");
        }
    }
    static private function send_json_model($category,$model){
        $devicesSRC = yaml_parse( file_get_contents("../src/Devices.yml") );
        if(isset($devicesSRC["categories"][$category]["models"][$model] )){
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($devicesSRC["categories"][$category]["models"][$model], true  );
        }else{
            self::send_404_json_style("Model and / or category not found");
        }
    }


    
    static private function send_404_json_style($customMessage = ""){
        header("HTTP/1.1 404 Not Found");
        header('Content-Type: application/json; charset=utf-8');
        echo (json_encode(array(
            "code" => 404,
            "message" => $customMessage  == "" ? "Resource you asked for is not found." : $customMessage 
        )));

    }
    static private function send_400_json_style($customMessage = ""){
        header("HTTP/1.1 400 Bad Request");
        header('Content-Type: application/json; charset=utf-8');
        echo (json_encode(array(
            "code" => 400,
            "message" => $customMessage  == "" ? "Bad Request" : $customMessage 
        )));

    }
}
