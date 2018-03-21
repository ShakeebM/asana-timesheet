<?php
/**
 * Created by PhpStorm.
 * User: netstager
 * Date: 25/09/14
 * Time: 11:57 AM
 */
require('Controller.php');
class AdminController extends Controller{

    //sync info

    public function syncUsers(){
        $asana=new Asana($this->apiKey);
        $userlist=$asana->getUsers();
        $userarray=json_decode($userlist);
            foreach($userarray->data as $singleuser){
                $userdetailed=$asana->getUserInfo($singleuser->id);
                $user=json_decode($userdetailed)->data;
                $photopath=$user->photo->image_36x36.'';
                $query="INSERT INTO `users` VALUES ('$user->id','$user->name','$user->email','$photopath',0)";
                $this->connectDB($query);
            }

    }

    public function syncWorkSpaces(){
        $asana=new Asana($this->apiKey);
        $row=$asana->getWorkspaces();
        $workspaces=json_decode($row)->data;
        foreach($workspaces as $workspace){
            $query="INSERT INTO `workspaces` VALUES ('$workspace->id','$workspace->name')";
            $this->connectDB($query);

        }
        return ':)';

    }
    public function syncProjects(){
        $asana=new Asana($this->apiKey);
        $selectquery="SELECT * FROM `workspaces`";
        $row=$this->connectDB($selectquery);
        while($selectquery=mysql_fetch_array($row)) {
            $workspaceid = $selectquery['workspaceid'];
            $rowprojects = $asana->getProjectsInWorkspace($workspaceid);
            $projects = json_decode($rowprojects)->data;
            if($projects) {
                foreach ($projects as $project) {
                  //  var_dump($project);
                    $query = "INSERT INTO `projects` VALUES ('$project->id','$workspaceid','0','$project->name')";
                    $this->connectDB($query);
                  //  $rowtasklist = $asana->getProjectTasks($project->id);
                   // $tasklist = json_decode($rowtasklist)->data;
//                    if($tasklist) {
//                        $this->syncProjecttasks($tasklist, $project->id);
//                    }
                }
            }
        }
    }
    public function syncProjectTasks($projectid)
    {
        $asana = new Asana($this->apiKey);
//        $selectquery = "SELECT * FROM `projects`";
//        $row = $this->connectDB($selectquery);
//        while ($selectquery = mysql_fetch_array($row)) {
//            $projectid = $selectquery['projectid'];
            $rowtasks = $asana->getProjectTasks(json_decode($projectid));
            $tasklist = json_decode($rowtasks)->data;
            if($tasklist) {
                foreach ($tasklist as $task) {
                    // var_dump($task);
                    $rowtaskdetails = $asana->getTask($task->id);
                    $taskdetails = json_decode($rowtaskdetails)->data;
                    if($taskdetails) {
                        $query = "INSERT INTO `tasksync_table` VALUES('$taskdetails->id', '0','$taskdetails->name', '$taskdetails->due_on','$projectid','$taskdetails->completed')";
                        $this->connectDB($query);
                    }

                }
        }
    }

    public function synWorkSpaceTasks(){
        $asana=new Asana($this->apiKey);
        $selectquery="SELECT * FROM `workspaces`";
        $row=$this->connectDB($selectquery);
        while($selectquery=mysql_fetch_array($row)) {
            $workspaceid = $selectquery['workspaceid'];
            $rowtasks = $asana->getWorkspaceTasks($workspaceid);
            $tasklist = json_decode($rowtasks)->data;
            if($tasklist) {
                foreach ($tasklist as $task) {
                   // var_dump($task);
                    $rowtaskdetails = $asana->getTask($task->id);
                    $taskdetails = json_decode($rowtaskdetails)->data;
                    if($taskdetails) {
                        $query = "INSERT INTO `tasksync_table` VALUES('$taskdetails->id', '0','$taskdetails->name', '$taskdetails->due_on','0','$taskdetails->completed')";
                        $this->connectDB($query);
                    }
                }
            }
//            $selector="SELECT COUNT(*) FROM `tasksync_table` WHERE `taskid`='$task->id'";
//            $selectvar=$this->connectDB($selector);
//            $row=mysql_fetch_array($selectvar);
//            if($row['COUNT(*)']==0){
//                $assigneeid=json_decode($task)->assignee->id;
//                echo json_encode($assigneeid);
//                $query="INSERT INTO `tasksync_table` values ('$task->id','$assigneeid','$task->name','$task->due_on','$projectid','$task->completed')";
//                $this->connectDB($query);
//                echo('inserted');
//            }
//            else{
//
//                $query="UPDATE `tasksync_table` SET `status`='$task->completed',`name`='$task->name',`projectid`='$projectid' where `taskid`='$task->id'";
//                $this->connectDB($query);
//                echo('updated');
//            }
        }
    }
    //get all

