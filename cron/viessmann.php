#!/usr/bin/php
<?php
$client_id = 'my.client.id';
$client_secret = 'my.client.secret';
$isiwebuserid = 'my@user.name'; //to be modified
$isiwebpasswd = "my.password"; //to be modified
$authorizeURL = 'https://iam.viessmann.com/idp/v1/authorize';
$token_url = 'https://iam.viessmann.com/idp/v1/token';
$apiURLBase = 'https://api.viessmann-platform.io';
$general = '/general-management/installations?expanded=true&';

$debug = false;

if (isset($argc)) {
	$target = $argv[1];
}

foreach ($argv as $argNo => $arg)
{
	debug_msg ("$argNo : $argv[$argNo]\n", $debug);
	switch ($argv[$argNo]) {
		case "--debug":
			$debug = true;
			break;
		case "-user":
			$isiwebuserid = $argv[$argNo + 1];
			break;
		case "-passwd":
			$isiwebpasswd = $argv[$argNo + 1];
			break;
	}
}

if(filemtime('/opt/viessmann/data'.$target.'.tmp')+120>time()) {
	echo "Verwende vorhandene Datei\n";
	exit();
}


debug_msg("isiwebuserid = $isiwebuserid\n", $debug); 
debug_msg("isiwebpasswd = $isiwebpasswd\n", $debug); 

$callback_uri = "vicare://oauth-callback/everest"; 
$code = getCode(); 
debug_msg("code=$code\n", $debug); 

$access_token = getAccessToken($code); 
debug_msg("access token= $access_token\n", $debug); 

$resource = getResource($access_token, $apiURLBase . $general); 
debug_msg("resource: $resource\n", $debug); 
$eid = 1-(int)$target;
$installation = json_decode($resource, true)["entities"][$eid]["properties"]["id"];
$gw = json_decode($resource, true)["entities"][$eid]["entities"][0]["properties"]["serial"];
debug_msg("gw: $gw\n", $debug); 

