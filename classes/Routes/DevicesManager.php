<?php

class DevicesManager extends Route{

    static public function get_content_html(PDO $db, User $user)
    {
        $tpl = new TplBlock();



        return $tpl->applyTplFile("../templates/DevicesManager.html");
    }
    
    static public function get_custom_js()
    {
        return file_get_contents("../templates/DevicesManager.js");
    }

}