    public function getAllUsers(){
        $query="SELECT * FROM `users`";
        $rawData=$this->connectDB($query);
        $array=array();
        while($result=mysql_fetch_array($rawData)){
            $array[]= array('userid'=>$result['userid'],'firstname'=>$result['firstname'],'photopath'=>$result['photopath'],'email'=>$result ['email'],'login'=>$result['login']);
        }
        return json_encode($array);
    }

    public function getLoginUsers()
    {
        $query="SELECT * FROM `login`";
        $rawData=$this->connectDB($query);
        $array=array();
        while($result=mysql_fetch_array($rawData)){
            $userid=$result['user_id'];
            $selector="SELECT `firstname` FROM `users` WHERE `userid`='$userid'";
            $firstname=$this->connectDB($selector);
            if(mysql_num_rows($firstname)>0){
                $firstnameRow=mysql_fetch_array($firstname);
                $array[]= array('userid'=>$userid,'firstname'=>$firstnameRow['firstname']);
            }

        }
        return json_encode($array);
    }

    public function getAllProjects()
    {
        $query="SELECT * FROM `projects`";
        $projectselector=$this->connectDB($query);
        $projectlist= array();
        while($row=mysql_fetch_array($projectselector)){
            $clientid=$row['clientid'];
            $workspaceid=$row['workspaceid'];
            $clientname='nil';
            if($clientid!=0){
                $client = $this->fetchClientinfo($clientid);
                $clientname=$client['clientname'];
            }
            $workspacename=$this->fetchWorkspaceInfo($workspaceid);
            $projectlist[]=array('client'=>array('id'=>$clientid,'name'=>$clientname),'workspacename'=>$workspacename['name'],'id'=>$row['projectid'],'name'=>$row['projectname']);
        }
        return json_encode($projectlist);

//        $projects=$asana->getProjectsInWorkspace();
    }

    public function getAllClients()
    {
        $query="SELECT * FROM `client`";
        $clientdata=$this->connectDB($query);

        $clientlist= array();
        while($row=mysql_fetch_array($clientdata)){
            $clientlist[]= array('clientid'=>$row['clientid'] ,'clientname'=>$row['clientname']);
        }
        return json_encode($clientlist);
    }

    //fetch details

    public function fetchClientinfo($clientid){
        $query="SELECT * FROM `client` WHERE `clientid`='$clientid'";
        $result=$this->connectDB($query);
        $clientname=mysql_fetch_array($result);
        return $clientname;
    }

    public function fetchWorkspaceInfo($workspaceid){
        $query="SELECT * FROM `workspaces` WHERE `workspaceid`='$workspaceid'";
        $result=$this->connectDB($query);
        $workspace=mysql_fetch_array($result);
        return $workspace;
    }
    public function fetchUserInfo($userid){
        $query="SELECT * FROM `users` WHERE `userid`='$userid'";
        $result=$this->connectDB($query);
        $user=mysql_fetch_array($result);
        return $user;
    }
    public function fetchProjectInfo($projectid){
        $query="SELECT * FROM `projects` WHERE `projectid`='$projectid'";
        $result=$this->connectDB($query);
        $project =mysql_fetch_array($result);
        return $project;
    }

    //add update

    public function addclient($clientname){
        $query="INSERT INTO `client` (`clientname`) values ('$clientname')";
        $this->connectDB($query);

    }

    public function updateclientName($clientname, $clientid)
    {
        $query="UPDATE `client` SET `clientname`='$clientname' WHERE `clientid`='$clientid'";
        $this->connectDB($query);

    }

    public function updateProjectWithClient($clientid, $projectid)
    {
        $query="UPDATE `projects` SET `clientid`='$clientid' WHERE `projectid`='$projectid'";
        $this->connectDB($query);
    }
    public function resetProjectClients($clientid){
        $query="UPDATE `projects` SET `clientid`='0' WHERE `clientid`='$clientid'";
        $this->connectDB($query);
    }

    //select all

