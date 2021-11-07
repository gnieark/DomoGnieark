<?php

//autoload classes
spl_autoload_register(function ($class_name) {
    $classFolders = array   ("../classes/"
                            ,"../classes/Devices/"
                            ,"../classes/Template/");
    foreach($classFolders as $folder)
    {
        if(file_exists( $folder . $class_name . '.php')){
            include $folder. $class_name . '.php';
            return;
        }
    }
});