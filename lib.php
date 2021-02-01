<?php
function ITDate($UNIX){
    $mesi = array(1=>'gennaio', 'Febbraio', 'Marzo', 'Aprile',
                'Maggio', 'Giugno', 'Luglio', 'Agosto',
                'Settembre', 'Ottobre', 'Novembre','Dicembre');

$giorni = array('Domenica','Lunedì','Martedì','Mercoledì',
                'Giovedì','Venerdì','Sabato');

list($sett,$giorno,$mese,$anno) = explode('-',date('w-d-n-Y',$UNIX));

return  $giorni[$sett].' '.$giorno.' '.$mesi[$mese].' '.$anno;
}
function pdomake(){
    $p= new PDO("sqlite:database.db");
    $p->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $p->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
    $p->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
    return $p;
}
function DOCTypeDescr($type){
    $type=strtolower(trim($type));
    switch($type){
        case "pdf":
            return "Documento pdf";
        case "word":
            return "Documento di Microsoft Word";
        case "excel":
            return "Documento di Microsoft Excel";
        case "ppt";
            return "Documento di Microsoft Power Point";
    }
    return "Documento di formato sconosciuto";
}
function colorByTypeDOC($type){
    $type=strtolower(trim($type));
    switch($type){
        case "pdf":
            return "red";
        case "word":
            return "blue";
        case "excel":
            return "green";
        case "ppt";
            return "yellow";
    }
    return "gray";
}

function size($path){
    if(filter_var($path, FILTER_VALIDATE_URL)){
        $ch = curl_init($path);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_NOBODY, TRUE);
       
        $data = curl_exec($ch);
        $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
       
        curl_close($ch);
        return $size;
    }
    return @filesize($path)?:0;
}
function filename($path){
    if(filter_var($path, FILTER_VALIDATE_URL)){
        $ft = parse_url($path, PHP_URL_PATH);
        return basename($ft);
    }
    return basename($path);

}
function eur($i){
    return  "€ ".number_format($i, 2, ',', '\'');
}
function humanFileSize($size,$unit="") {
    if( (!$unit && $size >= 1<<30) || $unit == "GB")
      return number_format($size/(1<<30),2)."GB";
    if( (!$unit && $size >= 1<<20) || $unit == "MB")
      return number_format($size/(1<<20),2)."MB";
    if( (!$unit && $size >= 1<<10) || $unit == "KB")
      return number_format($size/(1<<10),2)."KB";
    return number_format($size)." bytes";
  }


function linkM($link,$text){
    if(empty($link) || empty($text)) return "";
    return '<a href="'.$link.'">'.htmlentities($text).'</a>';
}
function titlePrn($title,$val,$head="h3"){
    if(empty($title) || empty($val)) return "";
    return "<p><".$head.">".htmlentities($title)."</".$head."><br />".htmlentities($val)."</p>";
}
function thumbnailGen($url,$MAXheight=200,$MAXwidth=200,$alt="thumbnail"){
    return '<br /><img src="'.$url.'" onclick="location.href=\''.$url.'\';" style="max-width:'.$MAXwidth.'px;max-heght:'.$MAXheight.'px;" alt="'.htmlentities($alt).'" />';
}
function cats(){
    return [
        "documents"=>"Documenti",
        "transactions"=>"Contabilit&agrave;",
        "questions"=>"Domande",
        "events"=>"Eventi",
        "feedbacks"=>"Feedbacks"
    ];
}
function buildDoc($data){
        $res="";
        $res.=$data["htmlDescription"]; 
        $res.=thumbnailGen($data["thumbnailUrl"])."<br />";
        $res.="<h4>".linkM($data["documentUrl"],"File")."</h4>";
        if(!empty($data["signatureUrl"])) $res.="<h4>".linkM($data["signatureUrl"],"Firma digitale")."</h4>";
        $res.=titlePrn("Data di pubblicazione",ITDate(strtotime($data["publishedDate"])));
        $res.=titlePrn("Data di creazione",ITDate(strtotime($data["createdDate"])));
        $res.=titlePrn("Data di ultima modifica",ITDate(strtotime($data["lastEditedDate"])));
        foreach ($data["tags"] as $v) {
            $res.='<a href="https://innovationplaylist.eu/lens/app/api/view/tags/'.urlencode($v).'/detailed/render"><span class="w3-tag w3-black">'.htmlentities($v)."</span></a>";
        }
        
        $res.='<br /><span class="w3-tag w3-'.$data["format"]["uiColor"].'">'.htmlentities($data["format"]["description"])."</span>";
        if(count($data["links"]["to"]) >0){
            $res.='<br /><h2>Risorse collegate:</h2>';
            foreach(array_keys($data["links"]["to"]) as $category){
                    $res.="<div><h3>".cats()[$category]."</h3>";
                        foreach($data["links"]["to"][$category] as $el){
                            $res.='<p><a href="https://innovationplaylist.eu/lens/app/api/view/UID/'.$el["UID"].'/render">'.htmlEntities($el["title"]).'</a></p>';
                        }
                    $res.="</div>";
            }
        }
        if(count($data["links"]["from"]) >0){
            $res.='<br /><h2>Fanno riferimento a questa risorsa:</h2>';
            foreach(array_keys($data["links"]["to"]) as $category){
                    $res.="<div><h3>".cats()[$category]."</h3>";
                        foreach($data["links"]["to"][$category] as $el){
                            $res.='<p><a href="https://innovationplaylist.eu/lens/app/api/view/UID/'.$el["UID"].'/render">'.htmlEntities($el["title"]).'</a></p>';
                        }
                    $res.="</div>";
            }
        }
        return pageBuild($data["title"],$res);
    
}

