<?php
class User_LDAP extends User {

    public function authentificate($login,$password,$depth=0)
    {
        
        if(empty($password))
        {
            $this->is_connected = false;
            return $this;
        }

        $ldap = ldap_connect( 
            Config::get_option_value("LDAP","host",true)
        );
        $ldaprdn = $login. "@" . Config::get_option_value("LDAP","domain",true);
        
        $bind = @ldap_bind($ldap, $ldaprdn, $password);
        
        if ($bind) {
           //l'user est authentifi via ldap

            $result = ldap_search(
                $ldap,
                Config::get_option_value("LDAP","basedn",true),
                "(sAMAccountName=$login)"
            );
            
            if(!$result){
                echo Config::get_option_value("LDAP","basedn",true)." " . ldap_error($ldap); die();
            }

            ldap_sort($ldap,$result,"sn");
            $info = ldap_get_entries($ldap, $result);
            $userLdap = $info[0];
            
            $properties = array(
                "display_name" => utf8_encode($userLdap["displayname"][0]),
                "email" => (isset($userLdap["mail"]) ? utf8_encode($userLdap["mail"][0]) : ""),
                "external_uid" => self::SIDtoString( $userLdap["objectsid"][0] )
            );
            
            //tester si l'user existe dans la bdd locale
            $userLocal = User_Manager::get_users_list($this->db, true, null, null, array(self::SIDtoString($userLdap["objectsid"][0])), array("ldap"));

            if(empty($userLocal))
            {
                
                if ($depth == 0){
                   
                    // créer l'user
                                  
                    User_Manager::add_user_on_db($this->db, 'ldap' , $properties);
                    //self::refreshLdap($this->db);
                    @ldap_close($ldap);
                    return $this->authentificate($login,$password,1);

                }else{

                    $this->is_connected = false;
                    @ldap_close($ldap);
                    return $this;

                }
            }


            $this->id = $userLocal[0] -> get_id(true);
            //groupes memberof
            
            $groups = $this->get_groups_from_membersof_array($userLdap["memberof"]);
            foreach($groups as $groupname)
            {
                $groupId =  User_manager::get_group_id_by_name($this->db, 1,$groupname);
                if( $groupId === false )
                {
                    $groupId = User_manager::create_group($this->db, 1,$groupname);
                    $this->groups[ $groupId ] = $groupname;
                }

            }

            $this->display_name = $properties["display_name"];
            $this->email = $properties["email"];
            $this->external_uid = $properties["external_uid"];
            $this->auth_method = 'ldap';
            $this->is_admin = $userLocal[0] -> is_admin();
            $this->is_connected = true;

            @ldap_close($ldap); 
            return $this->save_ldap_properties();
        }

        $this->is_connected = false;
        return $this;
    }
    
    private function save_ldap_properties()
    {

        $properties = array(
            "display_name"  => $this->display_name,
            "email"         => $this->email,
            "external_uid"  => $this->external_uid,
            "groups"        => $this->groups
        );
   

        User_Manager::edit_user_on_db($this->db, $this->id, $properties);
        return $this;
    }

    /**
     * 
     *  Return autorization groups names regarding the array memberof
     *  witch come from ldap_query
     * 
     */
    private function get_groups_from_membersof_array($membersof)
    {
        $availableGroups = array( 
            Config::get_option_value("AllowedGroups","archivesEtrangersRouenGroup"),  
            Config::get_option_value("AllowedGroups","archivesEtrangersLeHavreGroup")
        );

        $groups = array();
        foreach($membersof as $memberElem)
        {
            foreach($availableGroups as $availableGroup)
            {
                if(strrpos($memberElem, 'CN=' . $availableGroup . "," ) !== false)
                {
                    $groups[] = $availableGroup;
                }
            }
        }
        return $groups;
    }

    public static function refreshLdap($db)
    {


        /**
         * Get a list of users from Active Directory.
         * Update local users table
         */


        $ldapProperties = Config::get_group_values("LDAP");
        
        //print_r($ldapProperties);
        //die();
        //update refresh Time
        $ldapProperties["lastrefresh"] = time();
        Config::save_config_file("LDAP",$ldapProperties, false);



        $ldap_connection = ldap_connect($ldapProperties["host"], $ldapProperties["port"]);
        if (FALSE === $ldap_connection){
            throw new \UnexpectedValueException("Echec de la connecion LDAP");
        }

        // We have to set this option for the version of Active Directory we are using.
        ldap_set_option($ldap_connection, LDAP_OPT_PROTOCOL_VERSION, 3) or die('Unable to set LDAP protocol version');
        ldap_set_option($ldap_connection, LDAP_OPT_REFERRALS, 0);

        if (ldap_bind($ldap_connection, $ldapProperties["user"], $ldapProperties["password"])){

            $sr=ldap_search($ldap_connection, $ldapProperties["basedn"], $ldapProperties["filter"], array("displayName","objectSid","DistinguishedName","mail") );
            $entries = ldap_get_entries($ldap_connection, $sr);

            //lister les uids
            $uids = array();
            foreach($entries as $user){
                $uids[] = self::SIDtoString($user ["objectsid"][0]);
            }
            User_Manager::active_or_inactive_ldap_users($db, $uids);

            //update or create the users
            $existingLdapUUIDS = User_Manager::get_existing_ldap_uids($db);

            foreach($entries as $user){
                
                $uid = self::SIDtoString($user ["objectsid"][0]);

                if(in_array( $uid, $existingLdapUUIDS) )
                {
                    //user exists
                 
                    User_Manager::edit_user_on_db_by_external_id(
                        $db,
                        'ldap',
                        $uid,
                        array(
                            "display_name" => $user ["displayname"][0],
                            "email"        => (isset($user ["mail"]) ? $user ["mail"][0] : "")
                        )
                    );
                }else{
                    //new user, create him
                    User_Manager::add_user_on_db(
                        $db, 
                        'ldap',
                        array("display_name" => $user ["displayname"][0], "email"=> (isset($user ["mail"]) ? $user ["mail"][0] : ""), "external_uid" => $uid)
                    );
                }


            }
        
        }else{


        }

    }
    public static function SIDtoString($ADsid)
    {
       $sid = "S-";
       //$ADguid = $info[0]['objectguid'][0];
       $sidinhex = str_split(bin2hex($ADsid), 2);
       // Byte 0 = Revision Level
       $sid = $sid.hexdec($sidinhex[0])."-";
       // Byte 1-7 = 48 Bit Authority
       for($i=1; $i < 8; $i ++)
       {
           if(!isset($sidinhex[$i])){
                $sidinhex[$i] = 0;
           }
       }
       $sid = $sid.hexdec($sidinhex[6].$sidinhex[5].$sidinhex[4].$sidinhex[3].$sidinhex[2].$sidinhex[1]);
       // Byte 8 count of sub authorities - Get number of sub-authorities
       $subauths = hexdec($sidinhex[7]);
       //Loop through Sub Authorities
       for($i = 0; $i < $subauths; $i++) {
          $start = 8 + (4 * $i);
          // X amount of 32Bit (4 Byte) Sub Authorities
          $sid = $sid."-".hexdec($sidinhex[$start+3].$sidinhex[$start+2].$sidinhex[$start+1].$sidinhex[$start]);
       }
       return $sid;
    }
}
