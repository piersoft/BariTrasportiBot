<?php
$lat=$_GET["lat"];
$lon=$_GET["lon"];
$r=$_GET["r"];

function geoJson ($locales)
    {
        $original_data = json_decode($locales, true);
        $features = array();

        foreach($original_data as $key => $value) {
            $features[] = array(
                    'type' => 'Feature',
                    'geometry' => array('type' => 'Point', 'coordinates' => array((float)$value['PosizioneFermata']['Longitudine'],(float)$value['PosizioneFermata']['Latitudine'])),
                    'properties' => array('name' => $value['DescrizioneFermata'], 'id' => $value['IdFermata']),
                    );
            };

        $allfeatures = array('type' => 'FeatureCollection', 'features' => $features);
        return json_encode($allfeatures, JSON_PRETTY_PRINT);

    }
$url = 'http://bari.opendata.planetek.it/OrariBus/v2.1/OpenDataService.svc/REST/rete/FermateVicine/'.$lat.'/'.$lon.'/'.$r;
$file = "mappa.json";

$src = fopen($url, 'r');
$dest = fopen($file, 'w');
stream_copy_to_stream($src, $dest);

$file1 = "mappaf.json";
$original_json_string = file_get_contents('mappa.json', true);
if($original_json_string=="[]")
{
  echo "<script type='text/javascript'>alert('Non ci sono fermate vicino alla tua posizione');</script>";

}
$dest1 = fopen($file1, 'w');

$geostring=geoJson($original_json_string);

fputs($dest1, $geostring);


//echo stream_copy_to_stream($src, $dest) . "";
//sleep(1);
//header("Location:http://www.apposta.biz/prove/mappacqualta.html");

?>

<!DOCTYPE html>
<html lang="it">
  <head>
  <title>Trasporti BARI Amtab</title>
  <link rel="stylesheet" href="http://necolas.github.io/normalize.css/2.1.3/normalize.css" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
  <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.7.5/leaflet.css" />
        <link rel="stylesheet" href="MarkerCluster.css" />
        <link rel="stylesheet" href="MarkerCluster.Default.css" />
        <meta property="og:image" content="http://www.piersoft.it/baritrasportibot/bus_.png"/>
  <script src="http://cdn.leafletjs.com/leaflet-0.7.5/leaflet.js"></script>
   <script src="leaflet.markercluster.js"></script>
<script type="text/javascript">

function microAjax(B,A){this.bindFunction=function(E,D){return function(){return E.apply(D,[D])}};this.stateChange=function(D){if(this.request.readyState==4 ){this.callbackFunction(this.request.responseText)}};this.getRequest=function(){if(window.ActiveXObject){return new ActiveXObject("Microsoft.XMLHTTP")}else { if(window.XMLHttpRequest){return new XMLHttpRequest()}}return false};this.postBody=(arguments[2]||"");this.callbackFunction=A;this.url=B;this.request=this.getRequest();if(this.request){var C=this.request;C.onreadystatechange=this.bindFunction(this.stateChange,this);if(this.postBody!==""){C.open("POST",B,true);C.setRequestHeader("X-Requested-With","XMLHttpRequest");C.setRequestHeader("Content-Type","application/x-www-form-urlencoded; charset=UTF-8");C.setRequestHeader("Connection","close")}else{C.open("GET",B,true)}C.send(this.postBody)}};

</script>
  <style>
  #mapdiv{
        position:fixed;
        top:0;
        right:0;
        left:0;
        bottom:0;
}
#infodiv{
background-color: rgba(255, 255, 255, 0.95);

font-family: Helvetica, Arial, Sans-Serif;
padding: 2px;


font-size: 10px;
bottom: 13px;
left:0px;


max-height: 50px;

position: fixed;

overflow-y: auto;
overflow-x: hidden;
}
#loader {
    position:absolute; top:0; bottom:0; width:100%;
    background:rgba(255, 255, 255, 1);
    transition:background 1s ease-out;
    -webkit-transition:background 1s ease-out;
}
#loader.done {
    background:rgba(255, 255, 255, 0);
}
#loader.hide {
    display:none;
}
#loader .message {
    position:absolute;
    left:50%;
    top:50%;
}
</style>
  </head>

<body>

  <div data-tap-disabled="true">

  <div id="mapdiv"></div>
<div id="infodiv" style="leaflet-popup-content-wrapper">
  <p><b>Fermate Bus BARI nelle vicinanze<br></b>
  Mappa con fermate, linee e orarie dei Bus AMTAB di Bari by @piersoft. Fonte dati Lic. CC0 <a href="http://bari.opendata.planetek.it/OrariBus/v2.1/">SEMINA</a></p>
