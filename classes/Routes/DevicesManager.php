<?php

class DevicesManager extends Route{

    static public function get_content_html(PDO $db, User $user)
    {
        $tpl = new TplBlock();

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

    static public function get_custom_js()
    {
        return "<script>\n" . file_get_contents("../templates/DevicesManager.js") . "\n</script>";
    }

}