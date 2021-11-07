<?php

use PHPUnit\Framework\TestCase;
require ("fakeClasses/Sample.php");
require ("fakeClasses/Sample1.php");
require ("fakeClasses/brokenClass.php");
class Post_Rule_Test extends TestCase
{


    //instancier un objet et voir si tout se passe bien
    public function testNewMenuItem(){
        //__construct($name, $shortName, $levelNeeded,$scrudClass)
        $item = new MenuItem ('Le menu factice de test', 'test', 'admin', 'Sample');
        $item = new MenuItem ('Le menu factice de test', 'test', 'admin', 'Sample1');
        $this->assertFalse(false);
    }

    public function testURL(){
        $item = new MenuItem ('Le menu factice de test', 'test', 'admin', 'Sample');
        $this->assertEquals($item->get_url(), "/index.php?menu=test");
    }

    public function testIsCurrent(){
        unset($_GET["menu"]);
        $item = new MenuItem ('Le menu factice de test', 'test', 'admin', 'Sample');
        $this->assertFalse($item->is_the_current_menu_item());
        $_GET["menu"] = "plop";
        $this->assertFalse($item->is_the_current_menu_item());
        $_GET["menu"] = "test";
        $this->assertTrue($item->is_the_current_menu_item());
    }

    public function testScrudClassMustExists()
    {
        try{
            $item = new MenuItem ('Le menu factice de test', 'test', 'admin', 'Ssqdtqazere');
            $this->fail("Expected exception");
        }catch(UnexpectedValueException $e){
            $this->assertEquals(0, $e->getCode());
        }

        try{
            $item = new MenuItem ('Le menu factice de test', 'test', 'admin', 'brokenClass');
            $this->fail("Expected exception");
        }catch(UnexpectedValueException $e){
            $this->assertEquals(0, $e->getCode());
        }
       
    }

    public function testMenuManagerCurrent(){
        //no defaults setteds
        unset($_GET["menu"]);

        $mm = new Menus_manager();

        $sample = array(
            "config"    => array(
                "name" => "Configuration",
                "default_level_needed"=> "admin",
                "CRUDclass" => "Sample"
            ),
            "main" => array (
                "name" => "Page d'accueil",
                "default_level_needed" => "user",
                "CRUDclass" => "Sample1"
            ),
            "404"   => array(
                "name"  => "404",
                "default_level_needed"  => "guest",
                "CRUDclass" => "Sample"
            )
        );

        $mm->add_menus_items_from_structured_array($sample);

        try{
            $currentMenu = $mm->get_current_menu();
            $this->fail("Expected exception");
        }catch(UnexpectedValueException $e){
            $this->assertEquals(0, $e->getCode());
            $this->assertEquals("No current menu found, and no default menu setted", $e->getMessage());
        }
        
        //give a non existing menu
        try{
            $mm->set_defaultMenu('plop');
            $this->fail("Expected exception");
        }catch(UnexpectedValueException $e){
            $this->assertEquals(0, $e->getCode());
            $this->assertEquals("Given default Menu does not exists", $e->getMessage());  
        }

        try{
            $mm->set_defaultMenuIfGetEmpty('plop');
            $this->fail("Expected exception");
        }catch(UnexpectedValueException $e){
            $this->assertEquals(0, $e->getCode());
            $this->assertEquals("Given default Menu does not exists", $e->getMessage());  
        }

        $mm->set_defaultMenu('main');
        $this->assertEquals( $mm->get_current_menu()->shortName , "main");

        $_GET["menu"] = "ljkhgljgdjg";
        $mm->set_defaultMenu('main');
        $this->assertEquals( $mm->get_current_menu()->shortName , "main");

        $_GET["menu"] = "config";
        $mm->set_defaultMenu('main');
        $this->assertEquals( $mm->get_current_menu()->shortName , "config");
    }


}
