<?php

class Config extends Route{

    private static $optionsValues = array();

    private static $configs = array(
        "LDAP"  => array(
            "file"  =>    "../config/ldap.json",
            "fields"    => array(
                "active" => array(
                    "type"  => "boolean",
                    "constraints"   => array(),
                    "display_name"  => "Actif"
                ),
                "basedn"    => array(
                    "type"  =>"string",
                    "constraints"   => array(),
                    "display_name"  => "basedn"
                ),
                "domain"    =>  array(
                    "type"  =>"string",
                    "constraints"   => array()
                ),
                "filter"    => array(
                    "type"  => "string",
                    "constraints"   => array()
                ),
                "host"    => array(
                    "type"  => "string",
                    "constraints"   => array()
                ),
                "port" => array(
                    "type"  => "string",
                    "constraints"   => array()
                ),
                "regex_on_DistinguishedName"    => array(
                    "type"  => "string",
                    "constraints"   => array()
                ),
                "lastrefresh"   =>  array(
                    "type"  => "string",
                    "constraints"   => array()
                )
            )
        ),
        "Email"    => array(
            "file"  =>    "../config/system.json",
            "fields"    => array(
                "base_url" => array(
                    "type"  => "string",
                    "constraints"   => array()
                ),
                "SMTP_Host" => array(
                    "type"  => "string",
                    "constraints"   => array()
                ),
                "SMTP_Port" => array(
                    "type"  => "string",
                    "constraints"   => array()
                ),
                "SMTP_Auth" => array(
                    "type"  => "boolean",
                    "constraints"   => array()
                ),
                "SMTP_Username" => array(
                    "type"  => "string",
                    "constraints"   => array()
                ),
                "SMTP_Password" => array(
                    "type"  => "password",
                    "constraints"   => array()
                ),
                "SMTP_Secure" => array(
                    "type"  => "string",
                    "constraints"   => array()
                ),
                "SMTP_MailFrom" => array(
                    "type"  => "string",
                    "constraints"   => array()
                ),
                "SMTP_MailFromName" => array(
                    "type"  => "string",
                    "constraints"   => array()
                )
            )
        ),
        "About"  => array(
            "file"  => "../config/about.json",
            "fields"    => array(
                "InstanceTitle"     => array(
                    "type"  => "string",
                    "constraints"   => array()
                ),
                "entreprise_name"   => array(
                    "type"  => "string",
                    "constraints"   => array()
                ),
                "entreprise_name_with_br"   => array(
                    "type"  => "longstring",
                    "constraints"   => array()
                ),
                "description_on_login_page_html" => array(
                    "type"  => "longstring",
                    "constraints"   => array()
                )
            )
        )


    );


    static public function load_values($group){
        if(!isset(self::$configs[$group]["file"]))
        {
            throw new \UnexpectedValueException( $group . " file setting not found");
        }
        if(!file_exists(self::$configs[$group]["file"])){

            if( file_put_contents(self::$configs[$group]["file"],"") === false ){
                throw new \UnexpectedValueException( self::$configs[$group]["file"]. " file not found, and cant create it");
            }
        }
        self::$optionsValues[$group] = json_decode( file_get_contents(self::$configs[$group]["file"]) ,true);


    }
    static public function get_group_values($group)
    {
        if(!isset(self::$optionsValues[$group])){
            self::load_values($group);
        }
        return self::$optionsValues[$group];
    }
    static public function get_option_value($group,$option,$emptyIfNonSetted = true)
    {
        if(!isset(self::$optionsValues[$group])){
            self::load_values($group);
        }
        if( !isset(self::$optionsValues[$group][$option]) ){
            if ($emptyIfNonSetted ){
                return "";
            }else{
                throw new \UnexpectedValueException( $group. " " . $option . "not found");
            }

        }
        return self::$optionsValues[$group][$option];
    }
    static public function set_option_value($group,$option,$value)
    {
        if(!isset(self::$optionsValues[$group])){
            self::load_values($group);
        }
        if(!isset( self::$optionsValues[$group][$option] )){
            throw new \UnexpectedValueException( $group. " " . $option . "not found");
        }

        switch (self::$configs[$group]["fields"][$option]["type"]){
            case "boolean":
                if(in_array($value, array("on","true",true,"ON","TRUE", 1, "1") )){
                    self::$optionsValues[$group][$option] = true;
                }elseif(in_array($value, array("off","false",false,"OFF","FALSE", 0, "0") )){
                    self::$optionsValues[$group][$option] = false;
                }else{
                    throw new \UnexpectedValueException( $key . " need to be boolean");
                }
                break;
            case "integer":
                self::$optionsValues[$group][$option] = intval($value);
                break;
            default:
            self::$optionsValues[$group][$option] = $value;
                break;

        }
        self::save_config_file($group,self::$optionsValues[$group],false);

    }
    static private function generateFieldElem($formName, $fieldName, $fieldDef)
    {
        $field = new XmlElement("p");
        $label = new XmlElement("label");
        $label->setContent( isset($fieldDef["display_name"])? $fieldDef["display_name"] : $fieldName )
            ->addAttribute( new XmlElementAttribute("for", $fieldName));
        $field-> addChild($label);

        switch ($fieldDef["type"]){
            case "boolean":

                $inputElem = new XmlElement("input");
                $inputElem->enable_self_closure();

                $inputElem-> addAttribute( new XmlElementAttribute("type", "checkbox"));
                if( self::get_option_value($formName,$fieldName,true) ){
                    $inputElem->addAttribute( new XmlElementAttribute("checked", "checked"));
                }
                            
                break;
            
            case "longstring":

                $inputElem = new XmlElement("textarea");
                $inputElem->setContent(  self::get_option_value($formName,$fieldName,true) );
                break;

            case "password":
                $inputElem = new XmlElement("input");
                $inputElem->enable_self_closure();
                $inputElem  -> addAttribute( new XmlElementAttribute("type", "password"))
                            -> addAttribute( new XmlElementAttribute("value", self::get_option_value($formName,$fieldName,true)));
                break;

            default:

                $inputElem = new XmlElement("input");
                $inputElem->enable_self_closure();
                $inputElem  -> addAttribute( new XmlElementAttribute("type", "text"))
                            -> addAttribute( new XmlElementAttribute("value", self::get_option_value($formName,$fieldName,true)));
                break;
        }

        $inputElem  -> addAttribute( new XmlElementAttribute("name", $fieldName))
                    -> addAttribute( new XmlElementAttribute("id", $fieldName));

        $field-> addChild($inputElem);

        return $field;

    }
    static private function generateForm($formName){
        $struct = self::$configs[$formName]["fields"];
        $formElems = new XmlElement("article");

        $inputElemGroup = new XmlElement("input");
        $inputElemGroup  -> addAttribute( new XmlElementAttribute("name", "group"))
                            -> addAttribute( new XmlElementAttribute("type", "hidden"))
                            -> addAttribute( new XmlElementAttribute("value", $formName))
                            -> addAttribute( new XmlElementAttribute("id", "group"))
                            ->enable_self_closure();
        $formElems->addChild($inputElemGroup);

        foreach($struct as $fieldName => $field){
            $formElems->addChild(self::generateFieldElem($formName, $fieldName, $field));
        }
        return $formElems;
    }

