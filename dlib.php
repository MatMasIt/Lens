<?php

function delete(&$array,$index){
    unset($array[$index]); // remove item at index 0
    $array= array_values($array); // 'reindex' array

}

function conditionalDatePosting(&$array,$post,$dateKey){
    $start=$post["start"];
    $end=$post["end"];
    if($start!="false" || $end!="false"){
        $start=$start?strtotime($start):0;
        $end=$end?strtotime($end):100000000000;
        $array=dateRangeFilter($array,$dateKey,$start,$end);
        /*
        var_dump($array);
        var_dump($post);
        */
    } 
}

function dateRangeFilter($array,$dateKey,$start,$end){
    $new=[];
    for($i=0;$i<count($array);$i++){
            if(strtotime($array[$i][$dateKey])>=$start && strtotime($array[$i][$dateKey])<=$end)  $new[]=$array[$i];
    }
    return $new;
}
function adjustFlow($aa){
    $f=[];
    foreach(array_keys($aa) as $k){
        foreach($aa[$k] as $r){
            $r["objectType"]=substr($k, 0, -1);
            $f[]=$r;
        }
    }
    sortByDateAll($f);
    array_reverse($f);
    return $f;
}
function sortByDateAll(&$ar){
    usort($ar, function ($item1, $item2) {
        return strtotime($item1['publishedDate']) <=> strtotime($item2['publishedDate']);
    });
}

function iten($n){
    $sos=[
        "previsto il"=>"planned",
        "effettuato il"=>"executed",
        "importo"=>"amount",
        "titolo"=>"title",
        "descrizione breve"=>"shortDescription",
        "descrizione in html"=>"htmlDescription",
        "data di creazione"=>"createdDate",
        "data di pubblicazione"=>"publishedDate",
        "data di ultima modifica"=>"lastEditedDate",
        "url anteprima"=>"thumbnailUrl",
        "url documento"=>"documentUrl",
        "data di inizio"=>"startDate",
        "data di fine"=>"endDate",
        "partecipanti"=>"participants",
        "tipo"=>"isAssembly",
        "data inizio raccolta risposte"=>"startDate",
        "data fine raccolta risposte"=>"endDate",
        "data pubblicazione dei risultati"=>"publishResultsDate",
        "testo in html"=>"htmlDescription",
        "titolo domanda"=>"questionTitle",
        "domanda"=>"questionText",
        "titolo risposta"=>"answerTitle",
        "risposta"=>"answerText",
        "data richiesta"=>"askDate",
        "data risposta"=>"answerDate",
        "richiedente"=>"asker",
        "rispondente"=>"replier"
    ];  
    return $sos[strtolower($n)]?:$n;


}
function contains($string, $array, $caseSensitive = true)
{
    $stripedString = $caseSensitive ? str_replace($array, '', $string) : str_ireplace($array, '', $string);
    return strlen($stripedString) !== strlen($string);
}
function AAdateRect($array,$prase=["date","planned","executed"]){
    foreach (array_keys($array) as $k){
        if(contains($k,$prase)){
                $array[$k]=dateRectify($array[$k]);
        }
    }
    return $array;

}
function AALRect(&$list){
    for($i=0;$i<count($list);$i++){
        $list[$i]=AAdateRect($list[$i]);
    }
}
function dateRectify($dateAll){
    list($date,$time)=explode(" ",$dateAll,2);
    $d=explode("-",$date);
    if(count($d)<3) $d=explode("/",$date);
    if($d[0]<13) return trim($d[2]."-".$d[1]."-".$d[0]." ".$time);  //d m y  to y-m-d
    return trim($d[0]."-".$d[1]."-".$d[2]." ".$time); 
}

