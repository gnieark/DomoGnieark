<?php
class C404 extends Route
{
    protected static $displayOnPage = false;

    static public function send_content(PDO $db, User $user)
    {
        header("HTTP/1.0 404 Not Found");
        $tpl = new TplBlock();
        echo $tpl->applyTplFile("../templates/404.html");
    }

}
