<?php
header("Access-Control-Allow-Origin: *");
error_reporting(E_ERROR | E_PARSE);
$cacheDir="apicache";
$cacheTime=5*60;
$enableCache=  false;  //!$_POST["start"] && !$_POST["end"] ;          //true;

require("lib.php");
require("dlib.php");
$ap=str_replace("/lens/app/api/","",$_SERVER['REQUEST_URI']);
cachedReplier($enableCache,$cacheDir,$cacheTime,$ap);
$al=explode("/",$ap);
$pdo=pdomake();
switch($al[0]){
    case "view":
        switch($al[1]){
        case "all";
            $r=new Reply($enableCache,$cacheDir,$cacheTime,$ap);
            $o=listAll($pdo);
            for($i=0;$i<count(array_keys($o));$i++){
                AALRect($o[array_keys($o)[$i]]);
            }
            //sort all by date 
            if($al[2]=="chronology"){
                $el=adjustFlow($o);
                $r->send($el);
            }
            if($al[2]=="rss"){
                $el=adjustFlow($o);
                include("rssBuilder.php");
                for($i=0;$i<count($el);$i++){
             if(!empty($el[$i]["questionTitle"])){
             	$el[$i]["title"]=$el[$i]["questionTitle"];
             	$el[$i]["shortDescription"]=$el[$i]["questionTitle"]."\n----\n".$el[$i]["questionText"]."\n````````\n".$el[$i]["answerTitle"]."\n----\n".$el[$i]["answerText"];
             }
                }
                $data=rss($el);
                ob_clean();
                header("Content-Type: application/xml");
                echo $data;
                exit;
            } 
            else $r->send($o);
        break;
        case "transactions":
            switch($al[2]){
                case "all":
                    $r=new Reply($enableCache,$cacheDir,$cacheTime,$ap);
                    $data=listCont($pdo);
                    conditionalDatePosting($data,$_POST,"publishedDate");
                    AALRect($data);
                    $r->send($data);
                break;
                default:
                    $r=new Reply($enableCache,$cacheDir,$cacheTime,$ap);
                    if($al[2]==null) $r->die("MISSING_PARAMETER","Missing parameter \"ID\"",400);
                    $data=listCont($pdo,$al[2]);
                    if(!count($data)) $r->die("RESOURCE_NOT_FOUND","The specified resource was not found",404);
                    AALRect($data);
                    $r->send($data[0]);
                break;
            }
            break;
            case "documents":
                switch($al[2]){
                    case "all":
                        $r=new Reply($enableCache,$cacheDir,$cacheTime,$ap);
                        $data=listDocs($pdo);
                        conditionalDatePosting($data,$_POST,"publishedDate");
                        AALRect($data);
                        $r->send($data);
                    break;
                    default:
                        $r=new Reply($enableCache,$cacheDir,$cacheTime,$ap);
                        if($al[2]==null) $r->die("MISSING_PARAMETER","Missing parameter \"ID\"",400);
                        $data=listDocs($pdo,$al[2]);
                        if(!count($data)) $r->die("RESOURCE_NOT_FOUND","The specified resource was not found",404);
                        AALRect($data);
                        $r->send($data[0]);
                    break;
                }
            break;
            case "events":
                switch($al[2]){
                    case "all":
                        $r=new Reply($enableCache,$cacheDir,$cacheTime,$ap);
                        $data=listEvents($pdo);
                        conditionalDatePosting($data,$_POST,"publishedDate");
                        AALRect($data);
                        $r->send($data);
                    break;
                    default:
                        $r=new Reply($enableCache,$cacheDir,$cacheTime,$ap);
                        if($al[2]==null) $r->die("MISSING_PARAMETER","Missing parameter \"ID\"",400);
                        $data=listEvents($pdo,$al[2]);
                        if(!count($data)) $r->die("RESOURCE_NOT_FOUND","The specified resource was not found",404);
                        AALRect($data);
                        $r->send($data[0]);
                    break;
                }
            break;
            case "questions":
                switch($al[2]){
                    case "all":
                        $r=new Reply($enableCache,$cacheDir,$cacheTime,$ap);
                        $data=listDomande($pdo);
                        conditionalDatePosting($data,$_POST,"publishedDate");
                        AALRect($data);
                        $r->send($data);
                    break;
                    default:
                        $r=new Reply($enableCache,$cacheDir,$cacheTime,$ap);
                        if($al[2]==null) $r->die("MISSING_PARAMETER","Missing parameter \"ID\"",400);
                        $data=listDomande($pdo,$al[2]);
                        if(!count($data)) $r->die("RESOURCE_NOT_FOUND","The specified resource was not found",404);
                        AALRect($data);
                        $r->send($data[0]);
                    break;
                }
            break;
            case "feedbacks":
                switch($al[2]){
                    case "all":
                        $r=new Reply($enableCache,$cacheDir,$cacheTime,$ap);
                        $data=listFeedbacks($pdo);
                        conditionalDatePosting($data,$_POST,"publishedDate");
                        AALRect($data);
                        $r->send($data);
                    break;
                    default:
                        $r=new Reply($enableCache,$cacheDir,$cacheTime,$ap);
                        if($al[2]==null) $r->die("MISSING_PARAMETER","Missing parameter \"ID\"",400);
                        $data=listFeedbacks($pdo,$al[2]);
                        if(!count($data)) $r->die("RESOURCE_NOT_FOUND","The specified resource was not found",404);
                        AALRect($data);
                        $r->send($data[0]);
                    break;
                }
            break;
            case "UID":
            if($al[3]=="render"){
                echo pageMake($al[2],$pdo);
                exit;
            }
            $r=new Reply($enableCache,$cacheDir,$cacheTime,$ap);
            if($al[2]==null) $r->die("MISSING_PARAMETER","Missing parameter \"UID\"",400);
            $data=uid($al[2],$pdo);
            if($data===(-1)) $r->die("INVALID_UID_FORMAT","The specified UID is invalid",404);
            if(!count($data)) $r->die("RESOURCE_NOT_FOUND","The specified resource was not found",404);
            $r->send($data[0]);
            break;
            case "tags":
                switch($al[2]){
                    case "all":
                        switch($al[3]){
                            case "detailed":
                                $r=new Reply($enableCache,$cacheDir,$cacheTime,$ap);
                                $data=listTagsDetail($pdo);
                                $r->send($data);
                            break;
                            case "summary":
                                $r=new Reply($enableCache,$cacheDir,$cacheTime,$ap);
                                $data=listTags($pdo);
                                $r->send($data);
                            break;
                        }
                        
                    break;
                    default:
                        $r=new Reply($enableCache,$cacheDir,$cacheTime,$ap);
                        if($al[2]==null) $r->die("MISSING_PARAMETER","Missing parameter \"ID\"",400);
                        switch($al[3]){
                            case "detailed":
                                $r=new Reply($enableCache,$cacheDir,$cacheTime,$ap);
                                $data=listTagsDetail($pdo)[$al[2]];
                                if($data==null) $r->die("RESOURCE_NOT_FOUND","The specified resource was not found",404);
                                if($al[4]=="render"){
                                    echo renderTag($data,$al[2]);
                                    exit;
                                }
                                
                                $r->send($data);
                            break;
                            case "summary":
                                $r=new Reply($enableCache,$cacheDir,$cacheTime,$ap);
                                $data=listTags($pdo)[$al[2]];
                                if($data==null) $r->die("RESOURCE_NOT_FOUND","The specified resource was not found",404);
                                $r->send($data);
                            break;
                            default:
                            break;
                        }
                        if(!$data) $r->die("RESOURCE_NOT_FOUND","The specified resource was not found",404);
                        $r->send($data);
                    break;
                }
            break;
        }
    break; 
    
}
$r=new Reply($enableCache,$cacheDir,$cacheTime,$ap);
$r->die("UNKNOWN_ENDPOINT","The endpoint specified is not valid",400);