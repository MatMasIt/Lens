function htmlEntities(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

class NodeLinks{
    constructor(elLinks){
        // is APIobj["links"]
        this.elLinks=elLinks;
        this.lookup={
            "documents":"Documenti",
            "transactions":"Contabilit&agrave;",
            "questions":"Domande",
            "events":"Eventi",
            "feedbacks":"Feedbacks"
        }
    }
    computeHTML(){
        if(!this.elLinks) return "";
        this.html='<div class="linkList">'
        var flag=false,list=[];
        var dict=null;
        
        if(Object.keys(this.elLinks).includes("to")) dict=this.elLinks["to"];
        if(dict!=null){
            var ks=Object.keys(dict);
            if(ks.length>0&&!flag){
                this.html+='<span>Risorse collegate:</span><div data-role="collapsibleset" data-theme="a" data-content-theme="a">';
                flag=true;
            }
            ks.forEach(category => {
                    list=dict[category];
                    this.html+="<div><h3>"+this.lookup[category]+"</h3>";
                    list.forEach(obj => {
                        this.html+='<p><button type="button" onclick="location.href=\'api/view/UID/'+obj["UID"]+'/render\';">'+htmlEntities(obj["title"])+'</button></p>';
                    });
                    this.html+="</div>";
                });
            if(ks.length>0&&flag){
                this.html+="</div>";
            }

        }
        flag=false;
        list=[];
        dict=this.elLinks["from"]
        if(dict!=null){
            ks=Object.keys(dict);
            if(ks.length>0&&!flag){
                this.html+='<span>Fanno riferimento a questa risorsa:</span><div>';
                flag=true;
            }
            ks.forEach(category => {
                    list=dict[category];
                    this.html+="<div><h3>"+this.lookup[category]+"</h3>";
                    list.forEach(obj => {
// alert(JSON.stringify(obj)); 
                      this.html+='<p><button type="button" onclick="location.href=\'api/view/UID/'+obj["UID"]+'/render\';">'+htmlEntities(obj["title"])+'</a></p>';
                    });
                    this.html+="</div>";
                });
            if(ks.length>0&&flag){
                this.html+="</div>";
            }
        }



        this.html+="</div>";
       // alert(this.html);
        return this.html;
    }
}
class Tags{
    constructor(tags){
        this.tags=tags;
    }
    computeHTML(){
        if(this.tags!=null&&this.tags.length>0){
            this.html+="<div><h3>Tag</h3>";
            this.tags.forEach(element => {
                this.html+='<p><a href="api/view/tags/'+encodeURI(element)+'/render">'+htmlEntities(element)+'</a></p>';
            });
        }
    }
}
function eurSet(eur) {
    
}
class ElementPreviewList{
    constructor(){
        this.k2it={
            "planned": "Previsto il",
            "executed": "Effettuato il",
            "amount": "Importo",
            "title": "Titolo",
            "shortDescription": "Descrizione breve",
            "htmlDescription": "Testo in html",
            "createdDate": "Data di creazione",
            "publishedDate": "Data di pubblicazione",
            "lastEditedDate": "Data di ultima modifica",
            "thumbnailUrl": "Url anteprima",
            "documentUrl": "Url documento",
            "startDate": "Data inizio",
            "endDate": "Data fine",
            "participants": "Partecipanti",
            "isAssembly": "Tipo",
            "publishResultsDate": "Data pubblicazione dei risultati",
            "questionTitle": "Titolo domanda",
            "questionText": "Domanda",
            "answerTitle": "Titolo risposta",
            "answerText": "Risposta",
            "askDate": "Data richiesta",
            "answerDate": "Data risposta",
            "asker": "Richiedente",
            "replier": "Rispondente"
        };        

    }

    itLookup(key){
        return this.k2it[key]?this.k2it[key]:key;
    }
    itLookupPairDict(dict,key){
        return {
            "value":dict[key]?dict[key]:"",
            "name":this.itLookup(key)
        };
    }
    textGen(title,value){
        if(value && title){
            return "<span><h3>"+htmlEntities(title)+"</h3><br/><p>"+htmlEntities(value)+"</p></span>";
        }
        return "";
    }
    listgen(title,values=[],colors=[],uids=[]){
        var html="";
        if(title && values.length>0){
            html+="<h3>"+htmlEntities(title)+"</h3><br />";
            for (let i = 0; i < values.length; i++) {
                if(!values[i]) continue;
                var color=colors[i]? colors[i]: (colors[0] ? colors[0] : "white");
                var a="<span>";
                if(uids[i]) a='<span style="text-decoration: underline;" onclick="location.href=\'api/view/UID/'+uids[i]+'\';">';
                html+=a+'<span class="w3-tag w3-'+color+'">'+htmlEntities(values[i])+'</span></span>';
                
            }
            return html;
        }

    }
    conditonalTextGen(title,bool,yes,no=""){
        return this.textGen(title,bool?yes:no);
    }
    boolGen(title,value){
        var res= value?"S&igrave;":"No";
        var color= value?"green":"red";
        return this.listgen(title,[res],[color],[]);
    }
    thumbnailGen(url,MAXheight=200,MAXwidth=200,alt="thumbnail"){
            return '<img src="'+url+'" onclick="location.href=\''+url+'\';" style="max-width:'+MAXwidth+'px;max-heght:'+MAXheight+'px;" alt="'+htmlEntities(alt)+'" />';
    }
    tagsgen(title,values=[],colors=[]){
        var html="";
        if(title && values.length>0){
            html+="<h3>"+htmlEntities(title)+"</h3><br />";
            for (let i = 0; i < values.length; i++) {
                if(!values[i]) continue;
                var color=colors[i]? colors[i]: (colors[0] ? colors[0] : "black");
                var a='<span style="text-decoration: underline;" onclick="location.href=\'api/view/tags/'+values[i]+'/detailed/render\';">';
                html+=a+'<span class="w3-tag w3-'+color+'">'+htmlEntities(values[i])+'</span></span>';
                
            }
            return html;
        }

    }

    generateLi(type,array){
        var tLihtml="",t;
        
        switch (type) {
            case "document":
                array.forEach(element => {
                    tLihtml+='<li>\n<a href="api/view/UID/'+element["UID"]+'/render">';

                    tLihtml+="<h1 style=\"font-size:30px;\">"+htmlEntities(element["title"])+"</h1>";

                    tLihtml+="<br /><hr />";
                    
                    tLihtml+="<span> "+this.thumbnailGen(element["thumbnailUrl"],200,200,element["title"])+"</span>";
                    tLihtml+="<br /><hr />";
                    


                    tLihtml+="<h4>"+htmlEntities(element["shortDescription"])+"</h4>";
                    tLihtml+="<br /><hr />";
                    
                    
                    tLihtml+= "<span>"+this.listgen("Formato",[ element["format"]["type"],element["format"]["description"] ],[element["format"]["uiColor"]]  )+"</span>";
                   
                    tLihtml+="<br /><hr />";
                    
                    t=this.itLookupPairDict(element,"publishedDate");
                    tLihtml+="<span> "+this.textGen(t["name"],(new TimeFormatter(t["value"])).compute("LLLL") )+"</span>";

                    tLihtml+="<br /><hr />";
                    
                    tLihtml+= "<span>"+this.tagsgen("Tag",element["tags"])+"</span>";

                    tLihtml+="<br /><hr />";
                    
                    var nl=new NodeLinks(element["links"]);
                    tLihtml+= nl.computeHTML();
                    tLihtml+='</a></li>';
                });
               
                break;
                case "event":
                    array.forEach(element => {
                        tLihtml+='<li>\n<a href="api/view/UID/'+element["UID"]+'/render">';

                    tLihtml+="<h1 style=\"font-size:30px;\">"+htmlEntities(element["title"])+"</h1>";

                    tLihtml+="<br /><hr />";
                    
                        tLihtml+="<span> "+this.thumbnailGen(element["thumbnailUrl"],200,200,element["title"])+"</span>";


                        tLihtml+="<br /><hr />";
                    
                        

                    tLihtml+="<h4>"+htmlEntities(element["shortDescription"])+"</h4>";
                    tLihtml+="<br /><hr />";
                    
                        tLihtml+=  this.conditonalTextGen(" ",element["isAssembly"],"È un assemblea");


                        tLihtml+="<br /><hr />";
                    
                        t=this.itLookupPairDict(element,"startDate");
                        tLihtml+="<span> "+this.textGen(t["name"],(new TimeFormatter(t["value"])).compute("LLLL") )+"</span>";


                        tLihtml+="<br /><hr />";
                    
                        t=this.itLookupPairDict(element,"endDate");
                        tLihtml+="<span> "+this.textGen(t["name"],(new TimeFormatter(t["value"])).compute("LLLL") )+"</span>";


                        tLihtml+="<br /><hr />";
                    
                        tLihtml+= "<span> "+this.listgen("Partecipanti",element["participants"])+"</span>";


                        tLihtml+="<br /><hr />";
                    
                        t=this.itLookupPairDict(element,"publishedDate");

                        tLihtml+="<span> "+this.textGen(t["name"],(new TimeFormatter(t["value"])).compute("LLLL") )+"</span>";


                        tLihtml+="<br /><hr />";
                    
                        tLihtml+= "<span> "+this.tagsgen("Tag",element["tags"])+"</span>";


                        tLihtml+="<br /><hr />";
                    
                        var nl=new NodeLinks(element["links"]);
                        tLihtml+= nl.computeHTML();
                        tLihtml+='</a></li>';
                    });
                break;
                case "feedback":
                    array.forEach(element => {
                        tLihtml+='<li>\n<a href="api/view/UID/'+element["UID"]+'/render">';

                    tLihtml+="<h1 style=\"font-size:30px;\">"+htmlEntities(element["title"])+"</h1>";

                        tLihtml+="<br /><hr />";
                        tLihtml+="<br /><hr />";

                        tLihtml+="<span> "+this.thumbnailGen(element["thumbnailUrl"],200,200,element["title"])+"</span>";
                        tLihtml+="<br /><hr />";

                        

                    tLihtml+="<h4>"+htmlEntities(element["shortDescription"])+"</h4>";
                    tLihtml+="<br /><hr />";


                        tLihtml+="<br /><hr />";
                        t=this.itLookupPairDict(element,"startDate");
                        tLihtml+="<span> "+this.textGen(t["name"],(new TimeFormatter(t["value"])).compute("LLLL") )+"</span>";

                        tLihtml+="<br /><hr />";
                        t=this.itLookupPairDict(element,"endDate");
                        tLihtml+="<span> "+this.textGen(t["name"],(new TimeFormatter(t["value"])).compute("LLLL") )+"</span>";

                        tLihtml+="<br /><hr />";
                        t=this.itLookupPairDict(element,"publishResultsDate");
                        tLihtml+="<span> "+this.textGen(t["name"],(new TimeFormatter(t["value"])).compute("LLLL") )+"</span>";

                        tLihtml+="<br /><hr />";
                        this.itLookupPairDict(element,"publishedDate")

                        tLihtml+="<span> "+this.textGen(t["name"],(new TimeFormatter(t["value"])).compute("LLLL") )+"</span>";

                        tLihtml+="<br /><hr />";
                        tLihtml+= "<span> "+this.tagsgen("Tag",element["tags"])+"</span>";

                        var nl=new NodeLinks(element["links"]);
                        tLihtml+= nl.computeHTML();
                        tLihtml+='</a></li>';
                    });
                break;
                case "question":
                    array.forEach(element => {
                            tLihtml+='<li>\n<a href="api/view/UID/'+element["UID"]+'/render">';

                    tLihtml+="<h1 style=\"font-size:30px;\">"+htmlEntities(element["questionTitle"])+"</h1>";

                            tLihtml+="<br /><hr />";

                            tLihtml+="<br /><hr />";
                            tLihtml+="<span> "+this.thumbnailGen(element["thumbnailUrl"],200,200,element["title"])+"</span>";

                            tLihtml+="<br /><hr />";
                            t=this.itLookupPairDict(element,"publishedDate")

                            tLihtml+="<span> "+this.textGen(t["name"],(new TimeFormatter(t["value"])).compute("LLLL") )+"</span>";

                            tLihtml+="<br /><hr />";
                            tLihtml+= "<span> "+this.tagsgen("Tag",element["tags"])+"</span>";

                            var nl=new NodeLinks(element["links"]);
                            tLihtml+= nl.computeHTML();
                            tLihtml+='</a></li>';
                        });
                        break;
                    case "transaction":
                        array.forEach(element => {
                        tLihtml+='<tr>';

                        tLihtml+="<td> "+this.thumbnailGen(element["thumbnailUrl"],200,200,element["title"])+"</td>";


                        t=this.itLookupPairDict(element,"title");

                        tLihtml+="<td> "+this.textGen(" ",t["value"])+"</td>";

                        t=this.itLookupPairDict(element,"planned")

                        tLihtml+="<td> "+this.textGen(" ",(new TimeFormatter(t["value"])).compute("LLLL") )+"</td>";

                        t=this.itLookupPairDict(element,"executed")

                        tLihtml+="<td> "+this.textGen(" ",(new TimeFormatter(t["value"])).compute("LLLL") )+"</td>";

                        tLihtml+="<td>"+eurS(element["amount"])+"</td>";

                        t=this.itLookupPairDict(element,"shortDescription");
                        
                        tLihtml+="<td> "+this.textGen(" ",t["value"])+"</td>";

                        t=this.itLookupPairDict(element,"publishedDate")

                        tLihtml+="<td> "+this.textGen(" ",(new TimeFormatter(t["value"])).compute("LLLL") )+"</td>";
                      

                        tLihtml+= "<td> "+this.tagsgen(" ",element["tags"])+"</td>";

                        tLihtml+= '<td><a href="api/view/UID/'+element["UID"]+'/render">link</a></td>';

                        tLihtml+"<td></td>";

                        /*var nl=new NodeLinks(element["links"]);
                        tLihtml+= nl.computeHTML();*/
                        tLihtml+='</tr>';
                    });
                    break;
        }
        return tLihtml;
    }
}
class TimeFormatter{
	constructor(timeString=null){
        if(timeString) this.m=moment(timeString);
        else this.m=moment();
        if(this.m.locale()!="it") this.m.locale("it",null);
	}
	compute(format){
        return this.m.format(format);
		}
    }
    
class APIfetch{
    constructor(epl){
        this.epl=epl;
    }

    fetchDocs(start=false,end=false){
        $.post("api/view/documents/all/",{"start":start,"end":end}, function(data){
          this.renderDocs(data);
        }.bind(this),"json");
    }
    fetchEvents(start=false,end=false){
        $.post("api/view/events/all/",{"start":start,"end":end}, function(data){
          this.renderEvents(data);
        }.bind(this),"json");
    }

    fetchFeedbacks(start=false,end=false){
        $.post("api/view/feedbacks/all/",{"start":start,"end":end}, function(data){
          this.renderFeedbacks(data);
        }.bind(this),"json");
    }

    fetchQuestions(start=false,end=false){
        $.post("api/view/questions/all/",{"start":start,"end":end}, function(data){
          this.renderQuestions(data);
        }.bind(this),"json");
    }

    fetchTransactions(start=false,end=false){
        $.post("api/view/transactions/all/",{"start":start,"end":end}, function(data){
          this.renderTransactions(data);
        }.bind(this),"json");
    }


    renderTransactions(data){
        var a= (new ElementPreviewList()).generateLi("transaction",data["data"]);
        console.log(a);
            $("#accountingList").html(a);
            $("#accountingList").enhanceWithin();
            $("#accountingList").listview('refresh');
    }

    renderFeedbacks(data){
        var a= (new ElementPreviewList()).generateLi("feedback",data["data"]);
            $("#feedbacksList").html(a);
            $("#feedbacksList").enhanceWithin();
            $("#feedbacksList").listview('refresh');
    }
    renderQuestions(data){
        var a= (new ElementPreviewList()).generateLi("question",data["data"]);
            $("#questionsList").html(a);
            $("#questionsList").enhanceWithin();
            $("#questionsList").listview('refresh');
    }
    renderDocs(data){
            var a= (new ElementPreviewList()).generateLi("document",data["data"]);
            $("#documentsList").html(a);
            $("#documentsList").enhanceWithin();
            $("#documentsList").listview('refresh');
    }
    renderEvents(data){
            var a= (new ElementPreviewList()).generateLi("event",data["data"]);
            $("#eventsList").html(a);
            $("#eventsList").enhanceWithin();
            $("#eventsList").listview('refresh');
    }
}

function eurS(val){
    if(val==0) return "<h2 style=\"color:yellow\">&euro;"+formatMoney(val,2,",","'")+"</h2>";
    if(val<0) return "<h2 style=\"color:red\">&euro;"+formatMoney(val,2,",","'")+"</h2>";
    return "<h2 style=\"color:green\">&euro;"+formatMoney(val,2,",","'")+"</h2>";
}

function formatMoney(number, decPlaces, decSep, thouSep) {
    decPlaces = isNaN(decPlaces = Math.abs(decPlaces)) ? 2 : decPlaces,
    decSep = typeof decSep === "undefined" ? "." : decSep;
    thouSep = typeof thouSep === "undefined" ? "," : thouSep;
    var sign = number < 0 ? "-" : "";
    var i = String(parseInt(number = Math.abs(Number(number) || 0).toFixed(decPlaces)));
    var j = (j = i.length) > 3 ? j % 3 : 0;
    
    return sign +
        (j ? i.substr(0, j) + thouSep : "") +
        i.substr(j).replace(/(\decSep{3})(?=\decSep)/g, "$1" + thouSep) +
        (decPlaces ? decSep + Math.abs(number - i).toFixed(decPlaces).slice(2) : "");
    }
function searchE(listArr,key){
    for (let i = 0; i < listArr.length; i++) {
        var e = listArr[i];
        if(e["name"]==key) return e["value"];
    }
      
}
var epl= new ElementPreviewList();
var api= new APIfetch(epl);
api.fetchDocs();
api.fetchEvents();
api.fetchFeedbacks();
api.fetchQuestions();
api.fetchTransactions();

var start,end;
$(document).delegate("form[data-act=search]","submit",function(){
        var data=$(this).serializeArray();
        start=searchE(data,"start")? searchE(data,"start"):"false";
        end=searchE(data,"end")? searchE(data,"start") :"false";
        switch(searchE(data,"search")){
            case "events":
                api.fetchEvents(start,end);
                break;
            case "documents":
                api.fetchDocs(start,end);
                break;
            case "feedbacks":
                api.fetchFeedbacks(start,end);
                break;
            case "questions":
                api.fetchQuestions(start,end);
                break;
            case "transactions":
                api.fetchTransactions(start,end);
                break;
        }

        return false;
});
function onKonamiCode(cb) {
    var input = '';
    var key = '38384040373937396665';
    document.addEventListener('keydown', function (e) {
      input += ("" + e.keyCode);
      if (input === key) {
        return cb();
      }
      if (!key.indexOf(input)) return;
      input = ("" + e.keyCode);
    });
  }
  
  onKonamiCode(function () {   window.open('https://www.youtube.com/watch?v=9YG9INjO91Y', '_blank'); });
