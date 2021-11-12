<?php
class MenuItem
{

    const QUERYPREFIX = "/";
    private $name = NULL;
    public $shortName = NULL;
    private $levelNeeded = 'admin';
    private $groups_allowed = array();
    public $scrudClass = NULL;
    public $displayOnNav = false;


    public function is_the_current_menu_item()
    {
        
        if(preg_match("/^\/" . $this->shortName . "($|\/)/",  $_SERVER['REQUEST_URI'] ) )
            return true;
        return false;
    }
    
    public function get_name()
    {
        return $this->name;
    }

    public function get_url()
    {
        return self::QUERYPREFIX . urlencode($this->shortName);
    }
    public function is_user_allowed(User $user)
    {
        //echo "hey".$this->name." ". $this->levelNeeded;
        //var_dump($this->groups_allowed);
        if($user->is_admin())
            return true;
        if ($this->levelNeeded == 'user' && $user->is_connected())
        {
          
           
            if(empty($this->groups_allowed)){
                return true;
            }
            foreach($this->groups_allowed as $group_allowed){
                if($user->is_in_group($group_allowed)){
                    return true;
                }
            }
            return false;  
        }
            
        if( $this->levelNeeded == 'guest')
            return true;
        

        return false;
    }

    private function test_scrudClass($className)
    {
        $methodsNeeded = array(
                            'get_custom_js',
                            'get_custom_css',
                            'get_content_html',
                            'apply_post'
                        );

                        
        if( !class_exists($className) )
        {
            return false;
        }
        foreach( $methodsNeeded as $method )
        {
            if( !method_exists( $className, $method ) )
            {
                return false;
            }
        }
        return true;
    }

    public function __construct($name, $shortName, $levelNeeded,$scrudClass, $display_on_nav = false, $groups_allowed = array())
    {

        $this->name = $name;
        $this->shortName = $shortName;

        if(!in_array($levelNeeded, array('user','admin','guest'))){
            throw new \UnexpectedValueException(
                "third parameter must be 'admin' or 'user'. " . $levelNeeded . " given."
            );
        }

        if(!$this->test_scrudClass($scrudClass ))
        {
            throw new \UnexpectedValueException(
                "Class " . $scrudClass . " does not exists or doesnot have expected methods"
            );
        }

        $this->levelNeeded = $levelNeeded;
        $this->scrudClass = $scrudClass;
        $this->displayOnNav = $display_on_nav;
        $this->groups_allowed = $groups_allowed;
    }
   
    public function apply_post(PDO $db, User $user){
       return call_user_func_array( array( $this->scrudClass, 'apply_post'), array($db,$user));
    }
    public function get_custom_js(PDO $db, User $user){
        return call_user_func_array( array( $this->scrudClass, 'get_custom_js'), array($db,$user));
    }
    public function get_custom_css(PDO $db, User $user){
        return call_user_func_array( array( $this->scrudClass, 'get_custom_css'), array($db,$user));
    }
    public function get_content_html(PDO $db, User $user){
        return call_user_func_array( array( $this->scrudClass, 'get_content_html'), array($db,$user));
    }
    public function display_on_page(){
        return call_user_func_array( array( $this->scrudClass, 'display_on_page'),array());
    }
    public function get_custom_after_body_tag(PDO $db, User $user){
        return call_user_func_array( array( $this->scrudClass, 'get_custom_after_body_tag'), array($db,$user));
    }
    public function send_content(PDO $db, User $user){
        return call_user_func_array( array( $this->scrudClass, 'send_content'), array($db,$user));
    }
    
}
