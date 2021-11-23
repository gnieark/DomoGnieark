<?php
class Devices_MqttServer extends Devices
{
    private $phpMQTT = null;

    
    public function __construct( $params = array() )
    {
        $this->load_params($params);
    }

}