function uid($t,$pdo){
    $id=substr($t, 1);
    $type=strtolower($t[0]);
    if(!is_numeric($id)) return (-1);
    switch($type){
        case "t":
            $re=listCont($pdo,$id);
        break;
        case "q":
            $re=listDomande($pdo,$id);
            break;
        case "d":
            $re=listDocs($pdo,$id);
        break;
        case "e":
            $re=listEvents($pdo,$id);
        break;
        case "f":
            $re=listFeedbacks($pdo,$id);
        break;
        default:
        return (-1);
            break;
    }
    return $re;
}
function aritenWrap($mix){
    for($i=0;$i<count($mix);$i++){
        $mix[$i]=ariten($mix[$i]);
    }
    return $mix;
}
function ariten($arr){
    foreach($arr as $k=>$v){
        if( ($nk=iten($k)) != $k){
            $arr[$nk]=$v;
            unset($arr[$k]);
        }
    }
    return $arr;
}
use Spatie\ArrayToXml\ArrayToXml;
function listDocs($pdo,$id=null){
    if(!$id){
        $q=$pdo->prepare('SELECT * FROM "Documenti" WHERE DATETIME("NOW") >=  strftime("%s", "Data di pubblicazione") ORDER BY "Data di pubblicazione" DESC');
        $q->execute();
        $docs=$q->fetchAll(PDO::FETCH_ASSOC);
    }
    else{
        $q=$pdo->prepare('SELECT * FROM "Documenti" WHERE DATETIME("NOW") >=  strftime("%s", "Data di pubblicazione") AND ID=:id ORDER BY "Data di pubblicazione" DESC');
        $q->execute([":id"=>$id]);
        $docs=$q->fetchAll(PDO::FETCH_ASSOC);
    }

    $docs=aritenWrap($docs);
    $t=$pdo->prepare('SELECT * FROM "Eventi" WHERE DATETIME("NOW") >=  strftime("%s", "Data di pubblicazione") ORDER BY "Data di pubblicazione" DESC');
    $t->execute();
    $evs=$t->fetchAll(PDO::FETCH_ASSOC);

    $t=$pdo->prepare('SELECT * FROM "Contabilita" WHERE DATETIME("NOW") >=  strftime("%s", "Data di pubblicazione") ORDER BY "Data di pubblicazione" DESC');
    $t->execute();
    $cnts=$t->fetchAll(PDO::FETCH_ASSOC);

    $t=$pdo->prepare('SELECT * FROM "Domande" WHERE DATETIME("NOW") >=  strftime("%s", "Data di pubblicazione") ORDER BY "Data di pubblicazione" DESC');
    $t->execute();
    $qts=$t->fetchAll(PDO::FETCH_ASSOC);

    $t=$pdo->prepare('SELECT * FROM "Feedbacks" WHERE DATETIME("NOW") >=  strftime("%s", "Data di pubblicazione") ORDER BY "Data di pubblicazione" DESC');
    $t->execute();
    $fbs=$t->fetchAll(PDO::FETCH_ASSOC);

    for($i=0;$i<count($docs);$i++){
$docs[$i]["ID"]=(int) $docs[$i]["ID"];
$docs[$i]["UID"]="d".$docs[$i]["ID"];
       for($j=0;$j<count($evs);$j++){ 
            if(in_array($docs[$i]["ID"],explode(",",$evs[$j]["Documenti collegati"]) )) $docs[$i]["links"]["from"]["events"][]=["title"=>$evs[$j]["Titolo"],"ID"=>(int)$evs[$j]["ID"],"UID"=>"e".$evs[$j]["ID"]];
        }

        for($j=0;$j<count($cnts);$j++){
            if(in_array($docs[$i]["ID"],explode(",",$cnts[$j]["Documenti collegati"]) )) $docs[$i]["links"]["from"]["transactions"][]=["title"=>$cnts[$j]["Titolo"],"ID"=>(int)$cnts[$j]["ID"],"UID"=>"t".$cnts[$j]["ID"]];
        }

        for($j=0;$j<count($qts);$j++){
            if(in_array($qts[$i]["ID"],explode(",",$qts[$j]["Documenti collegati"]) )) $docs[$i]["links"]["from"]["questions"][]=["title"=>$qts[$j]["Titolo Domanda"],"ID"=>(int)$qts[$j]["ID"],"UID"=>"q".$qts[$j]["ID"]];
        }

        for($j=0;$j<count($fbs);$j++){
            if(in_array($docs[$i]["ID"],explode(",",$fbs[$j]["Documenti collegati"]) )) $docs[$i]["links"]["from"]["feedbacks"][]=["title"=>$fbs[$j]["Titolo"],"ID"=>(int)$fbs[$j]["ID"],"UID"=>"f".$fbs[$j]["ID"]];
        }
        $docs[$i]["tags"]=explode(",",trim($docs[$i]["Tags"]));
        if($docs[$i]["tags"]==[""]) $docs[$i]["tags"]=[];
        unset($docs[$i]["Tags"]);
        if(!empty($docs[$i]["URL Firma Digitale"])){
            $docs[$i]["signatureUrl"]=$docs[$i]["URL Firma Digitale"];
        }
        unset($docs[$i]["URL Firma Digitale"]);
        $docs[$i]["format"]=["type"=>$docs[$i]["Tipo documento"],"description"=>DOCTypeDescr($docs[$i]["Tipo documento"]),"uiColor"=>colorByTypeDOC($docs[$i]["Tipo documento"])];
        unset($docs[$i]["Tipo documento"]);

    }
    return $docs;
}
function listCont($pdo,$id=null){
    if(!$id){
        $q=$pdo->prepare('SELECT * FROM "Contabilita" WHERE DATETIME("NOW") >=  strftime("%s", "Data di pubblicazione") ORDER BY "Data di pubblicazione" ASC');
        $q->execute();
        $docs=$q->fetchAll(PDO::FETCH_ASSOC);
    }
    else{
        $q=$pdo->prepare('SELECT * FROM "Contabilita" WHERE DATETIME("NOW") >=  strftime("%s", "Data di pubblicazione") AND ID=:id ORDER BY "Data di pubblicazione" ASC');
        $q->execute([":id"=>$id]);
        $docs=$q->fetchAll(PDO::FETCH_ASSOC);
    }
    $docs=aritenWrap($docs);
    $t=$pdo->prepare('SELECT * FROM "Feedbacks" WHERE "Data di pubblicazione" < DATETIME("NOW") ORDER BY "Data di pubblicazione" ASC');
    $t->execute();
    $fbs=$t->fetchAll(PDO::FETCH_ASSOC);

    $t=$pdo->prepare('SELECT * FROM "Domande" WHERE DATETIME("NOW") >=  strftime("%s", "Data di pubblicazione") ORDER BY "Data di pubblicazione" ASC');
    $t->execute();
    $qts=$t->fetchAll(PDO::FETCH_ASSOC);

    for($i=0;$i<count($docs);$i++){
$docs[$i]["ID"]=(int) $docs[$i]["ID"];
$docs[$i]["UID"]="t".$docs[$i]["ID"];
$docs[$i]["amount"]=(float)$docs[$i]["amount"];
        for($j=0;$j<count($qts);$j++){
            if(in_array($qts[$i]["ID"],explode(",",$qts[$j]["Contabilita collegata"]) )) $docs[$i]["links"]["from"]["questions"][]=["title"=>$qts[$j]["Titolo Domanda"],"ID"=>(int)$qts[$j]["ID"],"UID"=>"q".$qts[$j]["ID"]];
        }

        for($j=0;$j<count($fbs);$j++){
            if(in_array($docs[$i]["ID"],explode(",",$fbs[$j]["Feedback collegati"]) )) $docs[$i]["links"]["from"]["feedbacks"][]=["title"=>$fbs[$j]["Titolo"],"ID"=>(int)$fbs[$j]["ID"],"UID"=>"f".$fbs[$j]["ID"]];
        }
        $ef=explode(",",$docs[$i]["Documenti collegati"]);
        $ta=[];
        foreach($ef as $d){
            $t=$pdo->prepare('SELECT * FROM "Documenti" WHERE DATETIME("NOW") >=  strftime("%s", "Data di pubblicazione") AND ID=:id ORDER BY "Data di pubblicazione" DESC');
            $t->execute([":id"=>$d]);
            $tu=$t->fetch(PDO::FETCH_ASSOC);
            if($tu) $ta[]=["ID"=>(int)$d,"title"=>$tu["Titolo"],"UID"=>"d".$d];
        }
        
        if(count($ta)) $docs[$i]["links"]["to"]["documents"]=$ta;
        $docs[$i]["tags"]=explode(",",trim($docs[$i]["Tags"]));
        if($docs[$i]["tags"]==[""]) $docs[$i]["tags"]=[];
        unset($docs[$i]["Tags"]);
        unset($docs[$i]["Documenti collegati"]);
    }
    return $docs;
}
function listEvents($pdo,$id=null){
    if(!$id){
        $q=$pdo->prepare('SELECT * FROM "Eventi" WHERE DATETIME("NOW") >=  strftime("%s", "Data di pubblicazione") ORDER BY "Data di pubblicazione" DESC');
        $q->execute();
        $docs=$q->fetchAll(PDO::FETCH_ASSOC);
    }
    else{
        $q=$pdo->prepare('SELECT * FROM "Eventi" WHERE DATETIME("NOW") >=  strftime("%s", "Data di pubblicazione") AND ID=:id ORDER BY "Data di pubblicazione" DESC');
        $q->execute([":id"=>$id]);
        $docs=$q->fetchAll(PDO::FETCH_ASSOC); 
    }

    $docs=aritenWrap($docs);


    $t=$pdo->prepare('SELECT * FROM "Feedbacks" WHERE DATETIME("NOW") >=  strftime("%s", "Data di pubblicazione") ORDER BY "Data di pubblicazione" DESC');
    $t->execute();
    $fbs=$t->fetchAll(PDO::FETCH_ASSOC);

    $t=$pdo->prepare('SELECT * FROM "Domande" WHERE DATETIME("NOW") >=  strftime("%s", "Data di pubblicazione") ORDER BY "Data di pubblicazione" DESC');
    $t->execute();
    $qts=$t->fetchAll(PDO::FETCH_ASSOC); 

    for($i=0;$i<count($docs);$i++){
        
    $docs[$i]["participants"]=explode(",",$docs[$i]["participants"]);
    $docs[$i]["ID"]=(int) $docs[$i]["ID"];
    $docs[$i]["UID"]="e".(int) $docs[$i]["ID"];
    $docs[$i]["isAssembly"]=(bool)$docs[$i]["isAssembly"];
        for($j=0;$j<count($qts);$j++){
            if(in_array($qts[$i]["ID"],explode(",",$qts[$j]["Eventi collegati"]) )) $docs[$i]["links"]["from"]["questions"][]=["title"=>$qts[$j]["Titolo Domanda"],"ID"=>(int)$qts[$j]["ID"],"UID"=>"q".$qts[$j]["ID"]];
        }

        for($j=0;$j<count($fbs);$j++){
            if(in_array($docs[$i]["ID"],explode(",",$fbs[$j]["Eventi collegati"]) )) $docs[$i]["links"]["from"]["feedbacks"][]=["title"=>$fbs[$j]["Titolo"],"ID"=>(int)$fbs[$j]["ID"],"UID"=>"f".$fbs[$j]["ID"]];
        }
        $ef=explode(",",$docs[$i]["Documenti collegati"]);
        $ta=[];
        foreach($ef as $d){
            $t=$pdo->prepare('SELECT * FROM "Documenti" WHERE DATETIME("NOW") >=  strftime("%s", "Data di pubblicazione") AND ID=:id ORDER BY "Data di pubblicazione" DESC');
            $t->execute([":id"=>$d]);
            $tu=$t->fetch(PDO::FETCH_ASSOC);
            if($tu) $ta[]=["ID"=>(int)$d,"title"=>$tu["Titolo"],"UID"=>"d".$d];
        }
        if(count($ta)) $docs[$i]["links"]["to"]["documents"]=$ta;
        $docs[$i]["tags"]=explode(",",$docs[$i]["Tags"]);
        if($docs[$i]["tags"]==[""]) $docs[$i]["tags"]=[];
        unset($docs[$i]["Tags"]);
        unset($docs[$i]["Documenti collegati"]);
    }

    return $docs;
}


