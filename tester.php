
<html>
<body bgcolor="aqua">
<?php
echo'<h1>hellooo</h1> ';


/**
 * Created by PhpStorm.
 * User: netstager
 * Date: 25/11/14
 * Time: 4:09 PM
 */
require('asana/controller/AdminController.php');

$adminController= new AdminController();


$adminController->getTaskTime('15576272623581');

echo "helloo";

?>
