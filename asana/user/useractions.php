<?php
/**
 * Created by PhpStorm.
 * User: netstager
 * Date: 29/09/14
 * Time: 12:03 PM
 */

require('../controller/UserController.php');

$action =$_GET['action'];
$userControll=new UserController();

switch ($action) {
    case 'getuserdata':
        $userid=json_decode($_GET['uid']);
        $response=$userControll->getUserData($userid);
        echo($response);
        break;
    case 'gettaskdata':
        $userid=json_decode($_GET['userid']);
        $tasks=$userControll->getTasks($userid);
        echo $tasks;
        break;
    case 'synctasks':
        $userid=json_decode($_GET['userid']);
        $userControll->syncTasks($userid);
        break;
    case 'settimestamp':
        $taskid = $_GET['taskid'];
        $timestamp=$_GET['time'];
        $userControll->settimeStamp($taskid,$timestamp);
        break;
    case 'addtimetask':
        $taskid = $_GET['taskid'];
        $timestamp=$_GET['time'];
        $userControll->addTimeTask($taskid,$timestamp);
        break;
    case 'searchviadate':
        $userid= json_decode($_GET['userid']);
        $datechosenfrom= $_GET['datechoosefrom'];
        $datechosento=$_GET['datechooseto'];
        $tasks=$userControll->searchwithdate($userid,$datechosenfrom,$datechosento);
        echo $tasks;
        break;
    case 'findhistoricdata':
        $userid= json_decode($_GET['userid']);
        $tasklist=$userControll->findHistorictasks($userid);
        echo $tasklist;
        break;
    case 'gettimeinfo':
        $userid= json_decode($_GET['userid']);
        $timeold=$userControll->getOldTimeInfo($userid);
        echo $timeold;
        break;
    case 'addnewTask':
        $userid= json_decode($_GET['userid']);
        $taskdata= $_GET['taskInput'];
        $userControll->addNewTask($userid,$taskdata);


        break;
    case 'test' :

        $userControll->getTestData();
        break;
    default:

        break;
}
