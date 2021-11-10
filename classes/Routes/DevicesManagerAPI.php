<?php

class DevicesManagerAPI extends Route{

    protected static $displayOnPage = false;


    static public function send_content(PDO $db, User $user)
    {
    
        if( isset($_GET["list"]) &&  $_GET["list"] == "models" )
        {

        }
        
    }

    static private function send_model_list()
    {

    }




}