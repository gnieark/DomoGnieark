<?php
class Devices_Switchs_Sonoff_MiniR2_DIYMode extends Devices_Switchs
{
    

    private $device_ip = "";
    private $device_port = 8081;
    private $is_Scheme_HTTPS = false;
    private $device_own_id = ""; //The device id returned by the device, not the one used for devices management class.
    
    public function get_status()
    {
        
        $url = ($this->is_Scheme_HTTPS ? "https://" : "http://") . $this->device_ip . ":" . $this->device_port ."/zeroconf/info";
       
        $data = array(
            "deviceid"  => "",
            "data"  => array()
        );
        $data_string = json_encode($data);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER,
            array(
                'Content-Type:application/json',
                'Content-Length: ' . strlen($data_string)
            )
        );
        
        $result = curl_exec($ch);
        
        curl_close($ch);
        if(!$response = json_decode($result, true)){
            return array("status"   => "error"
                        ,"error"    => 1
                        ,"message" => "Device did not awnser on JSON format"
                        );
        }

        if(!isset($response["error"])){
            return array("status"   => "error"
                        ,"error"    => 2
                        , "message" => "Device made a response on JSON Format but fields are missing"
                        );
        }
        if($response["error"] <> 0){
            return array("status"   => "error"
                        ,"error"    => 3
                        , "message" => "Device returned errod code " . $response["error"]
                        );
        }
        if(  
                (!isset($response["data"]["deviceid"]))
            ||  (!isset($response["data"]["switch"]))
            ){
                return array("status"   => "error"
                            ,"error"    => 4
                            , "message" => "Device seems OK, but some fields are missings on his response"
                            );

            }
        //could by usefull later:
         $this->device_own_id = $response["data"]["deviceid"];

         return array("status"   => $response["data"]["switch"]
                     ,"error"    => 0
                     );


    }
    public function get_device_own_id()
    {
        if(empty($this->device_own_id)){
            $this->get_status();
        }
        return $this->device_own_id;
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
    public function makeRequest($targetStatus)
    {
        $url = ($this->is_Scheme_HTTPS ? "https://" : "http://") . $this->device_ip .  ":" . $this->device_port ."/zeroconf/switch";
     

        $data = array(
            "deviceid"  => $this->get_device_own_id(),
            "data"  => array(
                "switch"    => $targetStatus
            )

        );
        
        $data_string = json_encode($data);
        $ch=curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER,
            array(
                'Content-Type:application/json',
                'Content-Length: ' . strlen( $data_string )
            )
        );
        $result = curl_exec($ch);
        if(!$response = json_decode($result, true)){
            return false;
        }
        if(!isset($response["error"])){
            return false;
        }
        if($response["error"] <> 0){
            
            return false;
        }
        
        return true;
    }

    public function Off()
    {
        return $this->makeRequest("Off");
    }
    public function On()
    {
        return $this->makeRequest("On");
    }

}
