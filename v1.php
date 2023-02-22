<?php
if (!isset($_GET['clan']))
{
	die("Invalid clan, input the search clan in url!");
}
if (!isset($_GET['name']))
{
	die("Invalid name, input the search name in url!");
}

$ch = curl_init();
$api_url = "https://api.clashroyale.com/v1/clans?name=".urlencode($_GET['clan'])."&minMembers=30&minScore=30000";
$header = array(
	'Accept: application/json',   
	'Authorization: Bearer BEARER_TOKEN_FROM_CLASHROOYALE_OFFICIAL_API.'
);

curl_setopt($ch,    CURLOPT_HTTPHEADER, 	$header);
curl_setopt($ch,	CURLOPT_URL, 				$api_url);
curl_setopt($ch,	CURLOPT_RETURNTRANSFER, 	true);

$ch_data = curl_exec($ch);
curl_close($ch);
$response_data = json_decode($ch_data);
$UserDataArray = array();

foreach ($response_data->items as $clan){
	similar_text(strtolower($clan->name), strtolower($_GET['clan']), $percent);
	if ($percent > 90) {
		$ch = curl_init();
		//https://api.clashroyale.com/v1/clans/%23PU282JVL/members
		$api_url = "https://api.clashroyale.com/v1/clans/".urlencode($clan->tag)."/members";
		$header = array(
			'Accept: application/json',   
			'Authorization: Bearer BEARER_TOKEN_FROM_CLASHROOYALE_OFFICIAL_API.'
		);
		curl_setopt($ch,    CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch,	CURLOPT_URL, 				$api_url);
		curl_setopt($ch,	CURLOPT_RETURNTRANSFER, 	true);
		$clan_member_data = curl_exec($ch);	
		curl_close($ch);
		$f = json_decode($clan_member_data);		
		foreach ($f->items as $clan_members) {
			if ($clan_members->trophies > 3000) { 
				similar_text(strtolower($_GET['name']), strtolower($clan_members->name), $percent);
				if ($percent > 80) {		
					$ch = curl_init();
					//https://api.clashroyale.com/v1/players/%238G0LQQ92G
					$api_url = "https://api.clashroyale.com/v1/players/".urlencode($clan_members->tag);
					$header = array(
						'Accept: application/json',   
						'Authorization: Bearer BEARER_TOKEN_FROM_CLASHROOYALE_OFFICIAL_API.'
					);
					curl_setopt($ch,    CURLOPT_HTTPHEADER, $header);
					curl_setopt($ch,	CURLOPT_URL, 				$api_url);
					curl_setopt($ch,	CURLOPT_RETURNTRANSFER, 	true);
					$member_data = curl_exec($ch);	
					curl_close($ch);		
					$r = json_decode($member_data);
					array_push($UserDataArray, $clan_members->name);
					foreach ($r->currentDeck as $deck) {
						array_push($UserDataArray, $deck->name);							
					}
				}
			}
		}
	}
}
die(json_encode($UserDataArray));
//echo "Not found sorry!";
?>