function eurC($i){
    if($i>0) return '<h2 style="color:green">'.eur($i).'</h2>';
    if($i<0) return '<h2 style="color:red">'.eur($i).'</h2>';
    return '<h2 style="color:yellow">'.eur($i).'</h2>';
}
function buildTransaction($data){
    $res="";
    $res.=$data["htmlDescription"];
    $res.=thumbnailGen($data["thumbnailUrl"])."<br />";

    $res.=titlePrn("Data di pianificazione",ITDate(strtotime($data["planned"])));

    $res.=titlePrn("Data di esecuzione",ITDate(strtotime($data["executed"])));

    $res.='<h3>Importo: '.eurC($data["amount"]).'</h3>';

    $res.=titlePrn("Data di pubblicazione",ITDate(strtotime($data["publishedDate"])));
    $res.=titlePrn("Data di creazione",ITDate(strtotime($data["createdDate"])));
    $res.=titlePrn("Data di ultima modifica",ITDate(strtotime($data["lastEditedDate"])));
    foreach ($data["tags"] as $v) {
        $res.='<a href="https://innovationplaylist.eu/lens/app/api/view/tags/'.urlencode($v).'/detailed/render"><span class="w3-tag w3-black">'.htmlentities($v)."</span></a>";
    }
    $res.='<br /><span class="w3-tag w3-'.$data["format"]["uiColor"].'">'.htmlentities($data["format"]["description"])."</span>";
    if(count($data["links"]["to"]) >0){
        $res.='<br /><h2>Risorse collegate:</h2>';
        foreach(array_keys($data["links"]["to"]) as $category){
                $res.="<div><h3>".cats()[$category]."</h3>";
                    foreach($data["links"]["to"][$category] as $el){
                        $res.='<p><a href="https://innovationplaylist.eu/lens/app/api/view/UID/'.$el["UID"].'/render">'.htmlEntities($el["title"]).'</a></p>';
                    }
                $res.="</div>";
        }
    }
    if(count($data["links"]["from"]) >0){
        $res.='<br /><h2>Fanno riferimento a questa risorsa:</h2>';
        foreach(array_keys($data["links"]["to"]) as $category){
                $res.="<div><h3>".cats()[$category]."</h3>";
                    foreach($data["links"]["to"][$category] as $el){
                        $res.='<p><a href="https://innovationplaylist.eu/lens/app/api/view/UID/'.$el["UID"].'/render">'.htmlEntities($el["title"]).'</a></p>';
                    }
                $res.="</div>";
        }
    }
    return pageBuild($data["title"],$res);

}

