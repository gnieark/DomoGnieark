<?php
class C400 extends Route
{
    protected static $displayOnPage = false;

    static public function send_content(PDO $db, User $user)
    {
        header("HTTP/1.0 400 Not Found");
        echo "<html><header><title>400 Unconsistent request</title></header><body><h1>400 Unconsistent request</h1></body></html>";
    }

}