    static public function get_custom_css(PDO $db, User $user)
    {
        return "<style>\n" . file_get_contents("../templates/config.css") . "\n</style>";

    }
    static public function get_content_html(PDO $db, User $user)
    {  
        $tpl = new TplBlock();
        $tpl->addVars(array(
            "EmailInputs"       => self::generateForm('Email'),
            "AboutInputs"       => self::generateForm('About'),
            "LDAPInputs"        => self::generateForm('LDAP'),
            "submitButton"      => '<input type="submit" value="Sauvegarder"/>'

        ));

       return $tpl->applyTplFile("../templates/config.html");
  
    }

    static private function cast_post_vars($group,$vals)
    {
        if(!isset(self::$configs[$group])){
            throw new \UnexpectedValueException( $group . "is not a listed group");
        }
        $returnArr = array();
        foreach(self::$configs[$group]["fields"] as $key => $fieldDef){
            switch ($fieldDef["type"]){
                case "boolean":
                    if(!isset($_POST[$key])){
                        $returnArr[$key] = false;
                    }elseif(in_array($_POST[$key], array("on","true",true,"ON","TRUE", 1, "1") )){
                        $returnArr[$key] = true;
                    }elseif(in_array($_POST[$key], array("off","false",false,"OFF","FALSE", 0, "0") )){
                        $returnArr[$key] = false;
                    }else{
                        throw new \UnexpectedValueException( $key . " need to be boolean");
                    }
                    break;
                case "integer":
                    $returnArr[$key] = intval($_POST[$key]);
                    break;
                default:
                    $returnArr[$key] = $_POST[$key];
                    break;

            }
        }
        return $returnArr;

    }
    static public function save_config_file($group,$vals, $cast=true)
    {
        if(!isset(self::$configs[$group])){
            throw new \UnexpectedValueException( $group . "is not a listed group");
        }
        if($cast)
        {
            file_put_contents( self::$configs[$group]["file"], json_encode( self::cast_post_vars($group,$vals) ) );
        }else{
            file_put_contents( self::$configs[$group]["file"], json_encode( $vals ) );
        }
    }
    static public function apply_post(PDO $db, User $user)
    {
        if(!isset($_POST["act"])){
            return;
        }
        switch ($_POST["act"])
        {
 
            case "saveParams":

                if(!isset($_POST["group"])){
                    throw new \UnexpectedValueException( "'goup' post param is mandatory");
                }
                $group = $_POST["group"];
                if(!isset(self::$configs[$group])){
                    throw new \UnexpectedValueException( $group . "is not a listed group");
                }

                $vals = array();
                foreach(self::$configs[$group]["fields"] as $key => $value)
                {
                    
                    if( $value["type"] == "boolean" ){ //unexisting value eq false on e checkbox
                        $vals[$key] = (isset($_POST[$key]))? $_POST[$key] : "off";
                    }elseif( !isset($_POST[$key])) {
                        throw new \UnexpectedValueException( "Missing value " . $key);
                    }else{
                        $vals[$key] = $_POST[$key];
                    }
                }
                self::save_config_file($group,$vals);

                break;

        }

    }
    
}