function buildEvents($data){
    $res="";
    $res.=$data["htmlDescription"];
    $res.=thumbnailGen($data["thumbnailUrl"])."<br />";

    $res.=titlePrn("Data di inizio",ITDate(strtotime($data["startDate"])));

    $res.=titlePrn("Data di fine",ITDate(strtotime($data["endDate"])));

    if($data["isAssembly"]){
        $res.="<h2 style=\"color:green\">&Egrave; un&apos;assemblea</h2>";
    }

    $res.=titlePrn("Data di pubblicazione",ITDate(strtotime($data["publishedDate"])));
    $res.=titlePrn("Data di creazione",ITDate(strtotime($data["createdDate"])));
    $res.=titlePrn("Data di ultima modifica",ITDate(strtotime($data["lastEditedDate"])));
    foreach ($data["tags"] as $v) {
        $res.='<a href="https://innovationplaylist.eu/lens/app/api/view/tags/'.urlencode($v).'/detailed/render"><span class="w3-tag w3-black">'.htmlentities($v)."</span></a>";
    }
    $res.='<br /><span class="w3-tag w3-'.$data["format"]["uiColor"].'">'.htmlentities($data["format"]["description"])."</span>";
    if(count($data["links"]["to"]) >0){
        $res.='<br /><h2>Risorse collegate:</h2>';
        foreach(array_keys($data["links"]["to"]) as $category){
                $res.="<div><h3>".cats()[$category]."</h3>";
                    foreach($data["links"]["to"][$category] as $el){
                        $res.='<p><a href="https://innovationplaylist.eu/lens/app/api/view/UID/'.$el["UID"].'/render">'.htmlEntities($el["title"]).'</a></p>';
                    }
                $res.="</div>";
        }
    }
    if(count($data["participants"])){
        	$res.="<h3>Partecipanti</h3>";
        }
        foreach ($data["participants"] as $v) {
            $res.='<span class="w3-tag w3-white">'.htmlentities($v)."</span>";
        }
    if(count($data["links"]["from"]) >0){
        $res.='<br /><h2>Fanno riferimento a questa risorsa:</h2>';
        foreach(array_keys($data["links"]["to"]) as $category){
                $res.="<div><h3>".cats()[$category]."</h3>";
                    foreach($data["links"]["to"][$category] as $el){
                        $res.='<p><a href="https://innovationplaylist.eu/lens/app/api/view/UID/'.$el["UID"].'/render">'.htmlEntities($el["title"]).'</a></p>';
                    }
                $res.="</div>";
        }
    }
    return pageBuild($data["title"],$res);

}

function buildFeedback($data){
    $res="";
    $res.=$data["htmlDescription"]."<br />";
    $res.=thumbnailGen($data["thumbnailUrl"])."<br />";

    $res.=titlePrn("Data di inizio",ITDate(strtotime($data["startDate"])));
    $res.=titlePrn("Data di fine",ITDate(strtotime($data["endDate"])));
    $res.=titlePrn("Data di pubblicazione dei risultati",ITDate(strtotime($data["publishResultsDate"])));

    $res.=titlePrn("Data di pubblicazione",ITDate(strtotime($data["publishedDate"])));
    $res.=titlePrn("Data di creazione",ITDate(strtotime($data["createdDate"])));
    $res.=titlePrn("Data di ultima modifica",ITDate(strtotime($data["lastEditedDate"])));
    foreach ($data["tags"] as $v) {
        $res.='<a href="https://innovationplaylist.eu/lens/app/api/view/tags/'.urlencode($v).'/detailed/render"><span class="w3-tag w3-black">'.htmlentities($v)."</span></a>";
    }
    $res.='<br /><span class="w3-tag w3-'.$data["format"]["uiColor"].'">'.htmlentities($data["format"]["description"])."</span>";
    if(count($data["links"]["to"]) >0){
        $res.='<br /><h2>Risorse collegate:</h2>';
        foreach(array_keys($data["links"]["to"]) as $category){
                $res.="<div><h3>".cats()[$category]."</h3>";
                    foreach($data["links"]["to"][$category] as $el){
                        $res.='<p><a href="https://innovationplaylist.eu/lens/app/api/view/UID/'.$el["UID"].'/render">'.htmlEntities($el["title"]).'</a></p>';
                    }
                $res.="</div>";
        }
    }
    if(count($data["links"]["from"]) >0){
        $res.='<br /><h2>Fanno riferimento a questa risorsa:</h2>';
        foreach(array_keys($data["links"]["to"]) as $category){
                $res.="<div><h3>".cats()[$category]."</h3>";
                    foreach($data["links"]["to"][$category] as $el){
                        $res.='<p><a href="https://innovationplaylist.eu/lens/app/api/view/UID/'.$el["UID"].'/render">'.htmlEntities($el["title"]).'</a></p>';
                    }
                $res.="</div>";
        }
    }
    return pageBuild($data["title"],$res);

}



