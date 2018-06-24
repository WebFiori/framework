<?php
require_once 'root.php';
$uri = filter_var($_SERVER['REQUEST_URI']);
$split = Router::splitURI($uri);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Index</title>
    </head>
    <body style="color:green;background-color: black">
        <?php
            Util::print_r($split);
        ?>
        <p>Hello Word! I'm A live!</p>
        <p>This is the index page of generic PHP web applications building template.</p>
        <?php
            echo Config::get();
            echo SiteConfig::get();
        ?>
        <p>Steps to Start Building Projects:</p>
        <ul>
            <li>Define Database Schema (Use the class 'MySQLQuery' as bases)</li>
            <li>Build The logic (inside the folder /functions'</li>
            <li>Define your APIs (Use the class 'API' as bases.)</li>
            <li>Update 'AutoLoader' to load your classes as needed.</li>
            <li>Build Web Pages UI (Use the class 'PageAttributes' as bases.)</li>
        </ul>
    </body>
</html>
