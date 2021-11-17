<?php
class Devices_Switchs extends Devices {
    
    protected $availableMethods = ["On", "Off", "Get_Status"];


    public function get_snippet($containerType = "article", $class="deviceSnippet")
    {
        $container = new XmlElement($containerType);
        $title = new XmlElement("h3");
        $title->setContent($this->get_device_name());

        $img = new XmlElement("img");
        
        $img->addAttribute( new XmlElementAttribute("src","/imgs/switch-unknow.svg") )
            ->addAttribute( new XmlElementAttribute("class","switchButton") )
            ->addAttribute( new XmlElementAttribute("alt","switch status unknowed") );

        $container  ->addAttribute( new XmlElementAttribute("class",$class) )
                    ->addChild($title)
                    ->addChild($img);
        return $container->__toString();
    }

}