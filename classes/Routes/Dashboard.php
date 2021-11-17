<?php
Class Dashboard extends Route
{
    protected static $displayOnPage = true;

    static public function get_content_html(PDO $db, User $user)
    {
        //get Devices List
        $content = "";
        foreach (DevicesManager::get_devices_objects($db, $user) as $device){
            $content .= $device->get_snippet();
           
        }

        return $content;
    }
    static public function get_custom_css(PDO $db, User $user)
    {
        return "<style type=\"text/css\">\n" . file_get_contents("../templates/dashboard.css") . "\n</style>";
    }

}