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


}