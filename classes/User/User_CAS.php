<?php

class User_CAS extends User {


    /*
    * Return the user id if OK, return false if user not known.
    */
    private function check_if_users_exists_in_db($CASattritutes)
    {
                $UIDKey = Config::get_option_value("CAS","ATTRIBUTE_KEY_USER_ID",true);
             
                if(!isset( $CASattritutes[ $UIDKey ] ))
                {
                    throw new \UnexpectedValueException('The key given for user Id'. $UIDKey . 'does not exists on CAS attributes');
                }

                $external_uid = $CASattritutes[$UIDKey];

                $us = User_Manager::get_users_list($this->db, true, null, null, array($external_uid), array("cas"));
  
                return (empty($us)? false : $us[0] -> get_id(true));
    }
    
    private function load_user_from_CAS_attributes($attributes)
    {
        $user_id = $this->check_if_users_exists_in_db($attributes);



        if( $user_id === false)
        {
            //le créer
            $clean_attributes = array(
                "display_name"  => $attributes[ Config::get_option_value("CAS","ATTRIBUTE_KEY_FIRST_NAME",true)   ] . " " 
                                   . $attributes[ Config::get_option_value("CAS","ATTRIBUTE_KEY_LAST_NAME",true)    ],
                "email"         =>  $attributes[ Config::get_option_value("CAS","ATTRIBUTE_KEY_EMAIL",true)  ],
                "external_uid"  => $attributes[ Config::get_option_value("CAS","ATTRIBUTE_KEY_USER_ID",true)  ]
            );

           $user_id = User_Manager::add_user_on_db($this->db, 'cas' ,$clean_attributes );
        }else{
            //le mettre à jour (TO DO)

        }

        $user = User_Manager::get_users_by_Id($this->db, $user_id);

        $this->is_connected = true;
        $this->display_name = $user->get_display_name();
        $this->id = $user->get_id(true);
        $this->email = $user->get_email();
        $this->auth_method = 'cas';
        $this->is_admin = $user->is_admin();
        $this->external_id = $user->get_external_id();
        $this->is_connected = true;

        return $this;

    }

    public static function logout(){

        phpCAS::client(
            //Config::get_option_value("CAS","server_version",true),
            SAML_VERSION_1_1,
            Config::get_option_value("CAS","server_hostname",true),
            intval( Config::get_option_value("CAS","server_port",true) ),
            Config::get_option_value("CAS","server_uri",true)
        );
        phpCAS::setCasServerCACert(Config::get_option_value("CAS","server_ca_cert_path",true));

        if(!empty(Config::get_option_value("CAS","CURLOPT_PROXY",true))){
            phpCAS::setExtraCurlOption(CURLOPT_PROXY,Config::get_option_value("CAS","CURLOPT_PROXY",true));
        }

        phpCAS::logoutWithRedirectService(Config::get_option_value("SYSTEM","base_url",true));
      
    }

    public function __construct($db)
    {
        $this->db = $db;

        phpCAS::client(
            SAML_VERSION_1_1,
            Config::get_option_value("CAS","server_hostname",true),
            intval( Config::get_option_value("CAS","server_port",true) ),
            Config::get_option_value("CAS","server_uri",true)
        );
        phpCAS::setCasServerCACert(Config::get_option_value("CAS","server_ca_cert_path",true));

        if(!empty(Config::get_option_value("CAS","CURLOPT_PROXY",true))){
            phpCAS::setExtraCurlOption(CURLOPT_PROXY,Config::get_option_value("CAS","CURLOPT_PROXY",true));
        }
    
        phpCAS::forceAuthentication();

        $attributes = phpCAS::getAttributes();

        //verif filters

        if( !empty( Config::get_option_value("CAS","ATTRIBUTE_TO_FILTER",true))  )
        {
            if(!isset($attributes[ Config::get_option_value("CAS","ATTRIBUTE_TO_FILTER",true)  ])){
                C405::send_content($db, null);
            }        
            if(!preg_match(Config::get_option_value("CAS","FILTER_PATTERN",true), $attributes[ Config::get_option_value("CAS","ATTRIBUTE_TO_FILTER",true) ]  ))
            {
                return false;
            }
        }

        return $this->load_user_from_CAS_attributes($attributes);
    }
}