function listDomande($pdo,$id=null){
    if(!$id){
        $q=$pdo->prepare('SELECT * FROM "Domande" WHERE DATETIME("NOW") >=  strftime("%s", "Data di pubblicazione") ORDER BY "Data di pubblicazione" DESC');
        $q->execute();
        $docs=$q->fetchAll(PDO::FETCH_ASSOC);
    }
    else{
        $q=$pdo->prepare('SELECT * FROM "Domande" WHERE DATETIME("NOW") >=  strftime("%s", "Data di pubblicazione") AND ID=:id ORDER BY "Data di pubblicazione" DESC');
        $q->execute([":id"=>$id]);
        $docs=$q->fetchAll(PDO::FETCH_ASSOC);
    }

    $docs=aritenWrap($docs);
    for($i=0;$i<count($docs);$i++){
$docs[$i]["ID"]=(int) $docs[$i]["ID"];
$docs[$i]["UID"]="q".$docs[$i]["ID"];
        $docs[$i]["tags"]=explode(",",trim($docs[$i]["Tags"]));
        if($docs[$i]["tags"]==[""]) $docs[$i]["tags"]=[];
        unset($docs[$i]["Tags"]);

        $ef=explode(",",$docs[$i]["Documenti collegati"]);
        $ta=[];
        foreach($ef as $d){
            $t=$pdo->prepare('SELECT * FROM "Documenti" WHERE DATETIME("NOW") >=  strftime("%s", "Data di pubblicazione") AND ID=:id ORDER BY "Data di pubblicazione" DESC');
            $t->execute([":id"=>$d]);
            $tu=$t->fetch(PDO::FETCH_ASSOC);
            if($tu) $ta[]=["ID"=>(int)$d,"title"=>$tu["Titolo"],"UID"=>"d".$d];
        }
        if(count($ta)) $docs[$i]["links"]["to"]["documents"]=$ta;
        unset($docs[$i]["Documenti collegati"]);

        $ef=explode(",",$docs[$i]["Eventi collegati"]);
        $ta=[];
        foreach($ef as $d){
            $t=$pdo->prepare('SELECT * FROM "Eventi" WHERE DATETIME("NOW") >=  strftime("%s", "Data di pubblicazione") AND ID=:id ORDER BY "Data di pubblicazione" DESC');
            $t->execute([":id"=>$d]);
            $tu=$t->fetch(PDO::FETCH_ASSOC);
            if($tu) $ta[]=["ID"=>(int)$d,"title"=>$tu["Titolo"],"UID"=>"e".$d];
        }
        if(count($ta)) $docs[$i]["links"]["to"]["events"]=$ta;
        unset($docs[$i]["Eventi collegati"]);

        $ef=explode(",",$docs[$i]["Contabilita collegata"]);
        $ta=[];
        foreach($ef as $d){
            $t=$pdo->prepare('SELECT * FROM "Contabilita" WHERE DATETIME("NOW") >=  strftime("%s", "Data di pubblicazione") AND ID=:id ORDER BY "Data di pubblicazione" DESC');
            $t->execute([":id"=>$d]);
            $tu=$q->fetch(PDO::FETCH_ASSOC);
            if($tu) $ta[]=["ID"=>(int)$d,"title"=>$tu["Titolo"],"UID"=>"t".$d];
        }
        if(count($ta)) $docs[$i]["links"]["to"]["transactions"]=$ta;
        unset($docs[$i]["Contabilita collegata"]);

        $ef=explode(",",$docs[$i]["Feedback collegati"]);
        $ta=[];
        foreach($ef as $d){
            $t=$pdo->prepare('SELECT * FROM "Feedbacks" WHERE DATETIME("NOW") >=  strftime("%s", "Data di pubblicazione") AND ID=:id ORDER BY "Data di pubblicazione" DESC');
            $t->execute([":id"=>$d]);
            $tu=$q->fetch(PDO::FETCH_ASSOC);
            if($tu) $ta[]=["ID"=>(int)$d,"Titolo"=>$tu["Titolo"],"UID"=>"f".$d];
        }
        if(count($ta)) $docs[$i]["links"]["to"]["feedbacks"]=$ta;
        unset($docs[$i]["Feedback collegati"]);

    }
    return $docs;
}

