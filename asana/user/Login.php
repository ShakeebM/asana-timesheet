<?php
/**
 * Created by PhpStorm.
 * User: netstager
 * Date: 03/09/14
 * Time: 12:45 PM
 */

require('../controller/Controller.php');
$DBClass= new Controller();
$data = json_decode( file_get_contents('php://input') );
$username=$data->username;
$password=md5($data->password);

    $query="SELECT `user_id` FROM `login` WHERE `username`='$username' and `password`='$password'";
    $result= $DBClass->connectDB($query);
    $row = mysql_fetch_array($result);
    echo $row['user_id'];