    public function getProjectTasks($projectid)
    {

        $query="SELECT * FROM `projects` WHERE `projectid`='$projectid'";
        $selector=$this->connectDB($query);

        $projectarray=mysql_fetch_array($selector);
        $clientid=$projectarray['clientid'];
        $workspaceid=$projectarray['workspaceid'];
        $clientname=$this->fetchClientinfo($clientid);
        $workspacename=$this->fetchWorkspaceInfo($workspaceid);
        $taskquery="SELECT * FROM `tasksync_table` WHERE `projectid`='$projectid'";
        $taskarray= $this->connectDB($taskquery);
        $taskinfo=array();
        while($row=mysql_fetch_array($taskarray)){
            $user=$this->fetchUserInfo($row['userid']);
            $taskinfo[]=array('id'=>$row['taskid'],'name'=>$row['name'],'due_date'=>$row['due_date'],'status'=>$row['status'],'assignee'=>array('id'=>$row['userid'],'name'=>$user['firstname']));
        }
        $projectlist=array('id'=>$projectid,'name'=>$projectarray['projectname'],'workspace'=>array('name'=>$workspacename['name'],'id'=>$workspaceid),'client'=>array('name'=>$clientname['clientname'],'id'=>$clientid),'tasks'=>$taskinfo);
        return json_encode($projectlist);
    }

    public function getWorkSpaceTasks()
    {
        $taskquery="SELECT * FROM `tasksync_table` WHERE `projectid`=''";
        $taskarray= $this->connectDB($taskquery);
        $taskinfo=array();
        while($row=mysql_fetch_array($taskarray)){
            $user=$this->fetchUserInfo($row['userid']);
            $taskinfo[]=array('id'=>$row['taskid'],'name'=>$row['name'],'due_date'=>$row['due_date'],'status'=>$row['status'],'assignee'=>array('id'=>$row['userid'],'name'=>$user['firstname']));
        }
        return json_encode($taskinfo);
    }

    public function getTaskTime($projectid)
    {
        $selector = "SELECT * FROM `tasksync_table` WHERE `projectid`='$projectid'";
        $result = $this->connectDB($selector);
        if ($projectid) {

            $project = $this->fetchProjectInfo($projectid);
            $projectname = $project['projectname'];
            $workspace = $this->fetchWorkspaceInfo($project['workspaceid']);
            $client= $this->fetchClientinfo($project['clientid']);
        } else {
            $projectname = 'nil';
            $workspace= array('workspaceid'=>'nil','name'=>'nil');
            $client = array('clientid'=>'nil','clientname'=>'nil');
        }
        $taskrow= array();
        while ($row = mysql_fetch_array($result)) {
            $taskid = $row['taskid'];
            $timequery = "SELECT * FROM `timestatus` WHERE `task_id`='$taskid'";
            $timeresult = $this->connectDB($timequery);
            $userid = $row['userid'];
            $username = $this->fetchUserInfo($userid);
            $totalworked=0;
            while ($timearray = mysql_fetch_array($timeresult)) {
                if ($timearray['time_work']) {
                    $totalworked+=$timearray['time_work'];
                }
            }
            $taskrow[] = array('id' => $taskid,'due_date'=>$row['due_date'],'status'=>$row['status'],'name' => $row['name'], 'totaltime' => $totalworked,'assigned_user'=>array('id'=>$userid,'name'=>$username));
        }
        $taskarray = array('tasks' => $taskrow,'project'=>array('id'=>$projectid,'name' => $projectname),'workspace'=>$workspace,'client'=>$client);
        return $taskarray;
    }

    public function getClientProjectData($clientid)
    {
        $selector= "SELECT * FROM `projects` WHERE `clientid`='$clientid'";
        $result= $this->connectDB($selector);
        $projectArray=array();
        while($row=mysql_fetch_array($result)){
            $projecttasks=$this->getTaskTime($row['projectid']);
            $totaltime=0;
            $persons=array();
            foreach($projecttasks['tasks'] as $task){
                $totaltime+=$task['totaltime'];
                if($task['assigned_user']['name']['userid']){
                    $persons[$task['assigned_user']['name']['userid']] = array('id' => $task['assigned_user']['name']['userid'], 'name' => $task['assigned_user']['name']['firstname']);
                }
            }
            $projectArray[]= array('projectid'=>$row['projectid'],'projectname'=>$row['projectname'],'totaltime'=>$totaltime,'persons'=>$persons,'tasks'=>$projecttasks,'number_of_tasks'=>count($projecttasks['tasks']));
        }
        $clientdata=$this->fetchClientinfo($clientid);
        $clientdetails=array('id'=>$clientid,'name'=>$clientdata['clientname'],'projects'=>$projectArray);
        return json_encode($clientdetails);
    }

    public function deleteClient($clientid)
    {
        $deleter="DELETE FROM `client` WHERE `clientid`='$clientid'";
        $this->connectDB($deleter);
        $this->resetProjectClients($clientid);
        echo 'success';
    }


}
