<?php
/**
 * Created by PhpStorm.
 * User: netstager
 * Date: 16/10/14
 * Time: 5:33 PM
 */

require('../controller/Controller.php');
$DBClass= new Controller();
$data = json_decode( file_get_contents('php://input') );
$id=$data->userid;
$username=$data->email;
$password=md5($data->password);
$existingpassword=md5($data->existingpassword);

$nickname=$data->firstname;

$loginCheckQuery="SELECT `password` FROM `login` WHERE `user_id`='$id'";
$counter=$DBClass->connectDB($loginCheckQuery);
$passwordselector= mysql_fetch_object($counter);
$passwordatdb = $passwordselector->password;
if($passwordatdb==$existingpassword){
    $updater="UPDATE `users` SET `firstname`='$nickname' WHERE `userid`='$id'";
    $DBClass->connectDB($updater);
    $query="UPDATE `login` SET `password`='$password' WHERE `user_id`='$id'";
    $DBClass->connectDB($query);
    $response=json_encode(array('message'=>'Successfully updated!!','status'=>1));
}
else{
    $response=json_encode(array('message'=>'Error!! password did not match!!','status'=>0));
}
echo $response;

//$query="INSERT INTO `login` VALUES ('$id','$username','$password')";
//$DBClass->connectDB($query);
//$updater="UPDATE `users` SET `login`=1,`firstname`='$nickname' WHERE `userid`='$id'";
//$DBClass->connectDB($updater);