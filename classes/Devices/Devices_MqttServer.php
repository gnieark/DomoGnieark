<?php
class Devices_MqttServer extends Devices
{

    private $phpMQTT = null;
    private $mqttConnected = false;

    
    public function __construct( $params = array() )
    {
        // public function __construct($address, $port, $clientid, $cafile = null)

        if(!isset($params["device_ip"])){
            throw new \UnexpectedValueException(
                "params must contain a key value for device_ip. For others field, if not given default values will be used"
            );
        }
        $port = empty($params["device_port"]) ? 1883: $params["device_port"];
        $clientId = empty($params["client_id"])? "domoGnieark" : $params["client_id"];

        $this->phpMQTT =new Bluerhinos\phpMQTT($params["device_ip"], $port, $clientId);
        if(!$this->phpMQTT ->connect(true, NULL, $params["username"], $params["password"])) {
            $this->mqttConnected = false;
        }else{
            $this->mqttConnected = true;
        }
        
        $this->load_params($params);

    }
    public function get_snippet_as_XMLelement()
    {
        $container = new XmlElement( $this->containerHtmlType );
        $title = new XmlElement("h3");
        $title->setContent($this->get_device_name());
        $container->addChild($title);
        $message = new XmlElement('p');
        $message->setContent('Mqtt broker ' . ($this->mqttConnected? 'connected' : 'not available') ) ;
        $container->addChild($message);
        return $container;
    }
    static public function procMsg($topic, $msg){
		echo 'Msg Recieved: ' . date('r') . "\n";
		echo "Topic: {$topic}\n\n";
		echo "\t$msg\n\n";
    }

    public function findDevices()
    {
        
        if(!$this->mqttConnected === false)
        {
            //testing
            echo "hey";
            $topics['#'] = array('qos' => 0,'function' => 'Devices_MqttServer::procMsg');
            $this->phpMQTT->subscribe($topics, 0);
            //while($this->phpMQTT->proc()) {

            //}
            
            $this->phpMQTT->close();
            
            die();
        }

    }

}