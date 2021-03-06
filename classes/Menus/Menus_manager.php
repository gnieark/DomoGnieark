<?php

/*
* Manage and display menus items.
* Take care of current user ACLS
*/
class Menus_manager
{
    private $menusList = array();

    private $defaultMenu = ""; //A 404 menu is a good choice
    private $defaultMenuIfGetEmpty = ""; //The main page!

    public function set_defaultMenu($menuName)
    {
        
        foreach($this->menusList as $m)
        {
            if ($m->shortName == $menuName)
            {
                $this->defaultMenu = $menuName;
                return $this;
            }
        }
        throw new \UnexpectedValueException("Given default Menu does not exists");
        
    }

    public function set_defaultMenuIfGetEmpty($menuName)
    {
        foreach($this->menusList as $m)
        {
            if ($m->shortName == $menuName)
            {
                $this->defaultMenuIfGetEmpty = $menuName;
                return $this;
            }
        }
        throw new \UnexpectedValueException("Given default Menu does not exists");
    }

    public function add_menus_items_from_structured_array($array)
    {
      
        foreach($array as $shortName => $menuArr){
            $m = new MenuItem(
                $menuArr["name"],
                $shortName,
                $menuArr["default_level_needed"],
                $menuArr["CRUDclass"],
                (isset($menuArr["display_on_nav"]) ? $menuArr["display_on_nav"] : false ),
                (isset($menuArr["groups_allowed"]) ? $menuArr["groups_allowed"] : array() )
                
            );
            $this->menusList[] = $m;

            if(isset($menuArr["default_one"]) && $menuArr["default_one"] ){
                $this->defaultMenu = $shortName;
            }
            if(isset($menuArr["default_one_if_get_empty"]) && $menuArr["default_one_if_get_empty"] ){
                $this->defaultMenuIfGetEmpty= $shortName;
            }

        }
        return $this;

    }

    public function add_menus_items_from_json_file($file)
    {
        $arr = json_decode(file_get_contents($file),true);
        return $this->add_menus_items_from_structured_array($arr);
    }

    public function add_menu_item(MenuItem $menuItem, $default_one = false, $default_one_on_empty_get = false){
        $this->$menusList[] = $menuItem;
        if( $default_one ){
            $this->defaultMenu = $menuItem->shortName;
        }
        if( $default_one_on_empty_get ){
            $this->defaultMenuIfGetEmpty = $menuItem->shortName;
        }
        return $this;
    }

    /*
    * Retourne la liste des menus autoris??s ?? l'utilisateur courant.
    */
    public function get_user_menu_list(User $user, Bool $onlyDisplayablesItems = true)
    {
        $list = array();
        foreach( $this->menusList as $menuItem){
      
            if( $menuItem->is_user_allowed($user) && ( (! $onlyDisplayablesItems) || $menuItem->displayOnNav) ){
                $list[] = $menuItem;
            }
        }
        return $list; 
    }

    public  function load_menu_item_by_shortname(STRING $shortName)
    {
        foreach( $this->menusList as $menuItem){
            if($menuItem->shortName == $shortName){
                return $menuItem;
            }
        }
        return false;
    }

    public function get_current_menu()
    {

        preg_match("/^\/([a-zA-Z0-9_]*)($|\/)/", $_SERVER['REQUEST_URI'], $matches );

        $shortname = $matches[1];

        if ( $shortname == "" ){

            $m = $this->load_menu_item_by_shortname($this->defaultMenuIfGetEmpty);
            if($m === false)
            {
                $m = $this->load_menu_item_by_shortname( $this->defaultMenu);
                if($m === false){
                    throw new \UnexpectedValueException("No current menu found, and no default menu setted");
                }
  
            }
            return $m;
        }
        foreach($this->menusList as $m){
            if($m->shortName == $shortname){
                return $m;
            }
        }

        $m = $this->load_menu_item_by_shortname( $this->defaultMenu);
        if($m === false){
            throw new \UnexpectedValueException("No current menu found, and no default menu setted");
        }
        return $m ;
    }

}
