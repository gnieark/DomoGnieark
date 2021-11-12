<?php
class C404 extends Route
{
    protected static $displayOnPage = false;

    static public function send_content(PDO $db, User $user)
    {
        header("HTTP/1.0 404 Not Found");
        echo "<html><header><title>404 Not Found</title></header><body><h1>404 Not Found</h1></body></html>";
    }

}
