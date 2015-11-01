<?php
/**
* Telegram Bot example for Public Transport of Bari (Italy)
* @author @Piersoft
Funzionamento
- invio location
- invio fermata più vicina come risposta


*/

include("Telegram.php");

class mainloop{
const MAX_LENGTH = 4096;
function start($telegram,$update)
{

	date_default_timezone_set('Europe/Rome');
	$today = date("Y-m-d H:i:s");
	$db = new PDO(DB_NAME);

	$text = $update["message"] ["text"];
	$chat_id = $update["message"] ["chat"]["id"];
	$user_id=$update["message"]["from"]["id"];
	$location=$update["message"]["location"];
	$reply_to_msg=$update["message"]["reply_to_message"];

	$this->shell($telegram, $db,$text,$chat_id,$user_id,$location,$reply_to_msg);
	$db = NULL;

}

//gestisce l'interfaccia utente
 function shell($telegram,$db,$text,$chat_id,$user_id,$location,$reply_to_msg)
{
	date_default_timezone_set('Europe/Rome');
	$today = date("Y-m-d H:i:s");

	if ($text == "/start") {
		$reply = "Benvenuto. Invia la tua posizione cliccando sulla graffetta (📎) e ti indicherò le fermate AMTAB di Bari più vicine nel raggio di 200 metri e relative linee ed orari";
		$reply .= "\nI dati AMBTAB, in licenza opendata CC0 Universal, sono prelevabili realtime su http://bari.opendata.planetek.it/OrariBus/v2.1/";
		$reply .= "\nLe risposte avverranno ogni 60 secondi";
		$reply .= "\nProgetto sviluppato da @Piersoft. Si declina ogni responsabilità sulla veridicità dei dati.";

		$content = array('chat_id' => $chat_id, 'text' => $reply);
		$telegram->sendMessage($content);

		$forcehide=$telegram->buildKeyBoardHide(true);
		$content = array('chat_id' => $chat_id, 'text' => "", 'reply_markup' =>$forcehide, 'reply_to_message_id' =>$bot_request_message_id);
		$bot_request_message=$telegram->sendMessage($content);
		$log=$today. ";new chat started;" .$chat_id. "\n";
	}elseif ($text == "/linee" || $text =="linee") {
	//$response=$telegram->getData();
		$temp_c1="";
	//	$bot_request_message_id=$response["message"]["message_id"];
		$json_string = file_get_contents("http://bari.opendata.planetek.it/OrariBus/v2.1/OpenDataService.svc/REST/rete/Linee");
		$parsed_json = json_decode($json_string);
		$count =0;
		foreach($parsed_json as $data=>$csv1){
			 $count = $count+1;
		}
	//	echo 	"Linee: ".$count." ".$parsed_json[0]->{'IdLinea'};

		for ($i=0;$i<$count;$i++){

		$temp_c1 =$parsed_json[$i]->{'IdLinea'}." - ".$parsed_json[$i]->{'DescrizioneLinea'};
		//$content = array('chat_id' => $chat_id, 'text' => $chunk, 'reply_to_message_id' =>$bot_request_message_id,'disable_web_page_preview'=>true);
		//$telegram->sendMessage($content);

		$chunks = str_split($temp_c1, self::MAX_LENGTH);
	  foreach($chunks as $chunk) {
	 	// $forcehide=$telegram->buildForceReply(true);
	 		 //chiedo cosa sta accadendo nel luogo
	 		 $content = array('chat_id' => $chat_id, 'text' => $chunk,'disable_web_page_preview'=>true);
	 		 $telegram->sendMessage($content);

	  }
	}


}elseif ($text == "01") {
//	$response=$telegram->getData();
	$temp_c1="";
	$h = "1";// Hour for time zone goes here e.g. +7 or -4, just remove the + or -
	$hm = $h * 60;
	$ms = $hm * 60;
//	$bot_request_message_id=$response["message"]["message_id"];
	$json_string = file_get_contents("http://bari.opendata.planetek.it/OrariBus/v2.1/OpenDataService.svc/REST/ServizioGiornaliero/".$text."/");
	$parsed_json = json_decode($json_string);
	$count =0;

	foreach($parsed_json as $data=>$csv1){
		 $count = $count+1;
	}

		$time =$parsed_json[0]->{'Orario'}; //registro nel DB anche il tempo unix
		$time =str_replace("/Date(","",$time);
			if (strpos($time,'0100') == false) {
				$h = "2";
			}
		$time =str_replace("000+0200)/","",$time);
	$time =str_replace("000+0100)/","",$time);
		$time =str_replace(" ","",$time);
		$time =str_replace("\n","",$time);
		$timef=floatval($time);
		$timeff = time();
		$timec =gmdate('H:i:s', $timef+$ms);

		$temp_c1 .="\narrivo: ".$timec;


	$fermataid =$parsed_json[0]->{'IdFermata'};
	echo $fermataid;
	$json_stringf = file_get_contents("http://bari.opendata.planetek.it/OrariBus/v2.1/OpenDataService.svc/REST/rete/Fermate/".$fermataid);
	$parsed_jsonf = json_decode($json_stringf);

  $fermata=$parsed_jsonf[0]->{'DescrizioneFermata'};

	echo "\nLinea: ".$text."\nFermata".$fermata."\norario: ".$timec;

	for ($i=0;$i<$count;$i++){


	$time =$parsed_json[$i]->{'Orario'}; //registro nel DB anche il tempo unix
	$time =str_replace("/Date(","",$time);
	$time =str_replace("000+0200)/","",$time);
	$time =str_replace("000+0100)/","",$time);
	if (strpos($time,'0100') == false) {
		$h = "2";
	}
	$time =str_replace(" ","",$time);
	$time =str_replace("\n","",$time);
	$timef=floatval($time);
	$timeff = time();
	$timec =gmdate('H:i:s', $timef+$ms);

	$temp_c1 .="\narrivo: ".$timec;

	//echo $temp_c1;
}
}
		//gestione segnalazioni georiferite
		elseif($location!=null)
		{

			$this->location_manager($db,$telegram,$user_id,$chat_id,$location);
			exit;

		}else{

$forcehide=$telegram->buildKeyBoardHide(true);
$content = array('chat_id' => $chat_id, 'text' => "Comando errato.\nInvia la tua posizione cliccando sulla graffetta (📎) in basso e, se vuoi, puoi cliccare due volte sulla mappa e spostare il Pin Rosso in un luogo di cui vuoi conoscere le fermate più vicine. Risposta entro 60 secondi.", 'reply_markup' =>$forcehide);
$telegram->sendMessage($content);
	//		$this->create_keyboard($telegram,$chat_id);
			exit;

		}

		file_put_contents(LOG_FILE, $log, FILE_APPEND | LOCK_EX);

}


// Crea la tastiera
 function create_keyboard($telegram, $chat_id)
	{
		$forcehide=$telegram->buildKeyBoardHide(true);
		$content = array('chat_id' => $chat_id, 'text' => "Invia la tua posizione cliccando sulla graffetta (📎) in basso e, se vuoi, puoi cliccare due volte sulla mappa e spostare il Pin Rosso in un luogo di cui vuoi conoscere le fermate più vicine. Risposta entro 60 secondi.", 'reply_markup' =>$forcehide);
		$telegram->sendMessage($content);

	}



function location_manager($db,$telegram,$user_id,$chat_id,$location)
	{

			$lng=$location["longitude"];
			$lat=$location["latitude"];
      $r=100;
			//rispondo
			$response=$telegram->getData();

			$bot_request_message_id=$response["message"]["message_id"];
			$time=$response["message"]["date"]; //registro nel DB anche il tempo unix

			$h = "1";// Hour for time zone goes here e.g. +7 or -4, just remove the + or -
			$hm = $h * 60;
			$ms = $hm * 60;
			$timec=gmdate("Y-m-d\TH:i:s\Z", $time+($ms));
			$timec=str_replace("T"," ",$timec);
			$timec=str_replace("Z"," ",$timec);

			$json_string = file_get_contents("http://bari.opendata.planetek.it/OrariBus/v2.1/OpenDataService.svc/REST/rete/FermateVicine/".$lat."/".$lng."/200");
			$parsed_json = json_decode($json_string);
			$count =0;
			$countl = [];
			foreach($parsed_json as $data=>$csv1){
		     $count = $count+1;
			}
			if ($count ==0){
				$content = array('chat_id' => $chat_id, 'text' => 'Nessuna fermata trovata', 'reply_to_message_id' =>$bot_request_message_id);
				$telegram->sendMessage($content);
					$this->create_keyboard($telegram,$chat_id);
				exit;
			}
	//    echo "Fermate più vicine rispetto a ".$lat."/".$lng." in raggio di ".$r." metri con relative linee urbane ed orari arrivi\n";

		$IdFermata="";
		$temp_c1="";
		$temp_l1="";
		$option=array();

	for ($i=0;$i<$count;$i++){
		foreach($parsed_json[$i]->{'ListaLinee'} as $data=>$csv1){
			 $countl[$i] = $countl[$i]+1;

			}

			echo "countl: ".$countl[$i];
		//echo $countl;
			$temp_c1 .="Fermata: ".$parsed_json[$i]->{'DescrizioneFermata'};
			//."\nId Fermata: ".$parsed_json[$i]->{'IdFermata'};
			$longUrl="http://www.openstreetmap.org/?mlat=".$parsed_json[$i]->{'PosizioneFermata'}->{'Latitudine'}."&mlon=".$parsed_json[$i]->{'PosizioneFermata'}->{'Longitudine'}."#map=19/".$parsed_json[$i]->{'PosizioneFermata'}->{'Latitudine'}."/".$parsed_json[$i]->{'PosizioneFermata'}->{'Longitudine'}."/".$_POST['qrname'];

		//	$longUrl= "http://umap.openstreetmap.fr/it/map/segnalazioni-con-opendataleccebot-x-interni_54105#19/".$row[0]['lat']."/".$row[0]['lng']."/".$_POST['qrname'];
			$apiKey = API;

			$postData = array('longUrl' => $longUrl, 'key' => $apiKey);
			$jsonData = json_encode($postData);

			$curlObj = curl_init();

			curl_setopt($curlObj, CURLOPT_URL, 'https://www.googleapis.com/urlshortener/v1/url?key='.$apiKey);
			curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curlObj, CURLOPT_HEADER, 0);
			curl_setopt($curlObj, CURLOPT_HTTPHEADER, array('Content-type:application/json'));
			curl_setopt($curlObj, CURLOPT_POST, 1);
			curl_setopt($curlObj, CURLOPT_POSTFIELDS, $jsonData);

			$response = curl_exec($curlObj);

			// Change the response json string to object
			$json = json_decode($response);

			curl_close($curlObj);
		//  $reply="Puoi visualizzarlo su :\n".$json->id;
			$shortLink = get_object_vars($json);
		//return $json->id;

			$temp_c1 .="\nVisualizzala su :\n".$shortLink['id'];

		if ($countl[$i]!=0){
			$temp_c1 .="\nLinee servite :";
		}	else $temp_c1 .="\n";


			for ($l=0;$l<$countl[$i];$l++)
				{


			$temp_l1 =$parsed_json[$i]->{'ListaLinee'}[$l]->{'IdLinea'}." ".$parsed_json[$i]->{'ListaLinee'}[$l]->{'Direzione'};
			array_push($option, $temp_l1);

			$temp_c1 .=" ".$parsed_json[$i]->{'ListaLinee'}[$l]->{'IdLinea'}.".".$parsed_json[$i]->{'ListaLinee'}[$l]->{'Direzione'};
				 }
			$temp_c1 .="";


			// inzio sotto routine per orari per linee afferenti alla fermata:

			$IdFermata=$parsed_json[$i]->{'IdFermata'};
	    //	echo $IdFermata;
			$json_string1 = file_get_contents("http://bari.opendata.planetek.it/OrariBus/v2.1/OpenDataService.svc/REST/OrariPalina/".$IdFermata."/");
			$parsed_json1 = json_decode($json_string1);
		//  var_dump($parsed_json1);
		//  var_dump($parsed_json1->{'PrevisioniLinee'}[0]);
			$countf = 0 ;
			foreach($parsed_json1->{'PrevisioniLinee'} as $data123=>$csv113){
				 $countf = $countf+1;
			}


	//    echo $countf;
			$h = "1";// Hour for time zone goes here e.g. +7 or -4, just remove the + or -
			$hm = $h * 60;
			$ms = $hm * 60;
			date_default_timezone_set('UTC');
			for ($f=0;$f<$countf;$f++){
			//	$corsa = get_object_vars($parsed_json1);
			//return $json->id;

		//		$check =$shortLink['IdLinea'];
		//		if(!$parsed_json1->{'IdCorsa'}[$f]) alert();

			$check=$parsed_json1->{'PrevisioniLinee'}[$f]->{'IdLinea'};
			if (empty($check)){
				$content = array('chat_id' => $chat_id, 'text' => 'Non ci sono linee', 'reply_to_message_id' =>$bot_request_message_id,'disable_web_page_preview'=>true);
				$telegram->sendMessage($content);

       }
				$time =$parsed_json1->{'PrevisioniLinee'}[$f]->{'OrarioArrivo'}; //registro nel DB anche il tempo unix
		//    echo "\ntimestamp:".$time."senza pulizia dati";
				$time =str_replace("/Date(","",$time);
				$time =str_replace("000+0200)/","",$time);
					$time =str_replace("000+0100)/","",$time);
					if (strpos($time,'0100') == false) {
						$h = "2";
					}
		//    $time =str_replace("T"," ",$time);
		//    $time =str_replace("Z"," ",$time);
				$time =str_replace(" ","",$time);
				$time =str_replace("\n","",$time);
				$timef=floatval($time);
				$timeff = time();
				$timec =gmdate('H:i:s', $timef+$ms);

				$temp_c1 .="\nLinea: ".$parsed_json1->{'PrevisioniLinee'}[$f]->{'IdLinea'}." ".$parsed_json1->{'PrevisioniLinee'}[$f]->{'DirezioneLinea'}." arrivo: ".$timec."";
			 }
				$temp_c1 .="\n\n";


	}

$longUrl="http://www.piersoft.it/baritrasportibot/locator.php?lat=".$lat."&lon=".$lng."&r=200";
$apiKey = API;

$postData = array('longUrl' => $longUrl, 'key' => $apiKey);
$jsonData = json_encode($postData);

$curlObj = curl_init();

curl_setopt($curlObj, CURLOPT_URL, 'https://www.googleapis.com/urlshortener/v1/url?key='.$apiKey);
curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($curlObj, CURLOPT_HEADER, 0);
curl_setopt($curlObj, CURLOPT_HTTPHEADER, array('Content-type:application/json'));
curl_setopt($curlObj, CURLOPT_POST, 1);
curl_setopt($curlObj, CURLOPT_POSTFIELDS, $jsonData);

$response = curl_exec($curlObj);

// Change the response json string to object
$json = json_decode($response);

curl_close($curlObj);
//  $reply="Puoi visualizzarlo su :\n".$json->id;
$shortLink = get_object_vars($json);
//return $json->id;

$temp_c1 .="\nVisualizza tutte le fermate a te vicine su :\n".$shortLink['id'];



 $chunks = str_split($temp_c1, self::MAX_LENGTH);
 foreach($chunks as $chunk) {
	// $forcehide=$telegram->buildForceReply(true);
		 //chiedo cosa sta accadendo nel luogo
		 $content = array('chat_id' => $chat_id, 'text' => $chunk, 'reply_to_message_id' =>$bot_request_message_id,'disable_web_page_preview'=>true);
		 $telegram->sendMessage($content);

 }
 //$telegram->sendMessage($content);
	echo $temp_l1;

//if ($temp_l1 ==="") {
//	$content = array('chat_id' => $chat_id, 'text' => "Nessuna fermata nei paraggi", 'reply_to_message_id' =>$bot_request_message_id);
//		$telegram->sendMessage($content);

//}
	$today = date("Y-m-d H:i:s");

	$log=$today. ";fermatebari sent;" .$chat_id. "\n";
	$this->create_keyboard($telegram,$chat_id);
	exit;

	}


}

?>
