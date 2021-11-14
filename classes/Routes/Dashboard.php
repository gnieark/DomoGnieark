<?php
Class Dashboard extends Route
{
    protected static $displayOnPage = true;

    static public function get_content_html(PDO $db, User $user)
    {
        //get Devices List
        $content = new XmlElement("section");
        foreach (DevicesManager::get_devices_objects($db, $user) as $device){
            $content->addChild( $device->get_snippet_as_XMLelement());
           
        }

        return $content->__toString();
    }


}