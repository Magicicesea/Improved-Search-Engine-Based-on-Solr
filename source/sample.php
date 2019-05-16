<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);    
header('Access-Control-Allow-Origin: *');

// make sure browsers see this page as utf-8 encoded HTML
include 'SpellCorrector.php';
include 'simple_html_dom.php'; 

header('Content-Type: text/html; charset=utf-8');
$limit = isset($_REQUEST['limit'])? $_REQUEST['limit'] : 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$kw = isset($_REQUEST['kw']) ? $_REQUEST['kw'] : NULL;
//$kw = 10;
$additionalParameters = [
	//'fl' => isset($_REQUEST['fl'])? $_REQUEST['fl'] : '',
    'fl' => 'title, og_description, id,  og_url',
	'sort' => isset($_REQUEST['sort']) && $_REQUEST['sort'] == 'pageRankFile' ? 'pageRankFile desc': '',
    //'sort' => 'pageRankFile desc'
];
$results = false;
if ($kw) {
    
    $text = file_get_contents("http://localhost:8983/solr/myexample/suggest?q=".urlencode($kw));
    $tmp = json_decode($text,true);
    header('Content-Type: application/json');
    echo json_encode($tmp);
    return;
//     $text= post("localhost:8983/solr/myexample/suggest?q=".$kw);//q=xxx
} else {
if ($query)
{
 // The Apache Solr Client library should be on the include path
 // which is usually most easily accomplished by placing in the
 // same directory as this script ( . or current directory is a default
 // php include path entry in the php.ini)
 require_once('Apache/Solr/Service.php');
 // create a new solr service instance - host, port, and corename
 // path (all defaults in this example)
    
 $solr = new Apache_Solr_Service('localhost', 8983, '/solr/myexample/');
 
    
 // if magic quotes is enabled then stripslashes will be needed
 if (get_magic_quotes_gpc() == 1)
 {
 $query = stripslashes($query);
 }
 // in production code you'll always want to use a try /catch for any
 // possible exceptions emitted by searching (i.e. connection
 // problems or a query parsing error)
 try
 {
 $results = $solr->search($query, 0, $limit,$additionalParameters);
 //$results = $solr->search('content:blah', 0, 10, array('sort' => 'timestamp desc'));
 }
 catch (Exception $e)
 {
 // in production you'd probably log or email this error to an admin
 // and then show a special message to the user but for this example
 // we're going to show the full exception
 die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
 }
}
?>
<html>
 <head>
 <title>CSCI 572 Search Engine Homework</title>
 <script>
function showResult(str) {
  if (str.length==0) { 
    document.getElementById("livesearch").innerHTML="";
    document.getElementById("livesearch").style.border="0px";
    return;
  }
  if (window.XMLHttpRequest) {
    // code for IE7+, Firefox, Chrome, Opera, Safari
    xmlhttp=new XMLHttpRequest();
  } else {  // code for IE6, IE5
    xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
  xmlhttp.onreadystatechange=function() {
    if (this.readyState==4 && this.status==200) {
      var response = JSON.parse(this.responseText);

      var suggestArr = response["suggest"]["suggest"][str]["suggestions"];
      console.log(suggestArr);
      document.getElementById("livesearch").innerHTML = '';
        
      if(!document.getElementById("sort1").checked){
          sortString = "pageRankFile";
      }else{
          sortString = "";
      }
      
//       document.getElementById("livesearch").innerHTML= suggestArr[0]["term"];
      for(i in suggestArr){
          document.getElementById("livesearch").innerHTML+="<a href='sample.php?q="+suggestArr[i]["term"]
              +"&sort="+sortString+"&limit="+document.getElementById("limitno").value+"'><div>"
              +suggestArr[i]["term"]+"</div></a>";
      } 
      document.getElementById("livesearch").style.border="1px solid #A5ACB2";
    }
  }
    
  xmlhttp.open("POST","sample.php",true);
  xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");  
  xmlhttp.send("kw="+str);
//   xmlhttp.send();
}
 </script>
 </head>
 <body>
 <h1>Search Engine Homework</h1>
 <form accept-charset="utf-8" method="get">
<table>
<tr>
<td>Search:</td>
<td><input type="text" name="q" onkeyup="showResult(this.value)" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>">
    </td>
</tr>
<tr>
<td>
</td>
<td>
   <div id="livesearch"></div>
</td>
</tr>
<tr>
<td>Limit:</td>
<td><input id="limitno" type="number" name="limit" min = "1" value="<?php echo htmlspecialchars($limit, ENT_QUOTES, 'utf-8'); ?>"></td>
</tr>
<tr>
<td>
<div style="float:left">
  <input id="sort1" type="radio" name="sort" value="" <?php if($additionalParameters['sort'] == '' ){echo "checked";} ?> >Lucene<br>
  <input id="sort2" type="radio" name="sort" value="pageRankFile" <?php if($additionalParameters['sort'] != '' ){echo "checked";} ?>>Page Rank<br>
</div>
</td>
</tr>
</table>
<input type="submit"/>
</form>
<?php
      $suggestion = SpellCorrector::correct(htmlspecialchars($query, ENT_QUOTES, 'utf-8'));
      if($suggestion != strtolower(htmlspecialchars($query, ENT_QUOTES, 'utf-8')))
      {
          echo "<div> Show results for: <a href='sample.php?q=".$suggestion."&limit=".$limit."&sort=".$additionalParameters['sort']."'>".$suggestion."</a></div>";
      }
          ?>
<?php
// display results
if ($results)
{
 $total = (int) $results->response->numFound;
 $start = min(1, $total);
 $end = min($limit, $total);
?>
<div>Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div>
<ol>
<?php
 // iterate result documents
 foreach ($results->response->docs as $doc)
 {
?>
 <li>
 <table style="text-align: left">
   
 <tr>
    <th><a href=<?php echo $doc->og_url ?> ><?php echo htmlspecialchars($doc->title, ENT_NOQUOTES, 'utf-8');?></a></th>
 <tr>
 <tr>
    <th><a href=<?php echo $doc->og_url ?> ><?php echo htmlspecialchars($doc->og_url, ENT_NOQUOTES, 'utf-8');?></a></th>
 <tr>
 <tr>
    <td><?php echo htmlspecialchars($doc->id, ENT_NOQUOTES, 'utf-8');?></td>
 <tr>    
 <tr>
    <td><?php
        $html = file_get_html($doc->og_url);
        $data = $html->find('p');
        $term = htmlspecialchars($query, ENT_QUOTES, 'utf-8');
        //$term = $query;
     
         foreach($data as $para){
             if (strpos(strtolower($para -> plaintext), $term) == true){
                 
                 $result = $para -> plaintext.'<br>';
                 
                 if(strlen($para -> plaintext) <= 160){
                 }else{
                    $title = $para -> plaintext;
                    $result = substr( $title, 0, strpos($title, ' ', 160) );
                    $start = 0;
                    while(strpos($result, $term) != true){
                        $start+=10;
                        $result = substr( $title, $start, strpos($title, ' ', 160) );
                    }

                 }
                 $final = str_replace($term,"<b>".$term."</b>", $result);
                 echo $final.'<br>';

                 break;
             }
         }
     ?></td>
 <tr>
 </table>
 </li>
<?php
 }
?>
 </ol>
<?php
}
?>
 </body>
</html>
<?php }?>
