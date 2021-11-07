<?php

class User
{
    protected $is_connected = false;
    protected $login;
    protected $external_id; //the user's ID on the external auth system (Object SID on LDAP)
    protected $id; //the internal id to store locally user's datas
    protected $display_name;
    protected $auth_method;
    protected $email;
    protected $is_admin = false;
    protected $groups = null;

    protected $db;

    public function __sleep(){
        return array('is_connected','external_id','id','display_name','email','auth_method','groups','is_admin','login');
    }
    
    public function get_display_name()
    {
        return $this->display_name;
    }
    public function get_email()
    {
        return $this->email; 
    }
    public function get_id($force = false)
    {
        if($this->is_connected || $force){
            return $this->id;
        }
        return false;
    }
    public function is_admin(){
        return $this->is_admin;
    }
    public function is_connected()
    {
        return $this->is_connected;
    }
    public function get_external_id()
    {
        return $this->external_id;
    }
    public function get_auth_method()
    {
        if($this->is_connected){
            return $this->auth_method;
        }
        return false;
    }
    public function load_groups()
    {
        $this->groups = array();
        $tableUsers = User_Manager::get_table_users_str();
        $tableGroups = User_Manager::get_table_groups_str();
        $tableRel = $tableUsers . "_" . $tableGroups ."_rel";

        $sql = "
            SELECT 
                $tableRel.group_id as id,
                $tableGroups.name as name
            FROM
                $tableRel, $tableGroups
            WHERE $tableRel.group_id = $tableGroups.id
            AND $tableRel.user_id=:id;
        ";
        $rs = $this->db->prepare($sql);
        $rs->execute( array(":id" => $this->get_id()) );
        $groups = $rs->fetchAll(PDO::FETCH_ASSOC);
        foreach($groups as $group)
        {
            $this->groups[ $group["id"] ] = $group["name"];
        }
    }
    public function get_groups($forceRefresh = false)
    {
        if(is_null($this->groups) || $forceRefresh) {
            //les groupes n'ont pas été initialisés pour cet user
            $this->load_groups();
        }
	    return $this->groups;
    }
    public function is_in_group($group_id)
    {
        return isset($this->get_groups()[$group_id]);
    }
    public function set_db(PDO $db){
        
        $this->db = $db;
    }

    public function __construct(PDO $db){
        $this->db = $db;
    }

    /*
    *
    */
    public function set_properties($properties)
    {
        $setableProperties = array("login","external_id","id","display_name","email","auth_method","is_admin","groups");
        foreach ($setableProperties as $setableProperty){
            if(isset($properties[$setableProperty])){
                $this->$setableProperty = $properties[$setableProperty];
            }
        }
        return $this;
    }
   
}
