<?php

class DevicesManagerAPI extends Route{

    protected static $displayOnPage = false;


    static public function send_content(PDO $db, User $user)
    {
        
        if( isset($_GET["list"]) &&  $_GET["list"] == "models" && isset( $_GET["category"] ) )
        {
            self::send_json_models_list($_GET["category"]);
        }
        elseif( isset($_GET["list"]) &&  ($_GET["list"] == "needed-to-configure") && isset($_GET['model']) && isset( $_GET["category"] ))
        {
            self::send_json_needed_to_configure($_GET["category"],$_GET['model']);

        }else{
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
                    "displayName" => htmlentities($model["displayName"])
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