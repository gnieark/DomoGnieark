<?php
class DataList_devices_models extends DataList
{
    protected static $table = "devices_models";
    protected static $specificFields = array('name', 'display_name', 'category_id');
    protected static $mandatoryFields = array('name', 'display_name', 'category_id');


    protected static function get_specificics_fields_create()
    {
        return "
            `display_name` text NOT NULL,
            `category_id` int(11) NOT NULL
        ";
    }
    public static function create_list_table(PDO $db)
    {
        $sql =  "CREATE TABLE " . static::$table . " ("
        . static::get_common_fields_create() . ","
        . static::get_specificics_fields_create() .","
        ."PRIMARY KEY (`id`),
        KEY `created_by` (`created_by`),
        KEY `updated_by` (`updated_by`),
        KEY `category_id` (`category_id`),
        CONSTRAINT `" . static::$table . "_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
        CONSTRAINT `" . static::$table . "_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`),
        CONSTRAINT `" . static::$table . "_ibfk_3` FOREIGN KEY (`category_id`) REFERENCES `" . DataList_devices_categories::get_table_name() . "` (`id`)
        );";
        $db->query($sql);
    }
}