function listFeedbacks($pdo,$id=null){
    if(!$id){
        $q=$pdo->prepare('SELECT * FROM "Feedbacks" WHERE DATETIME("NOW") >=  strftime("%s", "Data di pubblicazione") ORDER BY "Data di pubblicazione" DESC');
        $q->execute();
        $docs=$q->fetchAll(PDO::FETCH_ASSOC);
    }
    else{
        $q=$pdo->prepare('SELECT * FROM "Feedbacks" WHERE DATETIME("NOW") >=  strftime("%s", "Data di pubblicazione") AND ID=:id ORDER BY "Data di pubblicazione" DESC');
        $q->execute([":id"=>$id]);
        $docs=$q->fetchAll(PDO::FETCH_ASSOC);
    }
   
    $docs=aritenWrap($docs);
    $t=$pdo->prepare('SELECT * FROM "Domande" WHERE DATETIME("NOW") >=  strftime("%s", "Data di pubblicazione") ORDER BY "Data di pubblicazione" DESC');
    $t->execute();
    $qts=$t->fetchAll(PDO::FETCH_ASSOC);

    for($i=0;$i<count($docs);$i++){
$docs[$i]["ID"]=(int) $docs[$i]["ID"];
$docs[$i]["UID"]="f".$docs[$i]["ID"];
        for($j=0;$j<count($qts);$j++){
            if(in_array($qts[$i]["ID"],explode(",",$qts[$j]["Feedback collegati"]) )) $docs[$i]["links"]["from"]["questions"][]=["title"=>$qts[$j]["Titolo Domanda"],"ID"=>(int)$qts[$j]["ID"],"UID"=>"q".$qts[$j]["ID"]];
        }

        
        $ef=explode(",",$docs[$i]["Eventi collegati"]);
        $ta=[];
        foreach($ef as $d){
            $t=$pdo->prepare('SELECT * FROM "Eventi" WHERE DATETIME("NOW") >=  strftime("%s", "Data di pubblicazione") AND ID=:id ORDER BY "Data di pubblicazione" DESC');
            $t->execute([":id"=>$d]);
            $tu=$t->fetch(PDO::FETCH_ASSOC);
            if($tu) $ta[]=["ID"=>(int)$d,"title"=>$tu["Titolo"],"UID"=>"e".$d];
        }
        if(count($ta)) $docs[$i]["links"]["to"]["events"]=$ta;
        unset($docs[$i]["Eventi collegati"]);

        $ef=explode(",",$docs[$i]["Documenti collegati"]);
        $ta=[];
        foreach($ef as $d){
            $t=$pdo->prepare('SELECT * FROM "Documenti" WHERE DATETIME("NOW") >=  strftime("%s", "Data di pubblicazione") AND ID=:id ORDER BY "Data di pubblicazione" DESC');
            $t->execute([":id"=>$d]);
            $tu=$t->fetch(PDO::FETCH_ASSOC);
            if($tu) $ta[]=["ID"=>(int)$d,"title"=>$tu["Titolo"],"UID"=>"d".$d];
        }
        if(count($ta))  $docs[$i]["links"]["to"]["documents"]=$ta;
        unset($docs[$i]["Documenti collegati"]);

        $ef=explode(",",$docs[$i]["Contabilita collegata"]);
        $ta=[];
        foreach($ef as $d){
            $t=$pdo->prepare('SELECT * FROM "Contabilita" WHERE DATETIME("NOW") >=  strftime("%s", "Data di pubblicazione") AND ID=:id ORDER BY "Data di pubblicazione" DESC');
            $t->execute([":id"=>$d]);
            $tu=$t->fetch(PDO::FETCH_ASSOC);
            if($tu) $ta[]=["ID"=>(int)$d,"title"=>$tu["Titolo"],"UID"=>"t".$d];
        }
        if(count($ta)) $docs[$i]["links"]["to"]["transactions"]=$ta;
        unset($docs[$i]["Contabilita collegata"]);


        $docs[$i]["tags"]=explode(",",trim($docs[$i]["Tags"]));
        if($docs[$i]["tags"]==[""]) $docs[$i]["tags"]=[];
        unset($docs[$i]["Tags"]);
    }
    return $docs;
}