$RequestList = array(
    "heating.boiler.serial.value"								=> "Kessel_Seriennummer",
    "heating.boiler.sensors.temperature.main.status" 						=> "Kessel_Status",
    "heating.boiler.sensors.temperature.main.value" 						=> "Kesseltemperatur",
    "heating.burner.active" 									=> "Brenner_aktiv",
    "heating.burner.automatic.status" 								=> "Brenner_Status",
    "heating.burner.automatic.errorCode"							=> "Brenner_Fehlercode",
    "heating.circuits.enabled" 									=> "Aktive_Heizkreise",
    "heating.circuits.0.active" 								=> "HK1-aktiv",
    "heating.circuits.0.circulation.schedule.active" 						=> "HK1-Zeitsteuerung_Zirkulation_aktiv",
    "heating.circuits.0.circulation.schedule.entries" 						=> "HK1-Zeitsteuerung_Zirkulation",
    "heating.circuits.0.frostprotection.status" 						=> "HK1-Frostschutz_Status",
    "heating.circuits.0.heating.curve.shift" 							=> "HK1-Heizkurve-Niveau",
    "heating.circuits.0.heating.curve.slope" 							=> "HK1-Heizkurve-Steigung",
    "heating.circuits.0.heating.schedule.active" 						=> "HK1-Zeitsteuerung_Heizung_aktiv",
    "heating.circuits.0.heating.schedule.entries" 						=> "HK1-Zeitsteuerung_Heizung",
    "heating.circuits.0.operating.modes.active.value" 						=> "HK1-Betriebsart",
    "heating.circuits.0.operating.modes.dhw.active" 						=> "HK1-WW_aktiv",
    "heating.circuits.0.operating.modes.dhwAndHeating.active"		 			=> "HK1-WW_und_Heizen_aktiv",
    "heating.circuits.0.operating.modes.forcedNormal.active" 					=> "HK1-Solltemperatur_erzwungen",
    "heating.circuits.0.operating.modes.forcedReduced.active" 					=> "HK1-Reduzierte_Temperatur_erzwungen",
    "heating.circuits.0.operating.modes.standby.active" 					=> "HK1-Standby_aktiv",
    "heating.circuits.0.operating.programs.active.value" 					=> "HK1-Programmstatus",
    "heating.circuits.0.operating.programs.comfort.active" 				=> "HK1-Solltemperatur_comfort_aktiv",
    "heating.circuits.0.operating.programs.comfort.temperature" 		=> "HK1-Solltemperatur_comfort",
    "heating.circuits.0.operating.programs.eco.active" 					=> "HK1-Solltemperatur_eco_aktiv",
    "heating.circuits.0.operating.programs.eco.temperature" 			=> "HK1-Solltemperatur_eco",
    "heating.circuits.0.operating.programs.external.active" 			=> "HK1-External_aktiv",
    "heating.circuits.0.operating.programs.external.temperature" 		=> "HK1-External_Temperatur",
    "heating.circuits.0.operating.programs.holiday.active" 				=> "HK1-Urlaub_aktiv",
    "heating.circuits.0.operating.programs.holiday.start" 				=> "HK1-Urlaub_Start",
    "heating.circuits.0.operating.programs.holiday.end" 					=> "HK1-Urlaub_Ende",
    "heating.circuits.0.operating.programs.normal.active" 				=> "HK1-Solltemperatur_aktiv",
    "heating.circuits.0.operating.programs.normal.temperature" 		=> "HK1-Solltemperatur_normal",
    "heating.circuits.0.operating.programs.reduced.active"				=> "HK1-Solltemperatur_reduziert_aktiv",
    "heating.circuits.0.operating.programs.reduced.temperature" 		=> "HK1-Solltemperatur_reduziert",
    "heating.circuits.0.operating.programs.standby.active" 				=> "HK1-Standby_aktiv",
    "heating.circuits.0.sensors.temperature.room.status" 				=> "HK1-Raum_Status",
    "heating.circuits.0.sensors.temperature.supply.status"				=> "HK1-Vorlauftemperatur_aktiv",
    "heating.circuits.0.sensors.temperature.supply.value" 				=> "HK1-Vorlauftemperatur",
    "heating.configuration.multiFamilyHouse.active" 						=> "Mehrfamilenhaus_aktiv",
    "heating.controller.serial.value" 								=> "Controller_Seriennummer",
    "heating.device.time.offset.value" 								=> "Device_Time_Offset",
    "heating.dhw.active" 									=> "WW-aktiv",
    "heating.dhw.oneTimeCharge.active" 								=> "WW-onTimeCharge_aktiv",
    "heating.dhw.sensors.temperature.hotWaterStorage.status" 			=> "WW-Temperatur_aktiv",
    "heating.dhw.sensors.temperature.hotWaterStorage.value" 			=> "WW-Isttemperatur",
    "heating.dhw.temperature.value" 								=> "WW-Solltemperatur",
    "heating.dhw.schedule.active" 								=> "WW-zeitgesteuert_aktiv",
    "heating.dhw.schedule.entries" 								=> "WW-Zeitplan",
    "heating.errors.active.entries" 								=> "Fehlereinträge_aktive",
    "heating.errors.history.entries" 								=> "Fehlereinträge_Historie",
    "heating.gas.consumption.dhw.day" 								=> "Gasverbrauch_WW/Tag",
    "heating.gas.consumption.dhw.week" 								=> "Gasverbrauch_WW/Woche",
    "heating.gas.consumption.dhw.month" 							=> "Gasverbrauch_WW/Monat",
    "heating.gas.consumption.dhw.year" 								=> "Gasverbrauch_WW/Jahr",
    "heating.gas.consumption.heating.day" 							=> "Gasverbrauch_Heizung/Tag",
    "heating.gas.consumption.heating.week" 							=> "Gasverbrauch_Heizung/Woche",
    "heating.gas.consumption.heating.month" 							=> "Gasverbrauch_Heizung/Monat",
    "heating.gas.consumption.heating.year" 							=> "Gasverbrauch_Heizung/Jahr",
    "heating.sensors.temperature.outside.status" 						=> "Aussen_Status",
    "heating.sensors.temperature.outside.statusWired" 					=> "Aussen_StatusWired",
    "heating.sensors.temperature.outside.statusWireless" 				=> "Aussen_StatusWireless",
    "heating.sensors.temperature.outside.value" 						=> "Aussentemperatur",
    "heating.service.timeBased.serviceDue" 							=> "Service_fällig",
    "heating.service.timeBased.serviceIntervalMonths" 					=> "Service_Intervall_Monate",
    "heating.service.timeBased.activeMonthSinceLastService" 			=> "Service_Monate_aktiv_seit_letzten_Service",
    "heating.service.timeBased.lastService" 							=> "Service_Letzter",
    "heating.service.burnerBased.serviceDue" 							=> "Service_fällig_brennerbasier",
    "heating.service.burnerBased.serviceIntervalBurnerHours" 			=> "Service_Intervall_Betriebsstunden",
    "heating.service.burnerBased.activeBurnerHoursSinceLastService" 	=> "Service_Betriebsstunden_seit_letzten",
    "heating.service.burnerBased.lastService" 							=> "Service_Letzter_brennerbasiert",
);


// Erstmal alle Daten komplett abfragen
$resource = getResource($access_token, "https://api.viessmann-platform.io/operational-data/installations/$installation/gateways/$gw/devices/0/features/");

$items = json_decode($resource, true)["entities"];

$r = "";
$data = array();

