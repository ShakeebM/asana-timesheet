<?php
/**
 * Created by PhpStorm.
 * User: netstager
 * Date: 26/09/14
 * Time: 3:23 PM
 */
require('Controller.php');
class UserController extends Controller
{


    public function getUserData($userid)
    {
        //echo $userid;
        $query = "Select * From `users` WHERE `userid`='$userid'";
        $selector = $this->connectDB($query);
        $selectArray = mysql_fetch_array($selector);
        return json_encode($selectArray);
    }

    public function getTasks($userid)
    {

        $taskarray = array();

        $query = "SELECT * FROM `tasksync_table` WHERE `userId`='$userid'";
        $result = $this->connectDB($query);
        while ($row = mysql_fetch_array($result)) {
            $taskid = $row['taskid'];
            $timequery = "SELECT * FROM `taskstamp` WHERE `taskid`='$taskid'";
            $timeresult = $this->connectDB($timequery);
            $timearray = mysql_fetch_array($timeresult);
            $projectid = $row['projectid'];
            if($projectid){
                $project = $this->fetchProjectInfo($projectid);
                $projectname=$project['projectname'];
            }
            else{
                $projectname= 'nil';
            }
            $taskarray[] = array('id' => $taskid, 'name' => $row['name'], 'due_date' => $row['due_date'], 'total_time' => $timearray['totaltime'], 'timestamp' => $timearray['timestamp'], 'project' => array('id'=>$projectid,'name'=>$projectname),'status'=>$row['status']);
        }
        return json_encode($taskarray);

    }

    public function syncTasks($userid)
    {
        $asana = new Asana($this->apiKey);
        $workspaces = json_decode($asana->getWorkspaces())->data;
        foreach($workspaces as $workspace) {
            $results = $asana->getWorkspaceTasks($workspace->id, $userid, array());
            $array = json_decode($results)->data;
//        $queryproject="SELECT * FROM `projects`";
//        $projectselector=mysql_fetch_array($this->connectDB($queryproject));

            if ($array)
                foreach ($array as $singletasks) {
                    $taskdetails = $asana->getTask($singletasks->id, array());
                    $arr = json_decode($taskdetails);
                    $taskid = $arr->data->id;
                    $duedate = $arr->data->due_on;
                    $status = $arr->data->completed;
                    $taskname = $arr->data->name;
                    $projectid= $arr->data->projects[0]->id;
                    $selector = "SELECT COUNT(*) FROM `tasksync_table` WHERE `taskid`='$taskid'";
                    $selectvar = $this->connectDB($selector);
                    $row = mysql_fetch_array($selectvar);
                    if ($row['COUNT(*)'] == 0) {
                        $query = "INSERT INTO `tasksync_table` (`taskid`,`userid`,`name`,`due_date`,`status`) values ('$taskid','$userid','$taskname','$duedate','$status')";
                        $this->connectDB($query);
                        echo('inserted');
                    } else {

                        $query = "UPDATE `tasksync_table` SET `status`='$status',`userid`='$userid',`name`='$taskname',`projectid`='$projectid' WHERE `taskid`='$taskid'";
                        $this->connectDB($query);
                        echo('updated');
                    }
                }
        }
    }

    public function settimeStamp($taskid, $timestamp)
    {
        $selector = "SELECT COUNT(*) FROM `taskstamp` WHERE `taskid`='$taskid'";
        $result = $this->connectDB($selector);
        $row = mysql_fetch_array($result);
        if ($row['COUNT(*)'] == 0) {
            $query = "INSERT INTO `taskstamp` values ('$taskid',0,'$timestamp')";
            $this->connectDB($query);
        } else {
            $query = "UPDATE `taskstamp` SET `timestamp`='$timestamp' where `taskid`='$taskid'";
            $this->connectDB($query);
        }
        echo ':)';
    }

    public function addTimeTask($taskid, $timestamp)
    {
        date_default_timezone_set("Asia/Kolkata");
        //update tasktamp table
        $selector = "SELECT * FROM `taskstamp` WHERE `taskid`='$taskid'";
        $result = $this->connectDB($selector);
        $row = mysql_fetch_array($result);
        $workedtime = $timestamp - $row['timestamp'];
        $totaltime = $row['totaltime'] + $workedtime;
        $query = "UPDATE `taskstamp` SET `totaltime`='$totaltime',`timestamp`=0  where `taskid`='$taskid'";
        $this->connectDB($query);

        //insert or update timestatus table
        $today = date("Y-m-d");
        $selector1 = "SELECT COUNT(*) FROM `timestatus` WHERE `task_id`='$taskid' AND `work_date`='$today'";
        $result1 = $this->connectDB($selector1);
        $row = mysql_fetch_array($result1);
        if ($row['COUNT(*)'] == 0) {
            $query1 = "INSERT INTO `timestatus` VALUES ('$taskid','$today','$workedtime')";
            $this->connectDB($query1);
        } else {
            $query1 = "UPDATE `timestatus` SET `time_work`=`time_work`+'$workedtime' WHERE `task_id`='$taskid' AND `work_date`='$today'";
            $this->connectDB($query1);
        }
    }