</div>
<div id='loader'><span class='message'>loading</span></div>
</div>
  <script type="text/javascript">
		var lat=41.1181,
        lon=16.8695,
        zoom=14;



        var osm = new L.TileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {maxZoom: 20, attribution: 'Map Data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors'});
		var mapquest = new L.TileLayer('http://otile{s}.mqcdn.com/tiles/1.0.0/osm/{z}/{x}/{y}.png', {subdomains: '1234', maxZoom: 18, attribution: 'Map Data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors'});

        var map = new L.Map('mapdiv', {
                    editInOSMControl: true,
            editInOSMControlOptions: {
                position: "topright"
            },
            center: new L.LatLng(lat, lon),
            zoom: zoom,
            layers: [osm]
        });

        var baseMaps = {
    "Mapnik": osm,
    "Mapquest Open": mapquest
        };
        L.control.layers(baseMaps).addTo(map);
        var markeryou = L.marker([parseFloat('<?php printf($_GET['lat']); ?>'), parseFloat('<?php printf($_GET['lon']); ?>')]).addTo(map);
        markeryou.bindPopup("<b>Sei qui</b>");
       var ico=L.icon({iconUrl:'icobusstop.png', iconSize:[40,60],iconAnchor:[20,0]});
       var markers = L.markerClusterGroup({spiderfyOnMaxZoom: false, showCoverageOnHover: true,zoomToBoundsOnClick: true});

        function loadLayer(url)
        {
                var myLayer = L.geoJson(url,{
                        onEachFeature:function onEachFeature(feature, layer) {
                                if (feature.properties && feature.properties.id) {
                                }

                        },
                        pointToLayer: function (feature, latlng) {
                        var marker = new L.Marker(latlng, { icon: ico });

                        markers[feature.properties.id] = marker;
                        marker.bindPopup('<img src="http://www.piersoft.it/baritrasportibot/ajax-loader.gif">',{maxWidth:50, autoPan:true});

                      //  marker.on('click',showMarker());
                        return marker;
                        }
                });
                //.addTo(map);

                markers.addLayer(myLayer);
                map.addLayer(markers);
                markers.on('click',showMarker);
        }

microAjax('mappaf.json',function (res) {
var feat=JSON.parse(res);
loadLayer(feat);
  finishedLoading();
} );
function convertTimestamp(timestamp) {
  var d = new Date(timestamp * 1000),	// Convert the passed timestamp to milliseconds
		yyyy = d.getFullYear(),
		mm = ('0' + (d.getMonth() + 1)).slice(-2),	// Months are zero based. Add leading 0.
		dd = ('0' + d.getDate()).slice(-2),			// Add leading 0.
		hh = d.getHours(),
		h = hh,
		min = ('0' + d.getMinutes()).slice(-2),		// Add leading 0.
		ampm = 'AM',
		time;

	if (hh > 12) {
	//	h = hh - 12;
		ampm = 'PM';
	} else if (hh === 12) {
	//	h = 12;
		ampm = 'PM';
	} else if (hh == 0) {
		h = 12;
	}

	// ie: 2013-02-18, 8:35 AM
	time = h + ':' + min;

	return time;
}

 function showMarker(marker) {

   var jsonref=marker.layer.feature;

   microAjax('http://bari.opendata.planetek.it/OrariBus/v2.1/OpenDataService.svc/REST/OrariPalina/'+jsonref.properties.id+'/', function (res) {

   var feat=JSON.parse(res);
   var index;
   console.log(feat.length);
//  alert (feat.length);
var text;
var i = 0;


if(feat['PrevisioniLinee'].length != "undefined")
{
  if(feat['PrevisioniLinee'].length == "0")
  {
  text ="Non ci sono linee in arrivo nelle prossime ore";
  marker.layer.closePopup();
  marker.layer.bindPopup(text);
  marker.layer.openPopup();
  console.log("Feat lenght: "+feat['PrevisioniLinee'].length);
}else{

  text ="Prossimo arrivo: <b>";
console.log("Feat lenght: "+feat['PrevisioniLinee'].length);
for (i=0;i<feat['PrevisioniLinee'].length;i++){
    //   // when the tiles load, remove the screen
    var last=feat['PrevisioniLinee'][i];
  //  var text ="Linee servite: "+last['IdLinea']+"<br>";
    text +="<br />"+last['IdLinea']+" "+last['DirezioneLinea'];
    var orario =last['OrarioArrivo'];
    orario= orario.replace("/Date(","");
    orario=orario.replace("000+0200)/","");
    orario=orario.replace("000+0100)/","");
    var date=convertTimestamp(orario);
    //var date = new Date(orario*1000);
    //var iso = date.toISOString().match(/(\d{2}:\d{2}:\d{2})/);
    text+=" Arrivo: "+date;
    marker.layer.closePopup();
    marker.layer.bindPopup(text);
    marker.layer.openPopup();
  }
}
}

}
  );

}
function startLoading() {
    loader.className = '';
}

function finishedLoading() {
    // first, toggle the class 'done', which makes the loading screen
    // fade out
    loader.className = 'done';
    setTimeout(function() {
        // then, after a half-second, add the class 'hide', which hides
        // it completely and ensures that the user can interact with the
        // map again.
        loader.className = 'hide';
    }, 500);
}
</script>

</body>
</html>
