<?php
Class Dashboard extends Route
{
    protected static $displayOnPage = true;

    static public function get_content_html(PDO $db, User $user)
    {
        $tpl = new TplBlock();






        return $tpl->applyTplFile("../templates/dashboard.html");
    }


}