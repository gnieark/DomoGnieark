<?php

class User_Manager
{

    private static $table_users = 'users';
    private static $table_groups = 'groups';

    const QUERY_CREATE_TABLE_USERS = "
    CREATE TABLE %table_users% (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `login` varchar(16) NOT NULL,
        `display_name` text NOT NULL,
        `email` text,
        `auth_method` enum('local','ldap','cas','none') NOT NULL,
        `password` char(128),
        `external_uid` char(60),
        `admin` tinyint(1) NOT NULL DEFAULT 0,
        `active` tinyint(1) NOT NULL DEFAULT '1',
        `created_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `created_by` int(11),
        `updated_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
        `updated_by` int(11),
        PRIMARY KEY (`id`)
    );
    
    ";
    const QUERY_CREATE_SYSTEM_USER = "INSERT INTO  %table_users% 
                                (id,login,display_name,auth_method,active,created_time,created_by)
                                VALUES (0,'','SYSTEM','none',0, NOW(),0);";

    const QUERY_CREATE_TABLE_GROUPS = "
    CREATE TABLE %table_groups% ( 
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` text NOT NULL,
        `active` tinyint(1) NOT NULL DEFAULT '1',
        `created_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `created_by` int(11) NOT NULL,
        `updated_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
        `updated_by` int(11) NOT NULL,
        PRIMARY KEY (`id`)
    );
    ";

    const QUERY_CREATE_DEFAULT_GROUPS = "
    INSERT INTO %table_groups% (id,name,active,created_time,created_by,updated_time,updated_by)
    VALUES ('0','Administrateurs','1',NOW(),'0',NOW(),'0'),
           ('1','Responsables sécurité','1',NOW(),'0',NOW(),'0'),
           ('2','Agent de prévention HSE','1', NOW(),'0',NOW(),'0')
    ;
    ";

    const QUERY_CREATE_REL_USERS_GROUPS = "  
    CREATE TABLE `%table_users%_%table_groups%_rel` (
        `user_id` int(11) NOT NULL,
        `group_id` int(11) NOT NULL,
        PRIMARY KEY (`user_id`,`group_id`),
        KEY `users_id` (`user_id`),
        KEY `group_id` (`group_id`),
        CONSTRAINT `%table_users%_%table_groups%_rel_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `%table_users%` (`id`),
        CONSTRAINT `%table_users%_%table_groups%_rel_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `%table_groups%` (`id`)
    );
    ";


    public static function get_table_users_str()
    {
        return self::$table_users;
    }
    public static function get_table_groups_str()
    {
        return self::$table_groups;
    }


    private static function check_if_array_keys_exists($mandatory_keys, $arrayToTest)
    {
        $givenKeys = array_keys( $arrayToTest);
        foreach($mandatory_keys as $fieldKey){
            if(!in_array($fieldKey, $givenKeys)){
                throw new \UnexpectedValueException("Mandatory key missing " . $fieldKey);
            }
        }
    }

    public static function edit_user_on_db_by_external_id(PDO $db, $authmethod,$externalid,$properties)
    {
        $sql = " SELECT id FROM ". self::get_table_users_str() 
        . " WHERE auth_method=:authmethod"
        . " AND external_uid=:externalid;";

        $rs = $db->prepare($sql);
        $rs->execute( array( ":authmethod" => $authmethod, ":externalid" => $externalid) );
        $rep = $rs->fetchAll();
        if(empty($rep)){
            return;
        }
        self::edit_user_on_db($db, $rep[0][0], $properties);
    }

    public static function edit_user_on_db(PDO $db, $userId, $properties)
    {
        static $fields = array('id','login','display_name','email','auth_method','password',
                                'external_uid','admin','active','created_time','created_by',
                                'updated_time','updated_by');
        

        if( isset( $properties["groups"] ) )
        {
            $db->beginTransaction();
            //delete all user groups`%table_users%_%table_groups%_rel`
            $sql = "DELETE FROM " . self::$table_users . "_" . self::$table_groups . "_rel WHERE user_id=:givenId";
            
            $rs = $db->prepare($sql);
            $rs->execute( array(":givenId" => $userId) );

            //add groups to user
            $sql = "INSERT INTO " . self::$table_users . "_" . self::$table_groups . "_rel (user_id,group_id) 
                    VALUES (:givenId,:group) ON DUPLICATE KEY UPDATE user_id=user_id";
           $rs = $db->prepare($sql);
            foreach(array_keys($properties["groups"]) as $group_id)
            {
                $rs->execute( 
                    array(
                        ":givenId"  => $userId,
                        ":group"  => $group_id
                    )
                );
            }
            $db->commit();
            unset($properties["groups"]);
        }

        unset($properties["groups"]);

        $sqlSetters = array();
        $values = array(":givenId" => $userId);
        foreach ($properties as $key=>$propertie)
        {
            if(!in_array($key,$fields)){
                throw new \UnexpectedValueException("Unknowed field key given " . $key);
            }
            $sqlSetters[]= $key . "=:" . $key; 
            $values[":" . $key] = $propertie;
        }

        $sql = "UPDATE " . self::$table_users . " SET "
                .implode(",", $sqlSetters )
                ." WHERE id=:givenId;";

        $rs = $db->prepare($sql);
        $rs->execute($values);       

    }

    /*
    * Save an user on the local db. 
    * Properties is an associative array. Mandatory fields depend on auth method
    */

    public static function add_user_on_db(PDO $db, $auth_method , $properties)
    {
        $tableUsers = self::get_table_users_str();
        switch($auth_method)
        {
            case "ldap":
            case "cas":
           
                
                self::check_if_array_keys_exists( array("display_name","email","external_uid") , $properties );
    
                $sql =  "INSERT INTO " . $tableUsers . " (login,display_name, email, auth_method, external_uid, created_time, created_by, admin,active)
                            VALUES ("    
                             . "'',"
                             . $db->quote( htmlentities( $properties["display_name"] ) ) . ","
                             . $db->quote( $properties["email"] ) . ","
                             . $db->quote( $auth_method ) . ","
                             . $db->quote( $properties["external_uid"] ) . ","
                             . "NOW(), 0, "
                             . $db->quote( isset( $properties["admin"]  ) ?  $properties["admin"] : 0 ) .","
                             . "1);";

                $stmt = $db->prepare( $sql );
                $stmt->execute();            
                return $db->lastInsertId(); 

                break;

            case "local":

                check_if_array_keys_exists( array("login", "display_name","email","password") , $properties );
                $stmt = $db->prepare(
                    "INSERT INTO " . $tableUsers . " 
                    (login, display_name,email, auth_method,password,admin,active) 
                    VALUES 
                    (:login, :display_name, :email, 'local', :password, :admin, :active)"
                );            
                $stmt->execute(
                    array(
                        ":login"        => $properties["login"],
                        ":display_name" => $properties["display_name"],
                        ":email"        => $properties["email"],
                        ":password"     =>  password_hash($properties["password"], PASSWORD_BCRYPT),
                        ":admin"        => isset( $properties["admin"]  ) ?  $properties["admin"] : 0,
                        ":active"       => isset($properties["active"]) ? $properties["active"] : 1
                        
                    )
                );
                return $db->lastInsertId(); 
                break;
            default:
                 throw new \UnexpectedValueException("Invalid auth method " . $auth_method );
                break;
        }
  
    }

    public static function get_table_users_groups_rel_str()
    {
        return self::get_table_users_str() . "_" . self::get_table_groups_str() ."_rel";
    }

    
    public static function create_local_tables(PDO $db)
    {
        $searched = array('%table_users%','%table_groups%');
        $replace = array(self::$table_users,self::$table_groups);

        $queries = array(
            str_replace($searched,$replace,self::QUERY_CREATE_TABLE_USERS),
            str_replace($searched,$replace,self::QUERY_CREATE_SYSTEM_USER),
            str_replace($searched,$replace,self::QUERY_CREATE_TABLE_GROUPS),
            str_replace($searched,$replace,self::QUERY_CREATE_REL_USERS_GROUPS),
            str_replace($searched,$replace,self::QUERY_CREATE_DEFAULT_GROUPS)
        );
        foreach($queries as $query)
        {
            $rs = $db->query($query);
            #if($rs === false){
            #    throw new \UnexpectedValueException("SQL ERROR ON QUERY " . $query );
            #}
        }
    }


    public function authentificate(PDO $db, STRING $login, STRING $password){

        //test sql
        $user = new User_Sql($db);
        $user->authentificate($login,$password);
        if($user->is_connected()){
            return $user;
        }
            
        //test LDAP
        if(Config::get_option_value("LDAP","active",true)){
            $user = new User_LDAP($db);
            $user->authentificate($login,$password);
            if($user->is_connected()){
                return $user;
            }
        }
   
        $user = new User($db);
        return $user;
    }
    public static function add_user_to_group(PDO $db, $userId,$groupId)
    {
        $tableRel = self::get_table_users_groups_rel_str();
        $sql = "INSERT INTO $tableRel (user_id,group_id) VALUES (:userid, :groupid)";
        $rs = $db->prepare($sql);

        $rs->execute( array(
                ":userid" => $userId,
                ":groupid" => $groupId
        ));

    }
    public static function del_user_from_group(PDO $db, $userId,$groupId)
    {
        $tableRel = self::get_table_users_groups_rel_str();
        $sql = "DELETE FROM $tableRel WHERE user_id=:userid AND group_id=:groupid;";
        $rs = $db->prepare($sql);
        $rs->execute( array(
                ":userid" => $userId,
                ":groupid" => $groupId
        ));
    }

    public static function get_group_id_by_name(PDO $db, $userId,$groupname)
    {
        $sql = "SELECT id FROM " . self::$table_groups ." WHERE name=:name;";
        $rs = $db->prepare($sql);
        $rs->execute( array (":name"    => $groupname ));

        if( !$arr = $rs->fetchAll() ){
            return false;
        }else{
            return $arr[0];
        }
    }
    public static function create_group(PDO $db, $userId,$groupName)
    {
        if( self::get_group_id_by_name($db, $userId, $groupName) !==  false)
        {
            //the group already exists
            return false;
        }
 

        $sql = "INSERT INTO " . self::$table_groups . "
        (name,active,created_time,created_by,updated_time,updated_by) 
        VALUES (
            " .$db->quote($groupName) . ",
            1,
            NOW(),
            " .$db->quote($userId) . ",
            NOW(),
            " .$db->quote($userId) . "
        );";
        $db->query($sql);
        return $db->lastInsertId();
    }
    public static function get_existing_ldap_uids (PDO $db, $onlyActives=false )
    {
        $sql = "SELECT external_uid FROM "  . self::get_table_users_str() 
                . " WHERE auth_method='ldap'"
                . ($onlyActives ? " AND active='1'" : "");

        $uuids = array();
        $rs = $db->prepare($sql);
        $rs->execute();
        $arr = $rs->fetchAll();
        foreach($arr as $ar)
        {
            $uuids[] = $ar[0];
        }
        return $uuids;

    }

    //$uids is a list containing the uids that have to be active
    public static function active_or_inactive_ldap_users(PDO $db, $uids)
    {
        if (empty($uids))
        {
            return;
        }
        $sql = "UPDATE " . self::get_table_users_str() . " 
            SET active=( external_uid IN  ('" . implode("','" , array_map( array($db,'quote') , $uids ) ) . "'))
            WHERE auth_method='ldap'";
        $rs = $db->prepare($sql);
        $rs->execute();
    }

    public static function get_users_by_Id(PDO $db, $id)
    {
        $us = self::get_users_list($db, false, null, array($id));
        return $us[0];
    }
    /*
    *   Return an array of users objects.
    *   is_connected for each ones is set to false
    */
    public static function get_users_list(PDO $db, $activesOnly = true, $groups = null, $ids = null, $external_uids = null, $auth_methods = null)
    {
        $tableUsers = self::get_table_users_str();
        $tableGroups = self::get_table_groups_str();
        $tableRel = $tableUsers . "_" . $tableGroups ."_rel";

        //conditions
        $conditions = array();
        if($activesOnly){
            $conditions[] = "$tableUsers.active='1'";
        }
        if(!is_null($groups)){
            //$ groups is an untrusted entry; check it before using it on a non a query
            foreach($groups as $group){
                if (!preg_match('/^[0-9]+$/',$group)){
                    throw new \UnexpectedValueException('$groups must be a list  containing only digits'. $group);
                }
            }
            $conditions[] = "$tableUsers.id IN(
                                                    SELECT $tableUsers.id
                                                    FROM $tableUsers, $tableRel
                                                    WHERE $tableRel.user_id = $tableUsers.id
                                                    AND $tableRel.group_id IN ('" . implode("','",$groups) ."')
                                                )";      
        }
        if(!is_null($ids)){
            $conditions[] = "$tableUsers.id IN ('" . implode(',', $ids) ."')"; 
        }
        if(!is_null($external_uids)){
            $conditions[] = "$tableUsers.external_uid IN ('" . implode(',', $external_uids) ."')"; 
        }
        if(!is_null($auth_methods)){
      
            $conditions[] = "$tableUsers.auth_method IN ('" . implode (',' , $auth_methods ) ."')"; 
        }
        

        $sql = "
            SELECT 
                $tableUsers.id as id,
                $tableUsers.login as login,
                $tableUsers.display_name as display_name,
                $tableUsers.email,
                $tableUsers.auth_method as auth_method,
                $tableUsers.external_uid as external_id,
                $tableUsers.admin as is_admin,
                $tableUsers.active as active,
                GROUP_CONCAT(groupsrel.group_id SEPARATOR \",\") as groups_ids,
                GROUP_CONCAT(groups.name SEPARATOR \",\") as groups_name
            FROM
                $tableUsers LEFT JOIN  $tableRel as groupsrel ON groupsrel.user_id = $tableUsers.id
                    LEFT JOIN $tableGroups as groups ON groups.id = groupsrel.group_id"
            .(empty($conditions) ? "" :  " WHERE " . implode(" AND ", $conditions))
            ." GROUP BY $tableUsers.id; ";
   
        $rs = $db->query($sql);
    
        $list = array();
        while ($r = $rs->fetch())
        {

            $groups_id = explode(",", $r["groups_ids"]);
            $groups_names = explode(",", $r["groups_name"]);
            $groups = array();
            for($i = 0; $i < count($groups_id); $i++){
                $groups[ $groups_id[$i] ] = $groups_names[$i];
            }

            $user = new User($db);

            $user-> set_properties(
                array(
                    "login" => $r["login"],
                    "id"    => $r["id"],
                    "display_name"  => $r["display_name"],
                    "email"         => $r["email"],
                    "external_id"   => $r["external_id"],
                    "auth_method"   => $r["auth_method"],
                    "is_admin"      => ($r["is_admin"] == "1"),
                    "groups"        => $groups
                )
            );
            $list[] = $user;
        }
        return $list;
    }
}
