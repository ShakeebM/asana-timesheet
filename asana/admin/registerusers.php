<?php
/**
 * Created by PhpStorm.
 * User: netstager
 * Date: 18/09/14
 * Time: 5:49 PM
 */

require('../controller/Controller.php');
$DBClass= new Controller();
$data = json_decode( file_get_contents('php://input') );
$id=$data->userid;
$username=$data->email;
$password=md5($data->password);

$nickname=$data->firstname;

    $query="INSERT INTO `login` VALUES ('$id','$username','$password')";
    $DBClass->connectDB($query);
    $updater="UPDATE `users` SET `login`=1,`firstname`='$nickname' WHERE `userid`='$id'";
    $DBClass->connectDB($updater);