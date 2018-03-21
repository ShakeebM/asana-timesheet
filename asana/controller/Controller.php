<?php
/**
 * Created by PhpStorm.
 * User: netstager
 * Date: 25/09/14
 * Time: 12:21 PM
 */
require("../apicore/asana.php");

class Controller {

    private $dbname='NETTIMWEDG';
//    private $dbname='asanadb';
    private $username= 'NETTIMWEDG';
    private $password= 'NETTIMWEDG';

    //protected $apiKey =  '2UfTGjHb.N0WqodwLFuKeO3jlhMe5WFW';
    protected $apiKey = '9VIgxOkW.y5fq9sscz0zsUXfILDVCGpd';
    private $hostname =  'localhost';
//    private $dbname   =  'PRJCTRC';
//    private $username =  'PRJCTRC';
//    private $password =  'PRJCTRC';


public function getHosts(){
    $whitelist = array('127.0.0.1', "::1");

    if(!in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
        $this->hostname   = 'localhost';
        $this->dbname     = 'PRJCTRC';
        $this->dbname     = 'PRJCTRC';
        $this->username   = 'PRJCTRC';
        $this->password   = 'PRJCTRC';

    }
}

    public function connectDB($query)
    {
        $db = mysql_connect($this->hostname, $this->username,$this->password);
//        $db = mysql_connect('localhost:3307', 'root');
        if($db){
            mysql_select_db($this->dbname,$db);
            $selector=mysql_query($query,$db);
            return $selector;
        }
        mysql_close($db);
    }

    protected function objectToArray( $object )
    {
        if( !is_object( $object ) && !is_array( $object ) )
        {
            return $object;
        }
        if( is_object( $object ) )
        {
            $object = get_object_vars($object);
        }
        return array_map( 'objectToArray', $object );
    }
}