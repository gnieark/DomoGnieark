<?php

class DevicesManagerAPI extends Route{

    protected static $displayOnPage = false;


    static public function send_content(PDO $db, User $user)
    {
        
        if( isset($_GET["list"]) &&  $_GET["list"] == "models" && isset( $_GET["category"] ) )
        {
            $devicesSRC = yaml_parse( file_get_contents("../src/Devices.yml") );
            if( isset( $devicesSRC["categories"][$_GET["category"]] ) ){
                $models = array();
                foreach( $devicesSRC["categories"][$_GET["category"]]["models"] as $modelName => $model ){
                    $models[] = array(
                        "name" => htmlentities($modelName),
                        "displayName" => htmlentities($model["displayName"])
                    );
                }
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode($models);
                die();
            }
           
          
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array());
        die();
        
    }

    static private function send_model_list()
    {

    }




}