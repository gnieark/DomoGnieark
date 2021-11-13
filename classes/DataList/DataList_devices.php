<?php
class DataList_Devices extends DataList
{

    protected static $table = "devices";

    protected static $specificFields = array('display_name','model_id','description','configuration');
    protected static $mandatoryFields = array('display_name','model_id');

   
    protected static function get_specificics_fields_create()
    {
        return "
            `display_name` text NOT NULL,
            `model_id` int(11) NOT NULL,
            `description` text DEFAULT '',
            `configuration` text DEFAULT ''
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
        KEY `category_id` (`model_id`),
        CONSTRAINT `" . static::$table . "_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
        CONSTRAINT `" . static::$table . "_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`),
        CONSTRAINT `" . static::$table . "_ibfk_3` FOREIGN KEY (`model_id`) REFERENCES `" . DataList_devices_models::get_table_name() . "` (`id`)
        );";
        $db->query($sql);
    }

    public static function GET($db, User $user = null, $activesOnly = false, $filters = null)
    {

        $tbl = static::$table;
        $tblCategories = DataList_devices_categories::get_table_name();
        $tblModels = DataList_devices_models::get_table_name();


        $strSpecificsFields = empty(static::$specificFields)? "" : $tbl . "." . implode( ", " . $tbl .".", static::$specificFields) . ",";

        $sql = "SELECT 
                    $strSpecificsFields
                    $tblModels.name as model_name,
                    $tblModels.display_name as model_display_name,
                    $tblCategories.name as category_name,
                    $tblCategories.display_name as category_display_name,
                    $tbl.id,
                    $tbl.created_by as created_by_id,
                    creator.display_name as created_by_name,
                    $tbl.created_time,
                    $tbl.updated_by as updated_by_id,
                    updator.display_name as updated_by_name,
                    $tbl.updated_time,
                    $tbl.active
                FROM
                    $tbl,
                    $tblModels,
                    users as creator,
                    users as updator
                WHERE
                    $tbl.created_by = creator.id
                AND $tbl.updated_by = updator.id
                AND $tbl.model_id = $tblModels.id
                AND $tblModels.category_id = $tblCategories.id
                ". ($activesOnly? "AND $tbl.active='1'" : "")
                .self::generate_sql_clauses_from_filter_arr($filters);
     
        $rs = $db->prepare($sql);
        $rs->execute(self::generate_dbo_array_from_filter_arr($filters));
        return $rs->fetchAll(PDO::FETCH_ASSOC);

    }

}