    public function searchwithdate($userid, $datechosenfrom, $datechosento)
    {

        $selector = "SELECT * FROM `tasksync_table` WHERE `userid`='$userid'";
        $result = $this->connectDB($selector);
        $taskarray = array();
        while ($row = mysql_fetch_array($result)) {
            $taskid = $row['taskid'];
            if ($datechosento == 0) {
                $timequery = "SELECT * FROM `timestatus` WHERE `task_id`='$taskid' and `work_date`='$datechosenfrom'";
            } else {
                $timequery = "SELECT * FROM `timestatus` WHERE `task_id`='$taskid' AND `work_date` BETWEEN '$datechosenfrom' AND '$datechosento'";
            }
            $timeresult = $this->connectDB($timequery);
            while ($timearray = mysql_fetch_array($timeresult)) {
                if ($timearray['time_work']) {
                    $projectid = $row['projectid'];
                    if($projectid){
                        $project = $this->fetchProjectInfo($projectid);
                        $projectname=$project['projectname'];
                    }
                    else{
                        $projectname= 'nil';
                    }
                    $taskarray[] = array('id' => $taskid, 'date_worked' => $timearray['work_date'], 'name' => $row['name'], 'totaltime' => $timearray['time_work'], 'project'=>array('id'=>$projectid,'name' => $projectname));
                }
            }

        }
        $tasksortedarray=$this->sortArray($taskarray);
        return json_encode($tasksortedarray);
    }

    public function sortArray($people){

        $sortArray = array();

            foreach($people as $person){
                foreach($person as $key=>$value){
                    if(!isset($sortArray[$key])){
                        $sortArray[$key] = array();
                    }
                    $sortArray[$key][] = $value;
            }
}

$orderby = "date_worked"; //change this to whatever key you want from the array

array_multisort($sortArray[$orderby],SORT_DESC,$people);

return $people;

}



    public function getOldTimeInfo($userid)
    {

        $selector = "SELECT * FROM `tasksync_table` WHERE `userid`='$userid' and `status`=1";
        $result = $this->connectDB($selector);
        $taskarray = array();
        while ($row = mysql_fetch_array($result)) {
            //time info fetch
            $taskid = $row['taskid'];
            $timequery = "SELECT * FROM `taskstamp` WHERE `taskid`='$taskid'";
            $timeresult = $this->connectDB($timequery);
            $timearray = mysql_fetch_array($timeresult);

            //project info fetch
            $projectid = $row['projectid'];
            $projectname = $this->fetchProjectInfo($projectid);

            $taskarray[] = array('id' => $taskid, 'name' => $row['name'], 'totaltime' => $timearray['totaltime'], 'projectname' => $projectname['projectname']);

        }
        return json_encode($taskarray);
    }

    public function fetchProjectInfo($projectid)
    {
        $projectquery = "SELECT * FROM `projects` WHERE `projectid`='$projectid'";
        $projectresult = $this->connectDB($projectquery);
        $projectname = mysql_fetch_array($projectresult);
        return $projectname;
    }


    public function getTestData()
    {
        $asana = new Asana($this->apiKey);
        $data = $asana->getTask(15844819219952);
        $assigneeid = json_decode($data)->data->assignee->id;
        echo json_encode($assigneeid);
        //var_dump(json_encode(json_decode($data)->data->due_on));
    }

    public function findHistorictasks($userid)
    {
        $selector = "SELECT * FROM `tasksync_table` WHERE `userid`='$userid'";
        $result = $this->connectDB($selector);
        $taskarray = array();
        while ($row = mysql_fetch_array($result)) {
            $taskid = $row['taskid'];
            $timequery = "SELECT * FROM `timestatus` WHERE `task_id`='$taskid'";
            $timeresult = $this->connectDB($timequery);
            while ($timearray = mysql_fetch_array($timeresult)) {
                if ($timearray['time_work']) {
                    //project info fetch
                    $projectid = $row['projectid'];
                    if($projectid){
                        $project = $this->fetchProjectInfo($projectid);
                        $projectname=$project['projectname'];
                    }
                    else{
                        $projectname= 'nil';
                    }
                    $taskarray[] = array('id' => $taskid, 'date_worked' => $timearray['work_date'], 'name' => $row['name'], 'totaltime' => $timearray['time_work'], 'project'=>array('id'=>$projectid,'name' => $projectname));
                }

            }
        }
        $tasksortedarray=$this->sortArray($taskarray);
        return json_encode($tasksortedarray);
    }

    public function addNewTask($userid, $taskdata)
    {
        $taskString= json_decode($taskdata);
//        var_dump($taskString->taskName);
        if($taskString->projectName=="nil"){
            $taskString->projectName="";
        }
        $taskid=$this->getTaskid();
        $selector= "INSERT INTO `tasksync_table` VALUES ('$taskid','$userid','$taskString->taskName','$taskString->due_date','$taskString->projectName','0')";
        $result=$this->connectDB($selector);
        echo $result;
    }

    private function getTaskid(){
        $taskid=rand(1000000,10000000);

        $checker = "SELECT `taskid` FROM `tasksync_table` WHERE `taskid`='$taskid'";
        $result = $this->connectDB($checker);

        if (mysql_num_rows($result)>0)
        {
            $this->getTaskid();
        }

        else
        {
            return $taskid;
        }

}

}