function listAll($pdo){
    $a=[];
    $a["documents"]=listDocs($pdo);
    $a["transactions"]=listCont($pdo);
    $a["feedbacks"]=listFeedbacks($pdo);
    $a["questions"]=listDomande($pdo);
    $a["events"]=listEvents($pdo);
    return $a;
}
function listTagsDetail($pdo){
    $tagsList=[];
    $heap=listAll($pdo);
    foreach($heap as $cat=>$list){
        foreach($list as $el){
            foreach($el["tags"] as $t){
                if(!$el["title"]) continue;
                $tagsList[$t]["usage"]["categories"][$cat][]=["title"=>$el["title"],"ID"=>$el["ID"],"UID"=>$el["UID"]];
                $tagsList[$t]["usage"]["timePoints"][dateRectify($el["publishedDate"])][]=["title"=>$el["title"],"ID"=>$el["ID"],"UID"=>$el["UID"]];
            }
      }
    }
    return $tagsList;
}
function listTags($pdo){
    $tagsList=[];
    $heap=listAll($pdo);
    foreach($heap as $cat=>$list){
        foreach($list as $el){
            foreach($el["tags"] as $t){
                $tagsList[$t]["usage"]["categories"][$cat]++;
                $tagsList[$t]["usage"]["timePoints"][$el["publishedDate"]]++;
            }
      }
    }
    return $tagsList;
}

