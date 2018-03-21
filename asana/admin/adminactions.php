<?php
/**
 * Created by PhpStorm.
 * User: netstager
 * Date: 18/09/14
 * Time: 5:41 PM
 */
require('../controller/AdminController.php');

$action =$_GET['action'];
$admin=new AdminController();

switch ($action) {
    case 'getall':
        $response=$admin->getAllUSers();
        echo $response;
        break;
    case 'getlogin':
        $response=$admin->getLoginUsers();
        echo $response;
        break;
    case 'getProjects':
        $projectdata=$admin->getAllProjects();
        echo $projectdata;
        break;
    case 'getprojecttasks':
        $projectid=$_GET['projectid'];
        $projecttasks=$admin->getProjectTasks($projectid);
        echo $projecttasks;
        break;
    case 'getclients':
        $clients=$admin->getAllClients();
        echo $clients;
        break;
    case 'getworkspacetasks':
        $taskdata=$admin->getWorkSpaceTasks();
        echo $taskdata;
        break;
    case 'projecttiming':
        $proid=$_GET['projectid'];
        $tasktimedata=$admin->getTaskTime($proid);
        echo json_encode($tasktimedata);
        break;
    case 'findclientproject':
        $clientid=$_GET['clientid'];
        $clientprojectdata=$admin->getClientProjectData($clientid);
        echo $clientprojectdata;
        break;
    case 'updateproject':
        $clientid=$_GET['clientid'];
        $projectid=$_GET['projectid'];
        $admin->updateProjectWithClient($clientid,$projectid);
        break;
    case 'addclient':
        $clientname=$_GET['clientname'];
        $admin->addclient($clientname);
        break;
    case 'deleteClient':
        $clientid=$_GET['clientid'];
        $admin->deleteClient($clientid);
        break;
    case 'updateclient':
        $clientid=$_GET['clientid'];
        $clientname=$_GET['clientname'];
        $admin->updateclientName($clientname,$clientid);
        break;

    case 'syncallusers':
        $admin->syncUsers();
        break;
    case 'syncprojects':
        $admin->syncProjects();
        break;
    case 'syncworkspacetasks':
        $admin->synWorkSpaceTasks();
        break;
    case 'syncprojecttasks':
        $myprojectid=$_GET['projectid'];
        $admin->syncProjectTasks($myprojectid);
        break;
    case 'syncworkspace':
        $arr=$admin->syncWorkSpaces();
        echo $arr;
        break;

    default:

        break;

        }
