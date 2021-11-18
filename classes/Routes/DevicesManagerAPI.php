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
        else
        {
            self::send_404_json_style();
        }
        
    }

    static private function send_json_models_list($category)
    {
        $devicesSRC = yaml_parse( file_get_contents("../src/Devices.yml") );
        if( isset( $devicesSRC["categories"][$category] ) ){
            $models = array();
            foreach( $devicesSRC["categories"][$category]["models"] as $modelName => $model ){
                $models[] = array(
                    "name" => htmlentities($modelName),
                    "display_name" => htmlentities($model["display_name"])
                );
            }
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($models);
        }else{
            self::send_404_json_style();
        }
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

    static private function send_404_json_style($customMessage = ""){
        header("HTTP/1.1 404 Not Found");
        header('Content-Type: application/json; charset=utf-8');
        echo (json_encode(array(
            "code" => 404,
            "message" => $customMessage  == "" ? "Resource you asked for is not found." : $customMessage 
        )));

    }
}