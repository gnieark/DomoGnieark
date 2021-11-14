<?php

/**
 * 
 * the devices Class
 * 
 * Unusabled, use childs classes
 * 
 */

class Devices {

    protected $device_id = "";
    protected $name = "";

    protected $containerHtmlType = "article"; //container used on generated html snipped

    /*
    * The methods avalaible on the device: on off get-status, volume-up, volume-down   etc....
    * @var array
    */
    protected $availableMethods = [];


    public function __construct( $params = array() )
    {

        
    }

    public function get_snippet_as_XMLelement()
    {
        $container = new XmlElement( $this->containerHtmlType );
        $message = new XmlElement('p');
        $message->setContent('empty device');
        $container->addChild($message);
        return $container;
    }
    public function __toString()
    {
        return $this->get_snippet_as_XMLelement()->__toString();
    }

    public function set_device_id($id)
    {
        $this->device_id = $id;
        return $this;
    }
    public function set_device_name( $name )
    {
        $this->name = $name;
        return $this;
    }
    public function get_device_id()
    {
        return $this->device_id;
    }
    public function get_device_name()
    {
        return $this->name;
    }






}