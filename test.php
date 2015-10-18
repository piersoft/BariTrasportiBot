<?php

//file di test
$lat=$_GET["lat"];
$lon=$_GET["lon"];
$r=$_GET["raggio"];
include("getting.php");



$data=new getdata();

/*
print("---Previsioni---\r\n");
$test=$data->get_forecast("Lecce");
print($test);

$json_string = file_get_contents("http://api.wunderground.com/api/b3f95b06a21229ff/forecast/lang:IT/q/pws:IPUGLIAL7.json");
$parsed_json = json_decode($json_string);
//var_dump($parsed_json->forecast->txt_forecast->forecastday[0]->title);

$temp_c = $parsed_json->{'forecast'}->{'txt_forecast'}->{'forecastday'}[0]->{'title'};
$temp_c1 =", ";
$temp_c2 =$parsed_json->{'forecast'}->{'txt_forecast'}->{'forecastday'}[0]->{'fcttext_metric'};
echo $temp_c.$temp_c1.$temp_c2;
echo "<br>";

print("\n---Allerta meteo---\r\n");

$html = file_get_contents('http://ppc-lecce.3plab.it');
$html = iconv('ASCII', 'UTF-8//IGNORE', $html);
$html = sprintf('<html><head><title></title></head><body>%s</body></html>', $html);
$html = sprintf('<html><head><title></title></head><body>%s</body></html>', $html);
$html = sprintf('<html><head><title></title></head><body>%s</body></html>', $html);
$html =str_replace("Consulta il","<!--",$html);
$html =str_replace("Commenti disabilitati","-->",$html);
$html =str_replace("Estratto, per la Zona di Allerta del Comune, del Messaggio di Allerta","",$html);
$html =str_replace("larea","l&#39;area",$html);
$html =str_replace("Articoli meno recenti","",$html);

$html =str_replace("←","",$html);
$html =str_replace("Criticit","Criticit&#224;",$html);



$doc = new DOMDocument;
$doc->loadHTML($html);

$xpa    = new DOMXPath($doc);

//$divs   = $xpa->query('//div[@id="post-2713"]');
$divs   = $xpa->query('//div[starts-with(@id, "post")]');

$allerta="";

foreach($divs as $div) {
    $allerta .= "\n".$div->nodeValue;
}
echo $allerta;


print("---Aria di Oggi---\r\n");
$test=$data->get_aria("lecce");

print("---Traffico di Oggi---\r\n");
$test=$data->get_traffico("lecce");
*/
/*
echo "<br>";

print("---Eventi di Oggi---\r\n");
$test=$data->get_events();
echo $test;

echo "<br>";

print("---defribillatori---\r\n");
$test=$data->get_dae();
echo $test;

print("---scuola---\r\n");
$test=$data->get_orariscuole(primaria);
echo $test;
*/

//print("---get_parcheggi---\r\n");
//$lat=41.1181;
//$lon=16.86953;
$test=$data->get_fermateba($lat,$lon,$r);
echo $test;

/*
$test=$data->get_parcheggi();
echo $test;

*/

?>
