<?php

class DataList
{
    /*
    * Not usable, use childs class
    *
    */

    protected static $commonFields = array('id', 'created_by', 'created_time','updated_by','updated_time','active');
    protected static $specificFields = array(); //should be setted in chield class
    protected static $mandatoryFields = array();//should be setted in chield class
    public static function get_add_inputs($prefixes = ""){
        $returnArr = array();
        
        foreach(static::$specificFields as $field){
            
            $label = new XmlElement ("label");
            $label-> addAttribute( new XmlElementAttribute("for", $prefixes . $field));
            $label-> setContent($field);

            
            $inputEl = new XmlElement ("input");
            $inputEl -> addAttribute( new XmlElementAttribute("name", $prefixes . $field))
                   -> addAttribute( new XmlElementAttribute("id", $prefixes . $field))
                   -> addAttribute( new XmlElementAttribute("type", "text"))
                   ->enable_self_closure();
            if(in_array($field, static::$mandatoryFields))
            {
                $inputEl-> addAttribute( new XmlElementAttribute("required", null));
            }
            
            $returnArr[] = array(
                "label" => $label,
                "input" => $inputEl
            );

        }
        return $returnArr;
    }
    public static function get_table_name(){
        return static::$table;
    }
    public static function create_list_table(PDO $db)
    {
        $db->query(
            "CREATE TABLE " . static::$table . " ("
            . static::get_common_fields_create() . ","
            . static::get_specificics_fields_create() .","
            ."PRIMARY KEY (`id`),
              KEY `created_by` (`created_by`),
              KEY `updated_by` (`updated_by`),
              CONSTRAINT `" . static::$table . "_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
              CONSTRAINT `" . static::$table . "_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`));"
        );
    }
    protected static function get_specificics_fields_create()
    {
        $sqlLines = array();
        foreach(static::$specificFields as $field){
            $sqlLines[] = "`" . $field ."` text NOT NULL";
         }
         return implode(",", $sqlLines);
    }

    protected static function get_common_fields_create()
    {
        return "`id` int(11) NOT NULL AUTO_INCREMENT,
               `created_by` int(11) NOT NULL,
               `created_time` timestamp NOT NULL DEFAULT  CURRENT_TIMESTAMP,
               `updated_by` int(11) NOT NULL,
               `updated_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
               `active` tinyint(1) NOT NULL DEFAULT '1'";

    }

    public static function associativeArrayToWhereClause($arr)
    {
        $clauses = array();
        foreach($arr as $key => $value)
        {
            $clauses[] = $key . "='" . $value . "'"; 
        }
        return implode(" AND ",$clauses);
    }

    public static function associativeArrayToUpdateString($arr)
    {
        $clauses = array();
        foreach($arr as $key => $value)
        {
            $clauses[] = $key . "='" . $value . "'"; 
        }
        return implode(",",$clauses);
    }

    public static function addUpdateFields(User $user, $arrValues)
    {
        $arrValues['updated_by'] = $user->id;
        $arrValues['update_time'] = date('Y-m-d H:i:s');
        return $arrValues;
    }

    public static function checkfields($values)
    {
        $availableFields = array_merge(static::$commonFields, static::$specificFields);
        
        foreach (array_keys($values) as $key){
            if(!in_array($key,$availableFields)){
                echo $key;
                return false;
            }
        }
        return true;
    }

    public static function checkMandatoryFields($values)
    {
        foreach(static::$mandatoryFields as $mandatoryKey)
        {
            if(!isset($values[$mandatoryKey])){
                throw new \UnexpectedValueException(
                    "field " . $mandatoryKey . " Is mandatory"
                );
                return false;
            }
        }
        return true;
    }

    public static function get_ids($db, $activesOnly = false)
    {
        $sql = "SELECT id FROM ". static::$table
                .($activesOnly? " WHERE active='1'" : "")
                .";";
        $rs = $db->query($sql);

        $ids = array();
        while ($row = $rs->fetch() ) {
            $ids[]= $row['id'];
        }
        return $ids;
    }

    public static function get_next_id_from(PDO $db, $id, $activesOnly = false)
    {
        $sql = "SELECT MIN(id) FROM ". static::$table . " WHERE id > :id"
                .($activesOnly? " AND active='1'" : "")
                .";";
        $sth = $db->prepare($sql);
        $sth->execute( array(":id"   => $id) );
        if($rs = $sth->fetch())
        {
            return $rs[0];
        }
        return -1;
    }
    public static function get_previous_id_from(PDO $db, $id, $activesOnly = false)
    {
        $sql = "SELECT MAX(id) FROM ". static::$table . " WHERE id < :id"
                .($activesOnly? " AND active='1'" : "")
                .";";
        $sth = $db->prepare($sql);
        $sth->execute( array(":id"   => $id) );
        if($rs = $sth->fetch())
        {
            return $rs[0];
        }
        return -1;

    }

    //values is an associative array. Optionaly taking system fields (id, create_time etc...)
    public static function POST($db, User $user, $values)
    {

        if (self::checkfields($values) === false)
        {
            throw new \UnexpectedValueException(
                "Inconsistents fields ". json_encode($values)
            );
        }
        self::checkMandatoryFields ($values);

        //mettre les champs techniques
        $values["created_by"] = $user->get_id();  
        $values["updated_by"] = $user->get_id();  

        $keys = array_keys($values);
        $sql = "INSERT INTO " . static::$table . " (" . implode(",", $keys ) . ")"
                . " VALUES ( :" . implode(", :",$keys) . ");";

        $q = $db->prepare($sql);
        $q->execute($values);
        return $db->lastInsertId(); 

    }


    protected static function generate_sql_clauses_from_filter_arr($filters)
    {
        $customClauses = "";
        if(!empty($filters)){ 
            foreach($filters as $filterKey => $filterValue)
            {
                if((!in_array( $filterKey , static::$commonFields  ) &&  (!in_array( $filterKey , static::$specificFields  ))))
                {
                    throw new \UnexpectedValueException(
                        "The filter key " . $filterKey . " does not exists on table " . static::$table
                    );
                }
                $customClauses .= ' AND ' . static::$table . '.' . $filterKey .' = :'.$filterKey;
            }  
        }
        return $customClauses;
    }
    protected static function generate_dbo_array_from_filter_arr($filters)
    {
        
        $sqlVals = array();
        if(!empty($filters)){ 
            $sqlVals = array();
            foreach($filters as $filterKey => $filterValue)
            {
                if((!in_array( $filterKey , static::$commonFields  ) &&  (!in_array( $filterKey , static::$specificFields  ))))
                {
                    throw new \UnexpectedValueException(
                        "The filter key " . $filterKey . " does not exists on table " . static::$table
                    );
                }
                $sqlVals[":".$filterKey ] = $filterValue;
            }
        }
        return $sqlVals;
    }
    /*
    * Par defaut, retourne un array contenant la liste, 
    * sans jointure sur les champs spécifiques, mais avec jointure sur la table users
    * $filters est un array associatif champs / value
    */

    public static function GET($db, User $user = null, $activesOnly = false, $filters = null)
    {

        $tbl = static::$table;
        
        $strSpecificsFields = empty(static::$specificFields)? "" : $tbl . "." . implode( ", " . $tbl .".", static::$specificFields) . ",";

        $sql = "SELECT 
                    $strSpecificsFields
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
                    users as creator,
                    users as updator
                WHERE
                    $tbl.created_by = creator.id
                AND $tbl.updated_by = updator.id
                ". ($activesOnly? "AND $tbl.active='1'" : "")
                .self::generate_sql_clauses_from_filter_arr($filters);
     

        $rs = $db->prepare($sql);
        $rs->execute(self::generate_dbo_array_from_filter_arr($filters));
        return $rs->fetchAll(PDO::FETCH_ASSOC);

    }
    /*
    * Modifie un enregistrement
    * l'id ne peut pas etre changé
    * Peuvent etre changées tous les champs spécifiques, et le champs active
    */
    public static function PATCH($db, User $user, $values)
    {
        if(!isset($values["id"])){
            throw new \UnexpectedValueException("The associative array must contains an 'id' key");
        }

        $data["id"]  =   $values["id"];
        $data["updator_id"] = $user->get_id();

        $speFields = array();
        $keysList = static::$specificFields;
        $keysList[] = "active";       
        foreach($keysList as $key){
            if( isset($values[$key]) )
            {
                $speFields[] = $key . "=:" . $key;
                $data[$key]  = $values[$key];
            }
        }

        
        $sql = "UPDATE " . static::$table . "
                SET updated_time=NOW(),
                    updated_by=:updator_id, 
                    " . implode(",", $speFields) . "
                WHERE id=:id;";


        $db->prepare($sql)->execute($data);
       
    }
    public static function PUT($db, User $user, $values)
    {
        
    }
    public static function DELETE($db, User $user, $values)
    {
        $sql = " DELETE FROM " . static::$table . " WHERE id=:id;";
        $db->prepare($sql)->execute(array("id"  => $values));
    }

}