function jsonSave($file,$data){
    file_put_contents($file,json_encode($data,JSON_PRETTY_PRINT));
}
class Reply{
    private $enableCache,$cacheDir,$cacheTime,$ap;
    function __construct($enableCache,$cacheDir,$cacheTime,$ap){
        $this->enableCache=$enableCache;
        $this->cacheDir=$cacheDir;
        $this->cacheTime=$cacheTime;
        $this->ap=$ap;

    }
    function die($errorName="GENERIC_ERROR",$description="Un errore non identificato si è verificato",$httpStatusCode=400){
        http_response_code($httpStatusCode);
        $this->sendJson(["ok"=>false,"errorName"=>$errorName,"description"=>$description],true);
    }
    function send($data){
        $this->sendJson(["ok"=>true,"data"=>$data]);
    }
    
    function sendJson($data,$isError=false){
            $json=json_encode($data,JSON_PRETTY_PRINT);
            if($this->enableCache&&!$isError){
                $contFile =$this->cacheDir."/".str_replace("/","-",$this->ap);
                $data["cache"]["last"]=date("d-m-Y H:i:s");
                jsonSave($contFile,$data);
            }
            header("Content-type: application/json");
            header("Content-Length: ".strlen($json));
            echo $json;
        exit;
    }
}


function cachedReplier($enableCache,$cacheDir,$cacheTime,$ap){
    if(!$enableCache) return false;
    $contFile =$cacheDir."/".str_replace("/","-",$ap);
    if(!file_exists($contFile)) return false;
    $fc=file_get_contents($contFile);
    $fcc=json_decode($fc,true);
    if( strtotime($fcc["cache"]["last"])< (int) file_get_contents($cacheDir."/lastDbUpdate") ){
        return false;
    }
    header("Content-type: application/json");
    header("Content-Length: ".strlen($fc));
    echo $fc;
    exit;
}
