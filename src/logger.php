<?php

namespace walklogger\logger;

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class logger
{

    public function logger(String $sName)
    {
        return 'Hi ' . $sName . '! How are you doing today?';
    }  
  
function setExecutiveEndPoint($req,$res)
{
    $out = new \Symfony\Component\Console\Output\ConsoleOutput();

    //Current route url
    $currentPath= Route::getFacadeRoot()->current()->uri();

    //variables
    $endpoint="";

    //JSON encoding decoding
    $jsonString = file_get_contents('../config.json');
    $data = json_decode($jsonString, true);
    $flow=json_encode($data['flow']);
    $projectId=$data['projectId'];
    $include=$data['include'];
    $array = (json_decode($flow, true));

    $req->headers->set('Accept', 'application/json');

    // Loop through the associative array
    foreach($array as $key=>$value)
    {
        if($key==$currentPath){
            $endpoint=$value ;
        }
    }

    if($data['authkey'] && $data['projectId']){
        
        $req->authkey=$data['authkey'];

        $reqbody=(array(
            "url"=> $req->fullUrl(),
            "method"=>$req->method(),
            "routePath"=>$currentPath,
            "ip"=>$req->ip(),
            "header"=>$req->headers->all(),
            "reqBody"=>json_decode($req->getContent(), true),
            "authKey"=>$req->authkey
        ));

        $resbody=(array(
            "StatusCode"=> $res->getStatusCode(),
            "method"=>$req->method(),
            "routePath"=>$currentPath,
            "header"=>$res->headers->all(),
            "resBody"=>json_decode($res->getContent(), true)
        ));

        $reqres=(array(
            "REQUEST_BODY"=>$reqbody,
            "RESPONSE_BODY"=>$resbody,
        ));

        $current_date_time = \Carbon\Carbon::now()->toDateTimeString();
        if(in_array("*", $include))
        {
            $data=array(
                "data"=>$reqres,
                "time"=>$current_date_time
                );

            $path="https://sokt.io/".$projectId."/".$endpoint;
            $response = Http::post($path,$data);    

        }else if(in_array($currentPath, $include)){
            $data=array(
                "data"=>$reqres,
                "time"=>$current_date_time
                );
    
                $path="https://sokt.io/".$projectId."/".$endpoint;
                $response = Http::post($path,$data);
        }
    }
    else{
        echo "please provide a valid projectId && authKey";
    }
}

function info($dumpPoint,$message){

    $jsonString = file_get_contents('../config.json');
    $configfile = json_decode($jsonString, true);

    $level=$configfile['level'];
    $projectId=$configfile['projectId'];
    $current_date_time = \Carbon\Carbon::now()->toDateTimeString();

    if ($level === "INFO") {
        $data=array(
            "level"=>"INFO",
            "time"=> $current_date_time,
            "message"=>$message
        );
        $path="https://sokt.io/".$projectId."/".$dumpPoint;
        $response = Http::post($path,$data);    
    }
}

function warning($dumpPoint,$message){

    $jsonString = file_get_contents('../config.json');
    $configfile = json_decode($jsonString, true);

    $level=$configfile['level'];
    $projectId=$configfile['projectId'];
    $current_date_time = \Carbon\Carbon::now()->toDateTimeString();

    if ($level == "INFO" || $level=="WARNING") {
        $data=array(
            "level"=>"WARNING",
            "time"=> $current_date_time,
            "message"=>$message
        );
        $path="https://sokt.io/".$projectId."/".$dumpPoint;
        $response = Http::post($path,$data);    
    }
}

function error($dumpPoint,$message){

    $jsonString = file_get_contents('../config.json');
    $configfile = json_decode($jsonString, true);

    $level=$configfile['level'];
    $projectId=$configfile['projectId'];
    $current_date_time = \Carbon\Carbon::now()->toDateTimeString();

        $data=array(
            "level"=>"ERROR",
            "time"=> $current_date_time,
            "message"=>$message
        );
        $path="https://sokt.io/".$projectId."/".$dumpPoint;
        $response = Http::post($path,$data);    
    
}
}

