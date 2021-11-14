<?php
class XmlElement
{

    /*
    * Gere une balise xml Nom, attributs, contenu ou enfants
    */

    private $name;
    private $attributes = array();
    private $content = "";
    private $childs = array();
    private $selfClosure = false;
    private $displayIfEmpty = true;

    public function enable_self_closure()
    {
        $this->selfClosure = true;
        return $this;
    }
    public function disable_self_closure(){
        $this->selfClosure = false;
        return $this;
    }

    public function dont_display_if_empty(){
       $this->displayIfEmpty = false;
       return $this;
    }
    public function addChild(XmlElement $child){
        if(!empty($this->content)){
            throw new \UnexpectedValueException(
                "No, you can't add chlid if content exists"
            );
            return;
        }
        $this->childs[] = $child;
        return $this;
    }
    public function setContent(string $content){
        if(!empty($this->childs)){
            throw new \UnexpectedValueException("No, you can't add content if chields exists ");
            return;
        }
        $this->content = $content;
        return $this;
    }

    public function addAttribute($attribute)
    {
        $this->attributes[] = $attribute;
        return $this;
    }

    public function __construct($name)
    {
        if(!preg_match('/^[\w-]+$/', $name)) {
            throw new \UnexpectedValueException(
                "Only alpha numerics chars are allowed on name'"
            );
        }
        $this->name = $name;
    }

    public function __toString()
    {
        if($this->displayIfEmpty === false && empty($this->childs) && empty($this->content)){
            return "";
        }

        if( empty($this->childs) && empty($this->content) && $this->selfClosure){

            if(empty($this->attributes))
            {
                return "<" . $this->name . "/>";
            }else{
                return "<" . $this->name . " " . implode(" ", $this->attributes) . "/>";
            }

        }

        if(empty($this->attributes))
        {
            $str = "<" . $this->name . ">";
        }else{
            $str = "<" . $this->name . " " . implode(" ", $this->attributes) . ">";
        }

        if(!empty($this->childs))
        {
            foreach($this->childs as $child){
                $str.= "\n  ".str_replace("\n","\n  ",$child);
            }
            return $str."\n</"  . $this->name . ">\n";
        }
        return  $str .
                $this->content .
                "</" . $this->name . ">";

    }

}

