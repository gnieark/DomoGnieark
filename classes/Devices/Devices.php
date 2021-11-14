<?php

/**
 * 
 * the devices Class
 * 
 * Unusabled, use childs classes
 * 
 */

class Devices {


    protected $name = "";

    protected $containerHtmlType = "article";

    /*
    * The methods avalaible on the device: on off get-status, volume-up, volume-down   etc....
    *
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



}