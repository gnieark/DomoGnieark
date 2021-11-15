<?php
class Devices_one_action_hackerspace_door extends Devices
{

    protected $device_ip;
    protected $device_port;
    protected $device_scheme;
    protected $SSLkey = "";
    protected $CAINFO = "";
    protected $SSLCERT = "";
    protected $SSLCERTPASSWORD = "";

    public function set_device_ip($device_ip)
    {
        $this->device_ip = $device_ip;
        return $this;
    }
    public function set_device_port( $device_port )
    {
        $this->device_port = $device_port;
        return $this;
    }
    public function set_device_scheme( $device_scheme )
    {
        $this->device_scheme = $device_scheme;
        return $this;
    }
    public function set_SSLkey ( $SSLkey )
    {
        $this->SSLKey = $SSLkey;
        return $this;
    }
    public function set_CAINFO ( $CAINFO )
    {
        $this->CAINFO = $CAINFO;
        return $this;
    }
    public function set_SSLCERT ( $SSLCERT )
    {
        $this->SSLCERT = $SSLCERT;
        return $this;
    }
    public function set_SSLCERTPASSWORD ( $SSLCERTPASSWORD )
    {
        $this->SSLCERTPASSWORD = $SSLCERTPASSWORD;
        return $this;
    }
    public function __construct( $params = array() )
    {
        $this->load_params($params);
    }

}