function buildQuestions($data){
    $res="";
    $res.=htmlentities($data["questionText"])."<br />";
    $res.=thumbnailGen($data["thumbnailUrl"])."<br />";
    $res.="<h3>".htmlentities($data["answerTitle"])."</h3>";
    $res.=htmlentities($data["answerText"]);


    $res.=titlePrn("Chiesta da",$data["asker"]);

    $res.=titlePrn("Risposta da",$data["replier"]);


    $res.=titlePrn("Data di ricezione",ITDate(strtotime($data["askDate"])));
    $res.=titlePrn("Data di risposta",ITDate(strtotime($data["answerDate"])));

    $res.=titlePrn("Data di pubblicazione",ITDate(strtotime($data["publishedDate"])));
    foreach ($data["tags"] as $v) {
        $res.='<a href="https://innovationplaylist.eu/lens/app/api/view/tags/'.urlencode($v).'/detailed/render"><span class="w3-tag w3-black">'.htmlentities($v)."</span></a>";
    }
    $res.='<br /><span class="w3-tag w3-'.$data["format"]["uiColor"].'">'.htmlentities($data["format"]["description"])."</span>";
    if(count($data["links"]["to"]) >0){
        $res.='<br /><h2>Risorse collegate:</h2>';
        foreach(array_keys($data["links"]["to"]) as $category){
                $res.="<div><h3>".cats()[$category]."</h3>";
                    foreach($data["links"]["to"][$category] as $el){
                        $res.='<p><a href="https://innovationplaylist.eu/lens/app/api/view/UID/'.$el["UID"].'/render">'.htmlEntities($el["title"]).'</a></p>';
                    }
                $res.="</div>";
        }
    }
    if(count($data["links"]["from"]) >0){
        $res.='<br /><h2>Fanno riferimento a questa risorsa:</h2>';
        foreach(array_keys($data["links"]["to"]) as $category){
                $res.="<div><h3>".cats()[$category]."</h3>";
                    foreach($data["links"]["to"][$category] as $el){
                        $res.='<p><a href="https://innovationplaylist.eu/lens/app/api/view/UID/'.$el["UID"].'/render">'.htmlEntities($el["title"]).'</a></p>';
                    }
                $res.="</div>";
        }
    }
    return pageBuild($data["questionTitle"],$res);

}










function pageMake($UID,$pdo){
    $data=uid($UID,$pdo);
    if($data==(-1)|| empty($UID)){
        return pageBuild("Errore","<h3>La risorsa richiesta non &egrave; stata trovata</h3>");
    }
    switch($UID[0]){
        case "d":
            return buildDoc($data[0]);
        break;
        case "t":
            return buildTransaction($data[0]);
        break;
        case "e":
            return buildEvents($data[0]);
        break;
        case "q":
            return buildQuestions($data[0]);
        break;
        case "f":
            return buildFeedback($data[0]);
        break;
    }
}

function pageBuild($title,$body){
    ob_start();
    ?><!DOCTYPE html> 
    <html>
    <head>
        <title>Lens - <?php echo htmlentities($title)?></title>
         <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, can-resize=no">
        <link rel="stylesheet" href="https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.css" />
        <script src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
        <script src="https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js"></script>
        <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
        <link href="https://fonts.googleapis.com/css2?family=Caveat&display=swap" rel="stylesheet">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment-with-locales.min.js" integrity="sha512-LGXaggshOkD/at6PFNcp2V2unf9LzFq6LE+sChH7ceMTDP0g2kn6Vxwgg7wkPP7AAtX+lmPqPdxB47A0Nz0cMQ==" crossorigin="anonymous"></script>
    
    </head>
    
    <body>
        <div data-role="page" id="main">
            <div data-role="header" data-position="fixed">
                <h1 style="font-family: 'Caveat', cursive;font-size:40px;">Lens</h1>
                <a href="https://innovationplaylist.eu/lens/app" data-icon="home" class="ui-btn-right"></a>
            </div>
            <h1><?php echo htmlentities($title)?></h1>
            <?php
            echo $body;
            ?>
            <br />
            <a href="https://innovationplaylist.eu/lens/app" >Home</a>
        </div>
    </body>
    </html>
    <?php
    return ob_get_contents();
}

function renderTag($data,$tag){
    $lk=[
        "documents"=>"Documenti",
    "transactions"=>"Contabilit&agrave;",
    "questions"=>"Domande",
    "events"=>"Eventi",
    "feedbacks"=>"Feedbacks"
    ];
    $res="";
    $res.="<h3>Utilizzo</h3>";
    $res.="<h2>Per risorsa</h2>";
    foreach(array_keys($data["usage"]["categories"])  as $k  ){
        $res.="<h4>".htmlentities($lk[$k])."</h4>";
        $i=0;
        foreach($data["usage"]["categories"][$k] as $r){
            if(!$r["title"]) continue;
            $res.='<a href="https://innovationplaylist.eu/lens/app/api/view/UID/'.$r["UID"].'">'.htmlentities($r["title"]).'</a>';
            $i++;
        }
        if($i==0) $res.="<i>Dati mancanti</i>";
    }

    $res.="<hr /><br /><h2>Per periodo di utilizzo</h2>";
    foreach(array_keys($data["usage"]["timePoints"])  as $k  ){
        $i=0;
        $res.="<h4>".htmlentities(ITDate(strtotime($k)))."</h4>";
        foreach($data["usage"]["timePoints"][$k] as $r){
            if(!$r["title"]) continue;
            $res.='<a href="https://innovationplaylist.eu/lens/app/api/view/UID/'.$r["UID"].'">'.htmlentities($r["title"]).'</a>';
            $i++; 
        }
        if($i==0) $res.="<i>Dati mancanti</i>";
        
    }
    return pageBuild("Tag: ".$tag,$res);
}