if(is_array($items)) { // Werte gültig? Dann Auswerten
	file_put_contents('/tmp/tmp.txt',print_r($items,1));
	foreach ($items as $ItemNo => $Item)
	{ 
		$FieldName = strval($items[$ItemNo]["class"][0]);
		//echo "$FieldName\n";
	
		if ( array_key_exists("properties", $items[$ItemNo] ) ) 
		{
			$Properties=$items[$ItemNo]["properties"];
		 	foreach ($Properties as $PropertyNo => $Property)
		 	{
				$Typ = strval($Properties["$PropertyNo"]["type"]);
				switch($Typ) {
					case "string":
					case "number":
						$Wert = strval($Properties["$PropertyNo"]["value"]);
						break;
					case "boolean":
						if ($Properties["$PropertyNo"]["value"] != "")
							{$Wert = "1";} 
						else 
							{$Wert = "0";}
						break;
					case "array":
						$Wert = implode(",", $Properties["$PropertyNo"]["value"]);
						break;
					case "Schedule":
						$Entries = $Properties["$PropertyNo"]["value"];
						$Wert = "";
						foreach ($Entries as $EntryNo => $Entry)
						{
							$Wert = $Wert.$EntryNo.":";
							$Entries2 = $Entries["$EntryNo"][0];
							foreach ($Entries2 as $EntryNo2 => $Entry2)
							{
								$Wert = $Wert." ".$EntryNo2.":".$Entries2[$EntryNo2];
							}
							$Wert = $Wert.", ";
						}
						break;
					case "ErrorListChanges":	
						$Wert = "Wert ist ein ErrorListChanges";
						break;
			 	}	
			 	$Description = $RequestList["$FieldName.$PropertyNo"];
				debug_msg ("$FieldName.$PropertyNo = $Description = $Wert ($Typ)", $debug);
				$data[$FieldName.$PropertyNo] = $Wert;
				if($debug === false) {$r = "$r"."setreading vitoconnect100 $Description $Wert ;\n";}
			 } 
		} 
	}

	//print_r($data);
	$gas_tmp = explode(",",$data['heating.power.consumption.totalday']);
	file_put_contents('/opt/viessmann/data'.$target.'.tmp', 'Aussen '.round($data['heating.sensors.temperature.outsidevalue']).'°C, Warmwasser '.round($data['heating.dhw.sensors.temperature.hotWaterStoragevalue']).'°C|warmwasser_temp='.$data['heating.dhw.sensors.temperature.hotWaterStoragevalue'].' aussen_temp='.$data['heating.sensors.temperature.outsidevalue'].' heizkreis='.$data['heating.circuits.0.sensors.temperature.supplyvalue'].' gasverbrauch='.$gas_tmp[0]);
	echo "Datei neu geschrieben\n";

	debug_msg ("\n$r", $debug);
	//shell_exec('perl /opt/fhem/fhem.pl 7072 "'.$r.'"');
}
else {
	print_r($resource);
	echo "\nFehler: Keine Daten\n";
}


return(0);

function getCode() {
	 global $client_id, $authorizeURL, $callback_uri;
	 global $isiwebuserid, $isiwebpasswd;
	 $url = "$authorizeURL?client_id=$client_id&scope=openid&redirect_uri=$callback_uri&response_type=code";
	 $header = array("Content-Type: application/x-www-form-urlencoded");
	 $curloptions = array(
        CURLOPT_URL => $url,
        CURLOPT_HTTPHEADER => $header,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERPWD => "$isiwebuserid:$isiwebpasswd",
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_POST => true, 
	 );
    $curl = curl_init();
    curl_setopt_array($curl, $curloptions);
    $response = curl_exec($curl);
    curl_close($curl);
    $matches = array();
    $pattern = '/code=(.*)"/';
    preg_match_all($pattern, $response, $matches);
    return ($matches[1][0]);
}

function getAccessToken($authorization_code) {
    global $token_url, $client_id, $client_secret, $callback_uri;
    global $isiwebuserid, $isiwebpasswd;
    $header = array("Content-Type: application/x-www-form-urlencoded;charset=utf-8");
    $params = array( 
        "client_id" => $client_id,
        "client_secret" => $client_secret,
        "code" => $authorization_code,
        "redirect_uri" => $callback_uri,
        "grant_type" => "authorization_code");
        
    $curloptions = array(
    	CURLOPT_URL => $token_url, 
    	CURLOPT_HEADER => false, 
    	CURLOPT_HTTPHEADER => $header, 
    	CURLOPT_SSL_VERIFYPEER => false, 
    	CURLOPT_RETURNTRANSFER => true, 
    	CURLOPT_POST => true, 
    	CURLOPT_POSTFIELDS => rawurldecode(http_build_query($params)));
    	
    $curl = curl_init();
    curl_setopt_array($curl, $curloptions);
    $response = curl_exec($curl); 
    curl_getinfo($curl); 
    
     if ($response === false) {
     	 echo "Failed\n"; echo curl_error($curl);
    //} elseif (json_decode($response)->error) {
    //    echo "Error:\n"; echo $authorization_code; echo $response;
     }
    curl_close($curl);

    return json_decode($response)->access_token;
}

//    we can now use the access_token as much as we want to access protected resources
function getResource($access_token, $api) {
    //echo "ok\n";
    $header = array("Authorization: Bearer {$access_token}");
    //var_dump($header);
    $curl = curl_init(); 
    curl_setopt_array($curl, array( 
	    CURLOPT_URL => $api, 
   	 CURLOPT_HTTPHEADER => $header, 
   	 CURLOPT_SSL_VERIFYPEER => false, 
   	 CURLOPT_RETURNTRANSFER => true,
    ));
    
    $response = curl_exec($curl); 
    curl_close($curl);
    
    //return json_decode($response, true);
    return ($response);
}

function debug_msg($message, $debug) {
	if ($debug) {
		 echo "$message\n";
        }
}

?>
