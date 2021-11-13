<?php
class DataList_devices_categories extends DataList
{
    protected static $table = "devices_categories";
    protected static $specificFields = array('name', 'display_name');
    protected static $mandatoryFields = array('name', 'display_name');
}
