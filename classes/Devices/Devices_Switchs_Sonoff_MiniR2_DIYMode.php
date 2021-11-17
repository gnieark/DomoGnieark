<?php
class Devices_Switchs_Sonoff_MiniR2_DIYMode extends Devices_Switchs
{
    

    private $device_ip = "";
    private $device_port = 8081;
    private $is_Scheme_HTTPS = false;
    
    public function Get_status($params = [])
    {


    }
    public function set_device_ip($ip)
    {
        $this->device_ip = $ip;
        return $this;
    }
    public function set_device_port($port)
    {
        $this->device_port = $port;
        return $this;
    }
    public function set_device_scheme ($sheme)
    {
        $this->is_Scheme_HTTPS = ($sheme == "https");
        return $this;
    }
    public function __construct( $params = array() )
    {
        $this->load_params($params);
    }
    private function makeRequest($on)
    {
        $url = ($this->is_Scheme_HTTPS ? "https://" : "http://") . $this->device_ip . "/zeroconf/switch";
        $data = array(
            "deviceid"  => $this->device_id,
            "data"  => array(
                "switch"    => ($on? "on": "off")
            )

        );

        $data_string = json_encode($data);
        $ch=curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, array("customer"=>$data_string));
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER,
            array(
                'Content-Type:application/json',
                'Content-Length: ' . strlen($data_string)
            )
        );
        
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;

    }

    public function Off()
    {
        return $this->makeRequest(false);
    }
    public function On()
    {
        return $this->makeRequest(true);
    }

}
