<?php

class XmlElementAttribute
{
    private $name;
    private $value;

    public function __toString()
    {
        if(is_null($this->value))
        {
            return $this->name;
        }
        return $this->name . '="' . addslashes($this->value) . '"';
    }

    public function __construct($name,$value)
    {
        if(!preg_match('/^[\w-]+$/', $name)) {
            throw new \UnexpectedValueException(
                "Only alphanumerics chars are allowed for the attribute name"
            );
        }
        $this->name = $name;
        $this->value = $value;
    }
}
