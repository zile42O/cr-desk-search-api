<?php
	$starttime = microtime(true);
	if (!isset($_GET['name']))
		die("Invalid name, input the search name in url!");

	header('Content-type: application/json');
	$arr_items = array();
	include("db.class.php");
	$CR_DataBase = new CR_DataBase();
	global $CR_DataBase;

	$query = "SELECT DISTINCT `tag`,`name`,`trophies` FROM `players` WHERE `trophies` > 6000 AND `name` LIKE '%".$_GET['name']."%' LIMIT 10";

	$CR_DataBase->Query($query);
	$CR_DataBase->Execute();
	$Return = array(
		'Count' => $CR_DataBase->RowCount(),
		'Response' => $CR_DataBase->ResultSet()
	);
	foreach ($Return['Response'] as $k => $v) 
	{
		//echo "name=".$v['name'];
		$nameget = RemoveSpecialChar(remove_emoji(strtolower($v['name'])));
		$getname = urldecode(strtolower($_GET['name']));
		$percent = null;
		$found = closest_word($getname, $nameget, $percent);
		if (round($percent * 100, 2) >= 70) {
			if (!isset($_GET['trophies'])) {
				$ch = curl_init();
				$api_url = "https://api.clashroyale.com/v1/players/".urlencode($v['tag']);
				$header = array(
					'Accept: application/json',
					'Authorization: Bearer BEARER_TOKEN_FROM_CLASHROOYALE_OFFICIAL_API.'
				);
				curl_setopt($ch,    CURLOPT_HTTPHEADER, $header);
				curl_setopt($ch,	CURLOPT_URL, 				$api_url);
				curl_setopt($ch,	CURLOPT_RETURNTRANSFER, 	true);
				curl_setopt($ch, 	CURLOPT_IPRESOLVE, 	CURL_IPRESOLVE_V4);
				$member_data = curl_exec($ch);
				curl_close($ch);
				$r = json_decode($member_data);		
				array_push($arr_items, array("name" => $v['name'], "arena" => $r->arena->name, "best_trophies" => $r->bestTrophies, "challenge_maxwins" => $r->challengeMaxWins, "wins" => $r->wins, "exp_level" => $r->expLevel, "clan" => $r->clan->name, "tag" => $v['tag'], "trophies" => $r->trophies));
				$deck_arr = array();
				foreach ($r->currentDeck as $deck) {
					//array_push($UserDataArray, $deck->name);
					array_push($arr_items, array("card" => $deck->name));			
				}
			} else {
				$ch = curl_init();
				$api_url = "https://api.clashroyale.com/v1/players/".urlencode($v['tag']);
				$header = array(
					'Accept: application/json',
					'Authorization: Bearer BEARER_TOKEN_FROM_CLASHROOYALE_OFFICIAL_API.'
				);
				curl_setopt($ch,    CURLOPT_HTTPHEADER, $header);
				curl_setopt($ch,	CURLOPT_URL, 				$api_url);
				curl_setopt($ch,	CURLOPT_RETURNTRANSFER, 	true);
				curl_setopt($ch, 	CURLOPT_IPRESOLVE, 	CURL_IPRESOLVE_V4);
				$member_data = curl_exec($ch);
				curl_close($ch);
				$r = json_decode($member_data);
				if ($r->trophies == $_GET['trophies']) {
					array_push($arr_items, array("name" => $v['name'], "arena" => $r->arena->name, "best_trophies" => $r->bestTrophies, "challenge_maxwins" => $r->challengeMaxWins, "wins" => $r->wins, "exp_level" => $r->expLevel, "clan" => $r->clan->name, "tag" => $v['tag'], "trophies" => $r->trophies));
					$deck_arr = array();
					foreach ($r->currentDeck as $deck) {
						//array_push($UserDataArray, $deck->name);
						array_push($arr_items, array("card" => $deck->name));			
					}
					break;
				}
			}
			
			
		}
		
	}
	/*
	JSON VERSION
	$data = file_get_contents("results.json");
	$decoded = json_decode($data);
	foreach($decoded as $key => $players)
	{	
		if ($players->trophies >= ($_GET['trophies'] - 500) && isset($_GET['trophies']) || $players->trophies >= 6000 && !isset($_GET['trophies'])) {					
			$nameget = RemoveSpecialChar(remove_emoji(strtolower($players->name)));
			$getname = urldecode(strtolower($_GET['name']));
			$percent = null;
			$found = closest_word($getname, $nameget, $percent);
			if (round($percent * 100, 2) >= 90.0 && isset($_GET['trophies']) || round($percent * 100, 2) >= 80.0 && !isset($_GET['trophies'])) {
				$ch = curl_init();
				$api_url = "https://api.clashroyale.com/v1/players/".urlencode($players->tag);
				$header = array(
					'Accept: application/json',
					'Authorization: Bearer BEARER_TOKEN_FROM_CLASHROOYALE_OFFICIAL_API.'
				);
				curl_setopt($ch,    CURLOPT_HTTPHEADER, $header);
				curl_setopt($ch,	CURLOPT_URL, 				$api_url);
				curl_setopt($ch,	CURLOPT_RETURNTRANSFER, 	true);
				curl_setopt($ch, 	CURLOPT_IPRESOLVE, 	CURL_IPRESOLVE_V4);
				$member_data = curl_exec($ch);
				curl_close($ch);
				$r = json_decode($member_data);

				if ($r->trophies == $_GET['trophies'] && isset($_GET['trophies']) || $r->trophies >= $_GET['trophies'] && !isset($_GET['trophies'])) {
					array_push($arr_items, array("name" => $players->name, "arena" => $r->arena->name, "best_trophies" => $r->bestTrophies, "challenge_maxwins" => $r->challengeMaxWins, "wins" => $r->wins, "loses" => $r->loses, "exp_level" => $r->expLevel, "clan" => $r->clan->name, "tag" => $players->tag, "trophies" => $r->trophies));
					$deck_arr = array();
					foreach ($r->currentDeck as $deck) {
						//array_push($UserDataArray, $deck->name);
						array_push($arr_items, array("card" => $deck->name));			
					}
				}
			}
		}
	}*/


$endtime = microtime(true);
array_push($arr_items, array("time" => sprintf("loaded in %f seconds.", $endtime - $starttime)));	
die(json_encode($arr_items, JSON_PRETTY_PRINT));

function changeTxt($oText) {
	
	$oText = str_ireplace('[*br*]', '<br>', $oText);

	/* General */
	$oText = str_ireplace('&amp;#34;', '"', $oText);
	$oText = str_ireplace('&amp;#39;', "'", $oText);


	// Latin
	$oText = str_replace('À', 'A', $oText);
	$oText = str_replace('Á', 'A', $oText);
	$oText = str_replace('Â', 'A', $oText);
	$oText = str_replace('Ã', 'A', $oText);
	$oText = str_replace('Ä', 'A', $oText);
	$oText = str_replace('Å', 'A', $oText);
	$oText = str_replace('Æ', 'AE', $oText);
	$oText = str_replace('Ç', 'C', $oText);
	$oText = str_replace('È', 'E', $oText);
	$oText = str_replace('É', 'E', $oText);
	$oText = str_replace('Ê', 'E', $oText);
	$oText = str_replace('Ë', 'E', $oText);
	$oText = str_replace('Ì', 'I', $oText);
	$oText = str_replace('Í', 'I', $oText);
	$oText = str_replace('Î', 'I', $oText);
	$oText = str_replace('Ï', 'I', $oText);
	$oText = str_replace('Ð', 'D', $oText);
	$oText = str_replace('Ñ', 'N', $oText);
	$oText = str_replace('Ò', 'O', $oText);
	$oText = str_replace('Ó', 'O', $oText);
	$oText = str_replace('Ô', 'O', $oText);
	$oText = str_replace('Õ', 'O', $oText);
	$oText = str_replace('Ö', 'O', $oText);
	$oText = str_replace('Ő', 'O', $oText);
	$oText = str_replace('Ø', 'O', $oText);
	$oText = str_replace('Ù', 'U', $oText);
	$oText = str_replace('Ú', 'U', $oText);
	$oText = str_replace('Û', 'U', $oText);
	$oText = str_replace('Ü', 'U', $oText);
	$oText = str_replace('Ű', 'U', $oText);
	$oText = str_replace('Ý', 'Y', $oText);
	$oText = str_replace('Þ', 'TH', $oText);
	$oText = str_replace('ß', 'ss', $oText);
	$oText = str_replace('à', 'a', $oText);
	$oText = str_replace('á', 'a', $oText);
	$oText = str_replace('â', 'a', $oText);
	$oText = str_replace('ã', 'a', $oText);
	$oText = str_replace('ä', 'a', $oText);
	$oText = str_replace('å', 'a', $oText);
	$oText = str_replace('æ', 'ae', $oText);
	$oText = str_replace('ç', 'c', $oText);
	$oText = str_replace('è', 'e', $oText);
	$oText = str_replace('é', 'e', $oText);
	$oText = str_replace('ê', 'e', $oText);
	$oText = str_replace('ë', 'e', $oText);
	$oText = str_replace('ì', 'i', $oText);
	$oText = str_replace('í', 'i', $oText);
	$oText = str_replace('î', 'i', $oText);
	$oText = str_replace('ï', 'i', $oText);
	$oText = str_replace('ð', 'd', $oText);
	$oText = str_replace('ñ', 'n', $oText);
	$oText = str_replace('ò', 'o', $oText);
	$oText = str_replace('ó', 'o', $oText);
	$oText = str_replace('ô', 'o', $oText);
	$oText = str_replace('õ', 'o', $oText);
	$oText = str_replace('ö', 'o', $oText);
	$oText = str_replace('ő', 'o', $oText);
	$oText = str_replace('ø', 'o', $oText);
	$oText = str_replace('ù', 'u', $oText);
	$oText = str_replace('ú', 'u', $oText);
	$oText = str_replace('û', 'u', $oText);
	$oText = str_replace('ü', 'u', $oText);
	$oText = str_replace('ű', 'u', $oText);
	$oText = str_replace('ý', 'y', $oText);
	$oText = str_replace('þ', 'th', $oText);
	$oText = str_replace('ÿ', 'y', $oText);
	
	// Symbols
	$oText = str_replace('&copy;', '(c)', $oText);
	$oText = str_replace('©', '(c)', $oText);
	
	// Greek
	$oText = str_replace('Α', 'A', $oText);
	$oText = str_replace('Β', 'B', $oText);
	$oText = str_replace('Γ', 'G', $oText);
	$oText = str_replace('Δ', 'D', $oText);
	$oText = str_replace('Ε', 'E', $oText);
	$oText = str_replace('Ζ', 'Z', $oText);
	$oText = str_replace('Η', 'H', $oText);
	$oText = str_replace('Θ', '8', $oText);
	$oText = str_replace('Ι', 'I', $oText);
	$oText = str_replace('Κ', 'K', $oText);
	$oText = str_replace('Λ', 'L', $oText);
	$oText = str_replace('Μ', 'M', $oText);
	$oText = str_replace('Ν', 'N', $oText);
	$oText = str_replace('Ξ', '3', $oText);
	$oText = str_replace('Ο', 'O', $oText);
	$oText = str_replace('Π', 'P', $oText);
	$oText = str_replace('Ρ', 'R', $oText);
	$oText = str_replace('Σ', 'S', $oText);
	$oText = str_replace('Τ', 'T', $oText);
	$oText = str_replace('Υ', 'Y', $oText);
	$oText = str_replace('Φ', 'F', $oText);
	$oText = str_replace('Χ', 'X', $oText);
	$oText = str_replace('Ψ', 'PS', $oText);
	$oText = str_replace('Ω', 'W', $oText);
	$oText = str_replace('Ά', 'A', $oText);
	$oText = str_replace('Έ', 'E', $oText);
	$oText = str_replace('Ί', 'I', $oText);
	$oText = str_replace('Ό', 'O', $oText);
	$oText = str_replace('Ύ', 'Y', $oText);
	$oText = str_replace('Ή', 'H', $oText);
	$oText = str_replace('Ώ', 'W', $oText);
	$oText = str_replace('Ϊ', 'I', $oText);
	$oText = str_replace('Ϋ', 'Y', $oText);
	$oText = str_replace('α', 'a', $oText);
	$oText = str_replace('β', 'b', $oText);
	$oText = str_replace('γ', 'g', $oText);
	$oText = str_replace('δ', 'd', $oText);
	$oText = str_replace('ε', 'e', $oText);
	$oText = str_replace('ζ', 'z', $oText);
	$oText = str_replace('η', 'h', $oText);
	$oText = str_replace('θ', '8', $oText);
	$oText = str_replace('ι', 'i', $oText);
	$oText = str_replace('κ', 'k', $oText);
	$oText = str_replace('λ', 'l', $oText);
	$oText = str_replace('μ', 'm', $oText);
	$oText = str_replace('ν', 'n', $oText);
	$oText = str_replace('ξ', '3', $oText);
	$oText = str_replace('ο', 'o', $oText);
	$oText = str_replace('π', 'p', $oText);
	$oText = str_replace('ρ', 'r', $oText);
	$oText = str_replace('σ', 's', $oText);
	$oText = str_replace('τ', 't', $oText);
	$oText = str_replace('υ', 'y', $oText);
	$oText = str_replace('φ', 'f', $oText);
	$oText = str_replace('χ', 'x', $oText);
	$oText = str_replace('ψ', 'ps', $oText);
	$oText = str_replace('ω', 'w', $oText);
	$oText = str_replace('ά', 'a', $oText);
	$oText = str_replace('έ', 'e', $oText);
	$oText = str_replace('ί', 'i', $oText);
	$oText = str_replace('ό', 'o', $oText);
	$oText = str_replace('ύ', 'y', $oText);
	$oText = str_replace('ή', 'h', $oText);
	$oText = str_replace('ώ', 'w', $oText);
	$oText = str_replace('ς', 's', $oText);
	$oText = str_replace('ϊ', 'i', $oText);
	$oText = str_replace('ΰ', 'y', $oText);
	$oText = str_replace('ϋ', 'y', $oText);
	$oText = str_replace('ΐ', 'i', $oText);
	
	// Turkish
	$oText = str_replace('Ş', 'S', $oText);
	$oText = str_replace('İ', 'I', $oText);
	$oText = str_replace('Ç', 'C', $oText);
	$oText = str_replace('Ü', 'U', $oText);
	$oText = str_replace('Ö', 'O', $oText);
	$oText = str_replace('Ğ', 'G', $oText);
	$oText = str_replace('ş', 's', $oText);
	$oText = str_replace('ı', 'i', $oText);
	$oText = str_replace('ç', 'c', $oText);
	$oText = str_replace('ü', 'u', $oText);
	$oText = str_replace('ö', 'o', $oText);
	$oText = str_replace('ğ', 'g', $oText);
	
	// Russian
	$oText = str_replace('А', 'A', $oText);
	$oText = str_replace('Б', 'B', $oText);
	$oText = str_replace('В', 'V', $oText);
	$oText = str_replace('Г', 'G', $oText);
	$oText = str_replace('Д', 'D', $oText);
	$oText = str_replace('Е', 'E', $oText);
	$oText = str_replace('Ё', 'Yo', $oText);
	$oText = str_replace('Ж', 'Zh', $oText);
	$oText = str_replace('З', 'Z', $oText);
	$oText = str_replace('И', 'I', $oText);
	$oText = str_replace('Й', 'J', $oText);
	$oText = str_replace('К', 'K', $oText);
	$oText = str_replace('Л', 'L', $oText);
	$oText = str_replace('М', 'M', $oText);
	$oText = str_replace('Н', 'N', $oText);
	$oText = str_replace('О', 'O', $oText);
	$oText = str_replace('П', 'P', $oText);
	$oText = str_replace('Р', 'R', $oText);
	$oText = str_replace('С', 'S', $oText);
	$oText = str_replace('Т', 'T', $oText);
	$oText = str_replace('У', 'U', $oText);
	$oText = str_replace('Ф', 'F', $oText);
	$oText = str_replace('Х', 'H', $oText);
	$oText = str_replace('Ц', 'C', $oText);
	$oText = str_replace('Ч', 'Ch', $oText);
	$oText = str_replace('Ш', 'Sh', $oText);
	$oText = str_replace('Щ', 'Sh', $oText);
	$oText = str_replace('Ъ', '', $oText);
	$oText = str_replace('Ы', 'Y', $oText);
	$oText = str_replace('Ь', '', $oText);
	$oText = str_replace('Э', 'E', $oText);
	$oText = str_replace('Ю', 'Yu', $oText);
	$oText = str_replace('Я', 'Ya', $oText);
	$oText = str_replace('а', 'a', $oText);
	$oText = str_replace('б', 'b', $oText);
	$oText = str_replace('в', 'v', $oText);
	$oText = str_replace('г', 'g', $oText);
	$oText = str_replace('д', 'd', $oText);
	$oText = str_replace('е', 'e', $oText);
	$oText = str_replace('ё', 'yo', $oText);
	$oText = str_replace('ж', 'zh', $oText);
	$oText = str_replace('з', 'z', $oText);
	$oText = str_replace('и', 'i', $oText);
	$oText = str_replace('й', 'j', $oText);
	$oText = str_replace('к', 'k', $oText);
	$oText = str_replace('л', 'l', $oText);
	$oText = str_replace('м', 'm', $oText);
	$oText = str_replace('н', 'n', $oText);
	$oText = str_replace('о', 'o', $oText);
	$oText = str_replace('п', 'p', $oText);
	$oText = str_replace('р', 'r', $oText);
	$oText = str_replace('с', 's', $oText);
	$oText = str_replace('т', 't', $oText);
	$oText = str_replace('у', 'u', $oText);
	$oText = str_replace('ф', 'f', $oText);
	$oText = str_replace('х', 'h', $oText);
	$oText = str_replace('ц', 'c', $oText);
	$oText = str_replace('ч', 'ch', $oText);
	$oText = str_replace('ш', 'sh', $oText);
	$oText = str_replace('щ', 'sh', $oText);
	$oText = str_replace('ъ', '', $oText);
	$oText = str_replace('ы', 'y', $oText);
	$oText = str_replace('ь', '', $oText);
	$oText = str_replace('э', 'e', $oText);
	$oText = str_replace('ю', 'yu', $oText);
	$oText = str_replace('я', 'ya', $oText);
	
	// Ukrainian
	$oText = str_replace('Є', 'Ye', $oText);
	$oText = str_replace('І', 'I', $oText);
	$oText = str_replace('Ї', 'Yi', $oText);
	$oText = str_replace('Ґ', 'G', $oText);
	$oText = str_replace('є', 'ye', $oText);
	$oText = str_replace('і', 'i', $oText);
	$oText = str_replace('ї', 'yi', $oText);
	$oText = str_replace('ґ', 'g', $oText);
	
	// Czech
	$oText = str_replace('Č', 'C', $oText);
	$oText = str_replace('Ď', 'D', $oText);
	$oText = str_replace('Ě', 'E', $oText);
	$oText = str_replace('Ň', 'N', $oText);
	$oText = str_replace('Ř', 'R', $oText);
	$oText = str_replace('Š', 'S', $oText);
	$oText = str_replace('Ť', 'T', $oText);
	$oText = str_replace('Ů', 'U', $oText);
	$oText = str_replace('Ž', 'Z', $oText);
	$oText = str_replace('č', 'c', $oText);
	$oText = str_replace('ď', 'd', $oText);
	$oText = str_replace('ě', 'e', $oText);
	$oText = str_replace('ň', 'n', $oText);
	$oText = str_replace('ř', 'r', $oText);
	$oText = str_replace('š', 's', $oText);
	$oText = str_replace('ť', 't', $oText);
	$oText = str_replace('ů', 'u', $oText);
	$oText = str_replace('ž', 'z', $oText);
	
	// Polish
	$oText = str_replace('Ą', 'A', $oText);
	$oText = str_replace('Ć', 'C', $oText);
	$oText = str_replace('Ę', 'e', $oText);
	$oText = str_replace('Ł', 'L', $oText);
	$oText = str_replace('Ń', 'N', $oText);
	$oText = str_replace('Ó', 'o', $oText);
	$oText = str_replace('Ś', 'S', $oText);
	$oText = str_replace('Ź', 'Z', $oText);
	$oText = str_replace('Ż', 'Z', $oText);
	$oText = str_replace('ą', 'a', $oText);
	$oText = str_replace('ć', 'c', $oText);
	$oText = str_replace('ę', 'e', $oText);
	$oText = str_replace('ł', 'l', $oText);
	$oText = str_replace('ń', 'n', $oText);
	$oText = str_replace('ó', 'o', $oText);
	$oText = str_replace('ś', 's', $oText);
	$oText = str_replace('ź', 'z', $oText);
	$oText = str_replace('ż', 'z', $oText);
	
	// Latvian
	$oText = str_replace('Ā', 'A', $oText);
	$oText = str_replace('Č', 'C', $oText);
	$oText = str_replace('Ē', 'E', $oText);
	$oText = str_replace('Ģ', 'G', $oText);
	$oText = str_replace('Ī', 'i', $oText);
	$oText = str_replace('Ķ', 'k', $oText);
	$oText = str_replace('Ļ', 'L', $oText);
	$oText = str_replace('Ņ', 'N', $oText);
	$oText = str_replace('Š', 'S', $oText);
	$oText = str_replace('Ū', 'u', $oText);
	$oText = str_replace('Ž', 'Z', $oText);
	$oText = str_replace('ā', 'a', $oText);
	$oText = str_replace('č', 'c', $oText);
	$oText = str_replace('ē', 'e', $oText);
	$oText = str_replace('ģ', 'g', $oText);
	$oText = str_replace('ī', 'i', $oText);
	$oText = str_replace('ķ', 'k', $oText);
	$oText = str_replace('ļ', 'l', $oText);
	$oText = str_replace('ņ', 'n', $oText);
	$oText = str_replace('š', 's', $oText);
	$oText = str_replace('ū', 'u', $oText);
	$oText = str_replace('ž', 'z', $oText);
	$oText = htmlspecialchars_decode($oText);
	return $oText; 
}
function RemoveSpecialChar($str){
  
	// Using preg_replace() function 
	// to replace the word 
	$res = preg_replace('/[^a-zA-Z0-9_ -]/s',' ',$str);

	// Returning the result 
	return $res;
}
function remove_emoji($text){
    return preg_replace('/\x{1F3F4}\x{E0067}\x{E0062}(?:\x{E0077}\x{E006C}\x{E0073}|\x{E0073}\x{E0063}\x{E0074}|\x{E0065}\x{E006E}\x{E0067})\x{E007F}|(?:\x{1F9D1}\x{1F3FF}\x{200D}\x{2764}(?:\x{FE0F}\x{200D}(?:\x{1F48B}\x{200D})?|\x{200D}(?:\x{1F48B}\x{200D})?)\x{1F9D1}|\x{1F469}\x{1F3FF}\x{200D}\x{1F91D}\x{200D}[\x{1F468}\x{1F469}]|\x{1FAF1}\x{1F3FF}\x{200D}\x{1FAF2})[\x{1F3FB}-\x{1F3FE}]|(?:\x{1F9D1}\x{1F3FE}\x{200D}\x{2764}(?:\x{FE0F}\x{200D}(?:\x{1F48B}\x{200D})?|\x{200D}(?:\x{1F48B}\x{200D})?)\x{1F9D1}|\x{1F469}\x{1F3FE}\x{200D}\x{1F91D}\x{200D}[\x{1F468}\x{1F469}]|\x{1FAF1}\x{1F3FE}\x{200D}\x{1FAF2})[\x{1F3FB}-\x{1F3FD}\x{1F3FF}]|(?:\x{1F9D1}\x{1F3FD}\x{200D}\x{2764}(?:\x{FE0F}\x{200D}(?:\x{1F48B}\x{200D})?|\x{200D}(?:\x{1F48B}\x{200D})?)\x{1F9D1}|\x{1F469}\x{1F3FD}\x{200D}\x{1F91D}\x{200D}[\x{1F468}\x{1F469}]|\x{1FAF1}\x{1F3FD}\x{200D}\x{1FAF2})[\x{1F3FB}\x{1F3FC}\x{1F3FE}\x{1F3FF}]|(?:\x{1F9D1}\x{1F3FC}\x{200D}\x{2764}(?:\x{FE0F}\x{200D}(?:\x{1F48B}\x{200D})?|\x{200D}(?:\x{1F48B}\x{200D})?)\x{1F9D1}|\x{1F469}\x{1F3FC}\x{200D}\x{1F91D}\x{200D}[\x{1F468}\x{1F469}]|\x{1FAF1}\x{1F3FC}\x{200D}\x{1FAF2})[\x{1F3FB}\x{1F3FD}-\x{1F3FF}]|(?:\x{1F9D1}\x{1F3FB}\x{200D}\x{2764}(?:\x{FE0F}\x{200D}(?:\x{1F48B}\x{200D})?|\x{200D}(?:\x{1F48B}\x{200D})?)\x{1F9D1}|\x{1F469}\x{1F3FB}\x{200D}\x{1F91D}\x{200D}[\x{1F468}\x{1F469}]|\x{1FAF1}\x{1F3FB}\x{200D}\x{1FAF2})[\x{1F3FC}-\x{1F3FF}]|\x{1F468}(?:\x{1F3FB}(?:\x{200D}(?:\x{2764}(?:\x{FE0F}\x{200D}(?:\x{1F48B}\x{200D}\x{1F468}[\x{1F3FB}-\x{1F3FF}]|\x{1F468}[\x{1F3FB}-\x{1F3FF}])|\x{200D}(?:\x{1F48B}\x{200D}\x{1F468}[\x{1F3FB}-\x{1F3FF}]|\x{1F468}[\x{1F3FB}-\x{1F3FF}]))|\x{1F91D}\x{200D}\x{1F468}[\x{1F3FC}-\x{1F3FF}]|[\x{2695}\x{2696}\x{2708}]\x{FE0F}|[\x{2695}\x{2696}\x{2708}]|[\x{1F33E}\x{1F373}\x{1F37C}\x{1F393}\x{1F3A4}\x{1F3A8}\x{1F3EB}\x{1F3ED}\x{1F4BB}\x{1F4BC}\x{1F527}\x{1F52C}\x{1F680}\x{1F692}\x{1F9AF}-\x{1F9B3}\x{1F9BC}\x{1F9BD}]))?|[\x{1F3FC}-\x{1F3FF}]\x{200D}\x{2764}(?:\x{FE0F}\x{200D}(?:\x{1F48B}\x{200D}\x{1F468}[\x{1F3FB}-\x{1F3FF}]|\x{1F468}[\x{1F3FB}-\x{1F3FF}])|\x{200D}(?:\x{1F48B}\x{200D}\x{1F468}[\x{1F3FB}-\x{1F3FF}]|\x{1F468}[\x{1F3FB}-\x{1F3FF}]))|\x{200D}(?:\x{2764}(?:\x{FE0F}\x{200D}(?:\x{1F48B}\x{200D})?|\x{200D}(?:\x{1F48B}\x{200D})?)\x{1F468}|[\x{1F468}\x{1F469}]\x{200D}(?:\x{1F466}\x{200D}\x{1F466}|\x{1F467}\x{200D}[\x{1F466}\x{1F467}])|\x{1F466}\x{200D}\x{1F466}|\x{1F467}\x{200D}[\x{1F466}\x{1F467}]|[\x{1F33E}\x{1F373}\x{1F37C}\x{1F393}\x{1F3A4}\x{1F3A8}\x{1F3EB}\x{1F3ED}\x{1F4BB}\x{1F4BC}\x{1F527}\x{1F52C}\x{1F680}\x{1F692}\x{1F9AF}-\x{1F9B3}\x{1F9BC}\x{1F9BD}])|\x{1F3FF}\x{200D}(?:\x{1F91D}\x{200D}\x{1F468}[\x{1F3FB}-\x{1F3FE}]|[\x{1F33E}\x{1F373}\x{1F37C}\x{1F393}\x{1F3A4}\x{1F3A8}\x{1F3EB}\x{1F3ED}\x{1F4BB}\x{1F4BC}\x{1F527}\x{1F52C}\x{1F680}\x{1F692}\x{1F9AF}-\x{1F9B3}\x{1F9BC}\x{1F9BD}])|\x{1F3FE}\x{200D}(?:\x{1F91D}\x{200D}\x{1F468}[\x{1F3FB}-\x{1F3FD}\x{1F3FF}]|[\x{1F33E}\x{1F373}\x{1F37C}\x{1F393}\x{1F3A4}\x{1F3A8}\x{1F3EB}\x{1F3ED}\x{1F4BB}\x{1F4BC}\x{1F527}\x{1F52C}\x{1F680}\x{1F692}\x{1F9AF}-\x{1F9B3}\x{1F9BC}\x{1F9BD}])|\x{1F3FD}\x{200D}(?:\x{1F91D}\x{200D}\x{1F468}[\x{1F3FB}\x{1F3FC}\x{1F3FE}\x{1F3FF}]|[\x{1F33E}\x{1F373}\x{1F37C}\x{1F393}\x{1F3A4}\x{1F3A8}\x{1F3EB}\x{1F3ED}\x{1F4BB}\x{1F4BC}\x{1F527}\x{1F52C}\x{1F680}\x{1F692}\x{1F9AF}-\x{1F9B3}\x{1F9BC}\x{1F9BD}])|\x{1F3FC}\x{200D}(?:\x{1F91D}\x{200D}\x{1F468}[\x{1F3FB}\x{1F3FD}-\x{1F3FF}]|[\x{1F33E}\x{1F373}\x{1F37C}\x{1F393}\x{1F3A4}\x{1F3A8}\x{1F3EB}\x{1F3ED}\x{1F4BB}\x{1F4BC}\x{1F527}\x{1F52C}\x{1F680}\x{1F692}\x{1F9AF}-\x{1F9B3}\x{1F9BC}\x{1F9BD}])|(?:\x{1F3FF}\x{200D}[\x{2695}\x{2696}\x{2708}]|\x{1F3FE}\x{200D}[\x{2695}\x{2696}\x{2708}]|\x{1F3FD}\x{200D}[\x{2695}\x{2696}\x{2708}]|\x{1F3FC}\x{200D}[\x{2695}\x{2696}\x{2708}]|\x{200D}[\x{2695}\x{2696}\x{2708}])\x{FE0F}|\x{200D}(?:[\x{1F468}\x{1F469}]\x{200D}[\x{1F466}\x{1F467}]|[\x{1F466}\x{1F467}])|\x{1F3FF}\x{200D}[\x{2695}\x{2696}\x{2708}]|\x{1F3FE}\x{200D}[\x{2695}\x{2696}\x{2708}]|\x{1F3FD}\x{200D}[\x{2695}\x{2696}\x{2708}]|\x{1F3FC}\x{200D}[\x{2695}\x{2696}\x{2708}]|\x{1F3FF}|\x{1F3FE}|\x{1F3FD}|\x{1F3FC}|\x{200D}[\x{2695}\x{2696}\x{2708}])?|(?:\x{1F469}(?:\x{1F3FB}\x{200D}\x{2764}(?:\x{FE0F}\x{200D}(?:\x{1F48B}\x{200D}[\x{1F468}\x{1F469}]|[\x{1F468}\x{1F469}])|\x{200D}(?:\x{1F48B}\x{200D}[\x{1F468}\x{1F469}]|[\x{1F468}\x{1F469}]))|[\x{1F3FC}-\x{1F3FF}]\x{200D}\x{2764}(?:\x{FE0F}\x{200D}(?:\x{1F48B}\x{200D}[\x{1F468}\x{1F469}]|[\x{1F468}\x{1F469}])|\x{200D}(?:\x{1F48B}\x{200D}[\x{1F468}\x{1F469}]|[\x{1F468}\x{1F469}])))|\x{1F9D1}[\x{1F3FB}-\x{1F3FF}]\x{200D}\x{1F91D}\x{200D}\x{1F9D1})[\x{1F3FB}-\x{1F3FF}]|\x{1F469}\x{200D}\x{1F469}\x{200D}(?:\x{1F466}\x{200D}\x{1F466}|\x{1F467}\x{200D}[\x{1F466}\x{1F467}])|\x{1F469}(?:\x{200D}(?:\x{2764}(?:\x{FE0F}\x{200D}(?:\x{1F48B}\x{200D}[\x{1F468}\x{1F469}]|[\x{1F468}\x{1F469}])|\x{200D}(?:\x{1F48B}\x{200D}[\x{1F468}\x{1F469}]|[\x{1F468}\x{1F469}]))|[\x{1F33E}\x{1F373}\x{1F37C}\x{1F393}\x{1F3A4}\x{1F3A8}\x{1F3EB}\x{1F3ED}\x{1F4BB}\x{1F4BC}\x{1F527}\x{1F52C}\x{1F680}\x{1F692}\x{1F9AF}-\x{1F9B3}\x{1F9BC}\x{1F9BD}])|\x{1F3FF}\x{200D}[\x{1F33E}\x{1F373}\x{1F37C}\x{1F393}\x{1F3A4}\x{1F3A8}\x{1F3EB}\x{1F3ED}\x{1F4BB}\x{1F4BC}\x{1F527}\x{1F52C}\x{1F680}\x{1F692}\x{1F9AF}-\x{1F9B3}\x{1F9BC}\x{1F9BD}]|\x{1F3FE}\x{200D}[\x{1F33E}\x{1F373}\x{1F37C}\x{1F393}\x{1F3A4}\x{1F3A8}\x{1F3EB}\x{1F3ED}\x{1F4BB}\x{1F4BC}\x{1F527}\x{1F52C}\x{1F680}\x{1F692}\x{1F9AF}-\x{1F9B3}\x{1F9BC}\x{1F9BD}]|\x{1F3FD}\x{200D}[\x{1F33E}\x{1F373}\x{1F37C}\x{1F393}\x{1F3A4}\x{1F3A8}\x{1F3EB}\x{1F3ED}\x{1F4BB}\x{1F4BC}\x{1F527}\x{1F52C}\x{1F680}\x{1F692}\x{1F9AF}-\x{1F9B3}\x{1F9BC}\x{1F9BD}]|\x{1F3FC}\x{200D}[\x{1F33E}\x{1F373}\x{1F37C}\x{1F393}\x{1F3A4}\x{1F3A8}\x{1F3EB}\x{1F3ED}\x{1F4BB}\x{1F4BC}\x{1F527}\x{1F52C}\x{1F680}\x{1F692}\x{1F9AF}-\x{1F9B3}\x{1F9BC}\x{1F9BD}]|\x{1F3FB}\x{200D}[\x{1F33E}\x{1F373}\x{1F37C}\x{1F393}\x{1F3A4}\x{1F3A8}\x{1F3EB}\x{1F3ED}\x{1F4BB}\x{1F4BC}\x{1F527}\x{1F52C}\x{1F680}\x{1F692}\x{1F9AF}-\x{1F9B3}\x{1F9BC}\x{1F9BD}])|\x{1F9D1}(?:\x{200D}(?:\x{1F91D}\x{200D}\x{1F9D1}|[\x{1F33E}\x{1F373}\x{1F37C}\x{1F384}\x{1F393}\x{1F3A4}\x{1F3A8}\x{1F3EB}\x{1F3ED}\x{1F4BB}\x{1F4BC}\x{1F527}\x{1F52C}\x{1F680}\x{1F692}\x{1F9AF}-\x{1F9B3}\x{1F9BC}\x{1F9BD}])|\x{1F3FF}\x{200D}[\x{1F33E}\x{1F373}\x{1F37C}\x{1F384}\x{1F393}\x{1F3A4}\x{1F3A8}\x{1F3EB}\x{1F3ED}\x{1F4BB}\x{1F4BC}\x{1F527}\x{1F52C}\x{1F680}\x{1F692}\x{1F9AF}-\x{1F9B3}\x{1F9BC}\x{1F9BD}]|\x{1F3FE}\x{200D}[\x{1F33E}\x{1F373}\x{1F37C}\x{1F384}\x{1F393}\x{1F3A4}\x{1F3A8}\x{1F3EB}\x{1F3ED}\x{1F4BB}\x{1F4BC}\x{1F527}\x{1F52C}\x{1F680}\x{1F692}\x{1F9AF}-\x{1F9B3}\x{1F9BC}\x{1F9BD}]|\x{1F3FD}\x{200D}[\x{1F33E}\x{1F373}\x{1F37C}\x{1F384}\x{1F393}\x{1F3A4}\x{1F3A8}\x{1F3EB}\x{1F3ED}\x{1F4BB}\x{1F4BC}\x{1F527}\x{1F52C}\x{1F680}\x{1F692}\x{1F9AF}-\x{1F9B3}\x{1F9BC}\x{1F9BD}]|\x{1F3FC}\x{200D}[\x{1F33E}\x{1F373}\x{1F37C}\x{1F384}\x{1F393}\x{1F3A4}\x{1F3A8}\x{1F3EB}\x{1F3ED}\x{1F4BB}\x{1F4BC}\x{1F527}\x{1F52C}\x{1F680}\x{1F692}\x{1F9AF}-\x{1F9B3}\x{1F9BC}\x{1F9BD}]|\x{1F3FB}\x{200D}[\x{1F33E}\x{1F373}\x{1F37C}\x{1F384}\x{1F393}\x{1F3A4}\x{1F3A8}\x{1F3EB}\x{1F3ED}\x{1F4BB}\x{1F4BC}\x{1F527}\x{1F52C}\x{1F680}\x{1F692}\x{1F9AF}-\x{1F9B3}\x{1F9BC}\x{1F9BD}])|\x{1F469}\x{200D}\x{1F466}\x{200D}\x{1F466}|\x{1F469}\x{200D}\x{1F469}\x{200D}[\x{1F466}\x{1F467}]|\x{1F469}\x{200D}\x{1F467}\x{200D}[\x{1F466}\x{1F467}]|(?:\x{1F441}\x{FE0F}?\x{200D}\x{1F5E8}|\x{1F9D1}(?:\x{1F3FF}\x{200D}[\x{2695}\x{2696}\x{2708}]|\x{1F3FE}\x{200D}[\x{2695}\x{2696}\x{2708}]|\x{1F3FD}\x{200D}[\x{2695}\x{2696}\x{2708}]|\x{1F3FC}\x{200D}[\x{2695}\x{2696}\x{2708}]|\x{1F3FB}\x{200D}[\x{2695}\x{2696}\x{2708}]|\x{200D}[\x{2695}\x{2696}\x{2708}])|\x{1F469}(?:\x{1F3FF}\x{200D}[\x{2695}\x{2696}\x{2708}]|\x{1F3FE}\x{200D}[\x{2695}\x{2696}\x{2708}]|\x{1F3FD}\x{200D}[\x{2695}\x{2696}\x{2708}]|\x{1F3FC}\x{200D}[\x{2695}\x{2696}\x{2708}]|\x{1F3FB}\x{200D}[\x{2695}\x{2696}\x{2708}]|\x{200D}[\x{2695}\x{2696}\x{2708}])|\x{1F636}\x{200D}\x{1F32B}|\x{1F3F3}\x{FE0F}?\x{200D}\x{26A7}|\x{1F43B}\x{200D}\x{2744}|(?:[\x{1F3C3}\x{1F3C4}\x{1F3CA}\x{1F46E}\x{1F470}\x{1F471}\x{1F473}\x{1F477}\x{1F481}\x{1F482}\x{1F486}\x{1F487}\x{1F645}-\x{1F647}\x{1F64B}\x{1F64D}\x{1F64E}\x{1F6A3}\x{1F6B4}-\x{1F6B6}\x{1F926}\x{1F935}\x{1F937}-\x{1F939}\x{1F93D}\x{1F93E}\x{1F9B8}\x{1F9B9}\x{1F9CD}-\x{1F9CF}\x{1F9D4}\x{1F9D6}-\x{1F9DD}][\x{1F3FB}-\x{1F3FF}]|[\x{1F46F}\x{1F9DE}\x{1F9DF}])\x{200D}[\x{2640}\x{2642}]|[\x{26F9}\x{1F3CB}\x{1F3CC}\x{1F575}](?:[\x{FE0F}\x{1F3FB}-\x{1F3FF}]\x{200D}[\x{2640}\x{2642}]|\x{200D}[\x{2640}\x{2642}])|\x{1F3F4}\x{200D}\x{2620}|[\x{1F3C3}\x{1F3C4}\x{1F3CA}\x{1F46E}\x{1F470}\x{1F471}\x{1F473}\x{1F477}\x{1F481}\x{1F482}\x{1F486}\x{1F487}\x{1F645}-\x{1F647}\x{1F64B}\x{1F64D}\x{1F64E}\x{1F6A3}\x{1F6B4}-\x{1F6B6}\x{1F926}\x{1F935}\x{1F937}-\x{1F939}\x{1F93C}-\x{1F93E}\x{1F9B8}\x{1F9B9}\x{1F9CD}-\x{1F9CF}\x{1F9D4}\x{1F9D6}-\x{1F9DD}]\x{200D}[\x{2640}\x{2642}]|[\xA9\xAE\x{203C}\x{2049}\x{2122}\x{2139}\x{2194}-\x{2199}\x{21A9}\x{21AA}\x{231A}\x{231B}\x{2328}\x{23CF}\x{23ED}-\x{23EF}\x{23F1}\x{23F2}\x{23F8}-\x{23FA}\x{24C2}\x{25AA}\x{25AB}\x{25B6}\x{25C0}\x{25FB}\x{25FC}\x{25FE}\x{2600}-\x{2604}\x{260E}\x{2611}\x{2614}\x{2615}\x{2618}\x{2620}\x{2622}\x{2623}\x{2626}\x{262A}\x{262E}\x{262F}\x{2638}-\x{263A}\x{2640}\x{2642}\x{2648}-\x{2653}\x{265F}\x{2660}\x{2663}\x{2665}\x{2666}\x{2668}\x{267B}\x{267E}\x{267F}\x{2692}\x{2694}-\x{2697}\x{2699}\x{269B}\x{269C}\x{26A0}\x{26A7}\x{26AA}\x{26B0}\x{26B1}\x{26BD}\x{26BE}\x{26C4}\x{26C8}\x{26CF}\x{26D1}\x{26D3}\x{26E9}\x{26F0}-\x{26F5}\x{26F7}\x{26F8}\x{26FA}\x{2702}\x{2708}\x{2709}\x{270F}\x{2712}\x{2714}\x{2716}\x{271D}\x{2721}\x{2733}\x{2734}\x{2744}\x{2747}\x{2763}\x{27A1}\x{2934}\x{2935}\x{2B05}-\x{2B07}\x{2B1B}\x{2B1C}\x{2B55}\x{3030}\x{303D}\x{3297}\x{3299}\x{1F004}\x{1F170}\x{1F171}\x{1F17E}\x{1F17F}\x{1F202}\x{1F237}\x{1F321}\x{1F324}-\x{1F32C}\x{1F336}\x{1F37D}\x{1F396}\x{1F397}\x{1F399}-\x{1F39B}\x{1F39E}\x{1F39F}\x{1F3CD}\x{1F3CE}\x{1F3D4}-\x{1F3DF}\x{1F3F5}\x{1F3F7}\x{1F43F}\x{1F4FD}\x{1F549}\x{1F54A}\x{1F56F}\x{1F570}\x{1F573}\x{1F576}-\x{1F579}\x{1F587}\x{1F58A}-\x{1F58D}\x{1F5A5}\x{1F5A8}\x{1F5B1}\x{1F5B2}\x{1F5BC}\x{1F5C2}-\x{1F5C4}\x{1F5D1}-\x{1F5D3}\x{1F5DC}-\x{1F5DE}\x{1F5E1}\x{1F5E3}\x{1F5E8}\x{1F5EF}\x{1F5F3}\x{1F5FA}\x{1F6CB}\x{1F6CD}-\x{1F6CF}\x{1F6E0}-\x{1F6E5}\x{1F6E9}\x{1F6F0}\x{1F6F3}])\x{FE0F}|\x{1F441}\x{FE0F}?\x{200D}\x{1F5E8}|\x{1F9D1}(?:\x{1F3FF}\x{200D}[\x{2695}\x{2696}\x{2708}]|\x{1F3FE}\x{200D}[\x{2695}\x{2696}\x{2708}]|\x{1F3FD}\x{200D}[\x{2695}\x{2696}\x{2708}]|\x{1F3FC}\x{200D}[\x{2695}\x{2696}\x{2708}]|\x{1F3FB}\x{200D}[\x{2695}\x{2696}\x{2708}]|\x{200D}[\x{2695}\x{2696}\x{2708}])|\x{1F469}(?:\x{1F3FF}\x{200D}[\x{2695}\x{2696}\x{2708}]|\x{1F3FE}\x{200D}[\x{2695}\x{2696}\x{2708}]|\x{1F3FD}\x{200D}[\x{2695}\x{2696}\x{2708}]|\x{1F3FC}\x{200D}[\x{2695}\x{2696}\x{2708}]|\x{1F3FB}\x{200D}[\x{2695}\x{2696}\x{2708}]|\x{200D}[\x{2695}\x{2696}\x{2708}])|\x{1F3F3}\x{FE0F}?\x{200D}\x{1F308}|\x{1F469}\x{200D}\x{1F467}|\x{1F469}\x{200D}\x{1F466}|\x{1F636}\x{200D}\x{1F32B}|\x{1F3F3}\x{FE0F}?\x{200D}\x{26A7}|\x{1F635}\x{200D}\x{1F4AB}|\x{1F62E}\x{200D}\x{1F4A8}|\x{1F415}\x{200D}\x{1F9BA}|\x{1FAF1}(?:\x{1F3FF}|\x{1F3FE}|\x{1F3FD}|\x{1F3FC}|\x{1F3FB})?|\x{1F9D1}(?:\x{1F3FF}|\x{1F3FE}|\x{1F3FD}|\x{1F3FC}|\x{1F3FB})?|\x{1F469}(?:\x{1F3FF}|\x{1F3FE}|\x{1F3FD}|\x{1F3FC}|\x{1F3FB})?|\x{1F43B}\x{200D}\x{2744}|(?:[\x{1F3C3}\x{1F3C4}\x{1F3CA}\x{1F46E}\x{1F470}\x{1F471}\x{1F473}\x{1F477}\x{1F481}\x{1F482}\x{1F486}\x{1F487}\x{1F645}-\x{1F647}\x{1F64B}\x{1F64D}\x{1F64E}\x{1F6A3}\x{1F6B4}-\x{1F6B6}\x{1F926}\x{1F935}\x{1F937}-\x{1F939}\x{1F93D}\x{1F93E}\x{1F9B8}\x{1F9B9}\x{1F9CD}-\x{1F9CF}\x{1F9D4}\x{1F9D6}-\x{1F9DD}][\x{1F3FB}-\x{1F3FF}]|[\x{1F46F}\x{1F9DE}\x{1F9DF}])\x{200D}[\x{2640}\x{2642}]|[\x{26F9}\x{1F3CB}\x{1F3CC}\x{1F575}](?:[\x{FE0F}\x{1F3FB}-\x{1F3FF}]\x{200D}[\x{2640}\x{2642}]|\x{200D}[\x{2640}\x{2642}])|\x{1F3F4}\x{200D}\x{2620}|\x{1F1FD}\x{1F1F0}|\x{1F1F6}\x{1F1E6}|\x{1F1F4}\x{1F1F2}|\x{1F408}\x{200D}\x{2B1B}|\x{2764}(?:\x{FE0F}\x{200D}[\x{1F525}\x{1FA79}]|\x{200D}[\x{1F525}\x{1FA79}])|\x{1F441}\x{FE0F}?|\x{1F3F3}\x{FE0F}?|[\x{1F3C3}\x{1F3C4}\x{1F3CA}\x{1F46E}\x{1F470}\x{1F471}\x{1F473}\x{1F477}\x{1F481}\x{1F482}\x{1F486}\x{1F487}\x{1F645}-\x{1F647}\x{1F64B}\x{1F64D}\x{1F64E}\x{1F6A3}\x{1F6B4}-\x{1F6B6}\x{1F926}\x{1F935}\x{1F937}-\x{1F939}\x{1F93C}-\x{1F93E}\x{1F9B8}\x{1F9B9}\x{1F9CD}-\x{1F9CF}\x{1F9D4}\x{1F9D6}-\x{1F9DD}]\x{200D}[\x{2640}\x{2642}]|\x{1F1FF}[\x{1F1E6}\x{1F1F2}\x{1F1FC}]|\x{1F1FE}[\x{1F1EA}\x{1F1F9}]|\x{1F1FC}[\x{1F1EB}\x{1F1F8}]|\x{1F1FB}[\x{1F1E6}\x{1F1E8}\x{1F1EA}\x{1F1EC}\x{1F1EE}\x{1F1F3}\x{1F1FA}]|\x{1F1FA}[\x{1F1E6}\x{1F1EC}\x{1F1F2}\x{1F1F3}\x{1F1F8}\x{1F1FE}\x{1F1FF}]|\x{1F1F9}[\x{1F1E6}\x{1F1E8}\x{1F1E9}\x{1F1EB}-\x{1F1ED}\x{1F1EF}-\x{1F1F4}\x{1F1F7}\x{1F1F9}\x{1F1FB}\x{1F1FC}\x{1F1FF}]|\x{1F1F8}[\x{1F1E6}-\x{1F1EA}\x{1F1EC}-\x{1F1F4}\x{1F1F7}-\x{1F1F9}\x{1F1FB}\x{1F1FD}-\x{1F1FF}]|\x{1F1F7}[\x{1F1EA}\x{1F1F4}\x{1F1F8}\x{1F1FA}\x{1F1FC}]|\x{1F1F5}[\x{1F1E6}\x{1F1EA}-\x{1F1ED}\x{1F1F0}-\x{1F1F3}\x{1F1F7}-\x{1F1F9}\x{1F1FC}\x{1F1FE}]|\x{1F1F3}[\x{1F1E6}\x{1F1E8}\x{1F1EA}-\x{1F1EC}\x{1F1EE}\x{1F1F1}\x{1F1F4}\x{1F1F5}\x{1F1F7}\x{1F1FA}\x{1F1FF}]|\x{1F1F2}[\x{1F1E6}\x{1F1E8}-\x{1F1ED}\x{1F1F0}-\x{1F1FF}]|\x{1F1F1}[\x{1F1E6}-\x{1F1E8}\x{1F1EE}\x{1F1F0}\x{1F1F7}-\x{1F1FB}\x{1F1FE}]|\x{1F1F0}[\x{1F1EA}\x{1F1EC}-\x{1F1EE}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F7}\x{1F1FC}\x{1F1FE}\x{1F1FF}]|\x{1F1EF}[\x{1F1EA}\x{1F1F2}\x{1F1F4}\x{1F1F5}]|\x{1F1EE}[\x{1F1E8}-\x{1F1EA}\x{1F1F1}-\x{1F1F4}\x{1F1F6}-\x{1F1F9}]|\x{1F1ED}[\x{1F1F0}\x{1F1F2}\x{1F1F3}\x{1F1F7}\x{1F1F9}\x{1F1FA}]|\x{1F1EC}[\x{1F1E6}\x{1F1E7}\x{1F1E9}-\x{1F1EE}\x{1F1F1}-\x{1F1F3}\x{1F1F5}-\x{1F1FA}\x{1F1FC}\x{1F1FE}]|\x{1F1EB}[\x{1F1EE}-\x{1F1F0}\x{1F1F2}\x{1F1F4}\x{1F1F7}]|\x{1F1EA}[\x{1F1E6}\x{1F1E8}\x{1F1EA}\x{1F1EC}\x{1F1ED}\x{1F1F7}-\x{1F1FA}]|\x{1F1E9}[\x{1F1EA}\x{1F1EC}\x{1F1EF}\x{1F1F0}\x{1F1F2}\x{1F1F4}\x{1F1FF}]|\x{1F1E8}[\x{1F1E6}\x{1F1E8}\x{1F1E9}\x{1F1EB}-\x{1F1EE}\x{1F1F0}-\x{1F1F5}\x{1F1F7}\x{1F1FA}-\x{1F1FF}]|\x{1F1E7}[\x{1F1E6}\x{1F1E7}\x{1F1E9}-\x{1F1EF}\x{1F1F1}-\x{1F1F4}\x{1F1F6}-\x{1F1F9}\x{1F1FB}\x{1F1FC}\x{1F1FE}\x{1F1FF}]|\x{1F1E6}[\x{1F1E8}-\x{1F1EC}\x{1F1EE}\x{1F1F1}\x{1F1F2}\x{1F1F4}\x{1F1F6}-\x{1F1FA}\x{1F1FC}\x{1F1FD}\x{1F1FF}]|[#\*0-9]\x{FE0F}?\x{20E3}|\x{1F93C}[\x{1F3FB}-\x{1F3FF}]|\x{2764}\x{FE0F}?|[\x{1F3C3}\x{1F3C4}\x{1F3CA}\x{1F46E}\x{1F470}\x{1F471}\x{1F473}\x{1F477}\x{1F481}\x{1F482}\x{1F486}\x{1F487}\x{1F645}-\x{1F647}\x{1F64B}\x{1F64D}\x{1F64E}\x{1F6A3}\x{1F6B4}-\x{1F6B6}\x{1F926}\x{1F935}\x{1F937}-\x{1F939}\x{1F93D}\x{1F93E}\x{1F9B8}\x{1F9B9}\x{1F9CD}-\x{1F9CF}\x{1F9D4}\x{1F9D6}-\x{1F9DD}][\x{1F3FB}-\x{1F3FF}]|[\x{26F9}\x{1F3CB}\x{1F3CC}\x{1F575}][\x{FE0F}\x{1F3FB}-\x{1F3FF}]?|\x{1F3F4}|[\x{270A}\x{270B}\x{1F385}\x{1F3C2}\x{1F3C7}\x{1F442}\x{1F443}\x{1F446}-\x{1F450}\x{1F466}\x{1F467}\x{1F46B}-\x{1F46D}\x{1F472}\x{1F474}-\x{1F476}\x{1F478}\x{1F47C}\x{1F483}\x{1F485}\x{1F48F}\x{1F491}\x{1F4AA}\x{1F57A}\x{1F595}\x{1F596}\x{1F64C}\x{1F64F}\x{1F6C0}\x{1F6CC}\x{1F90C}\x{1F90F}\x{1F918}-\x{1F91F}\x{1F930}-\x{1F934}\x{1F936}\x{1F977}\x{1F9B5}\x{1F9B6}\x{1F9BB}\x{1F9D2}\x{1F9D3}\x{1F9D5}\x{1FAC3}-\x{1FAC5}\x{1FAF0}\x{1FAF2}-\x{1FAF6}][\x{1F3FB}-\x{1F3FF}]|[\x{261D}\x{270C}\x{270D}\x{1F574}\x{1F590}][\x{FE0F}\x{1F3FB}-\x{1F3FF}]|[\x{261D}\x{270A}-\x{270D}\x{1F385}\x{1F3C2}\x{1F3C7}\x{1F408}\x{1F415}\x{1F43B}\x{1F442}\x{1F443}\x{1F446}-\x{1F450}\x{1F466}\x{1F467}\x{1F46B}-\x{1F46D}\x{1F472}\x{1F474}-\x{1F476}\x{1F478}\x{1F47C}\x{1F483}\x{1F485}\x{1F48F}\x{1F491}\x{1F4AA}\x{1F574}\x{1F57A}\x{1F590}\x{1F595}\x{1F596}\x{1F62E}\x{1F635}\x{1F636}\x{1F64C}\x{1F64F}\x{1F6C0}\x{1F6CC}\x{1F90C}\x{1F90F}\x{1F918}-\x{1F91F}\x{1F930}-\x{1F934}\x{1F936}\x{1F93C}\x{1F977}\x{1F9B5}\x{1F9B6}\x{1F9BB}\x{1F9D2}\x{1F9D3}\x{1F9D5}\x{1FAC3}-\x{1FAC5}\x{1FAF0}\x{1FAF2}-\x{1FAF6}]|[\x{1F3C3}\x{1F3C4}\x{1F3CA}\x{1F46E}\x{1F470}\x{1F471}\x{1F473}\x{1F477}\x{1F481}\x{1F482}\x{1F486}\x{1F487}\x{1F645}-\x{1F647}\x{1F64B}\x{1F64D}\x{1F64E}\x{1F6A3}\x{1F6B4}-\x{1F6B6}\x{1F926}\x{1F935}\x{1F937}-\x{1F939}\x{1F93D}\x{1F93E}\x{1F9B8}\x{1F9B9}\x{1F9CD}-\x{1F9CF}\x{1F9D4}\x{1F9D6}-\x{1F9DD}]|[\x{1F46F}\x{1F9DE}\x{1F9DF}]|[\xA9\xAE\x{203C}\x{2049}\x{2122}\x{2139}\x{2194}-\x{2199}\x{21A9}\x{21AA}\x{231A}\x{231B}\x{2328}\x{23CF}\x{23ED}-\x{23EF}\x{23F1}\x{23F2}\x{23F8}-\x{23FA}\x{24C2}\x{25AA}\x{25AB}\x{25B6}\x{25C0}\x{25FB}\x{25FC}\x{25FE}\x{2600}-\x{2604}\x{260E}\x{2611}\x{2614}\x{2615}\x{2618}\x{2620}\x{2622}\x{2623}\x{2626}\x{262A}\x{262E}\x{262F}\x{2638}-\x{263A}\x{2640}\x{2642}\x{2648}-\x{2653}\x{265F}\x{2660}\x{2663}\x{2665}\x{2666}\x{2668}\x{267B}\x{267E}\x{267F}\x{2692}\x{2694}-\x{2697}\x{2699}\x{269B}\x{269C}\x{26A0}\x{26A7}\x{26AA}\x{26B0}\x{26B1}\x{26BD}\x{26BE}\x{26C4}\x{26C8}\x{26CF}\x{26D1}\x{26D3}\x{26E9}\x{26F0}-\x{26F5}\x{26F7}\x{26F8}\x{26FA}\x{2702}\x{2708}\x{2709}\x{270F}\x{2712}\x{2714}\x{2716}\x{271D}\x{2721}\x{2733}\x{2734}\x{2744}\x{2747}\x{2763}\x{27A1}\x{2934}\x{2935}\x{2B05}-\x{2B07}\x{2B1B}\x{2B1C}\x{2B55}\x{3030}\x{303D}\x{3297}\x{3299}\x{1F004}\x{1F170}\x{1F171}\x{1F17E}\x{1F17F}\x{1F202}\x{1F237}\x{1F321}\x{1F324}-\x{1F32C}\x{1F336}\x{1F37D}\x{1F396}\x{1F397}\x{1F399}-\x{1F39B}\x{1F39E}\x{1F39F}\x{1F3CD}\x{1F3CE}\x{1F3D4}-\x{1F3DF}\x{1F3F5}\x{1F3F7}\x{1F43F}\x{1F4FD}\x{1F549}\x{1F54A}\x{1F56F}\x{1F570}\x{1F573}\x{1F576}-\x{1F579}\x{1F587}\x{1F58A}-\x{1F58D}\x{1F5A5}\x{1F5A8}\x{1F5B1}\x{1F5B2}\x{1F5BC}\x{1F5C2}-\x{1F5C4}\x{1F5D1}-\x{1F5D3}\x{1F5DC}-\x{1F5DE}\x{1F5E1}\x{1F5E3}\x{1F5E8}\x{1F5EF}\x{1F5F3}\x{1F5FA}\x{1F6CB}\x{1F6CD}-\x{1F6CF}\x{1F6E0}-\x{1F6E5}\x{1F6E9}\x{1F6F0}\x{1F6F3}]|[\x{23E9}-\x{23EC}\x{23F0}\x{23F3}\x{25FD}\x{2693}\x{26A1}\x{26AB}\x{26C5}\x{26CE}\x{26D4}\x{26EA}\x{26FD}\x{2705}\x{2728}\x{274C}\x{274E}\x{2753}-\x{2755}\x{2757}\x{2795}-\x{2797}\x{27B0}\x{27BF}\x{2B50}\x{1F0CF}\x{1F18E}\x{1F191}-\x{1F19A}\x{1F201}\x{1F21A}\x{1F22F}\x{1F232}-\x{1F236}\x{1F238}-\x{1F23A}\x{1F250}\x{1F251}\x{1F300}-\x{1F320}\x{1F32D}-\x{1F335}\x{1F337}-\x{1F37C}\x{1F37E}-\x{1F384}\x{1F386}-\x{1F393}\x{1F3A0}-\x{1F3C1}\x{1F3C5}\x{1F3C6}\x{1F3C8}\x{1F3C9}\x{1F3CF}-\x{1F3D3}\x{1F3E0}-\x{1F3F0}\x{1F3F8}-\x{1F407}\x{1F409}-\x{1F414}\x{1F416}-\x{1F43A}\x{1F43C}-\x{1F43E}\x{1F440}\x{1F444}\x{1F445}\x{1F451}-\x{1F465}\x{1F46A}\x{1F479}-\x{1F47B}\x{1F47D}-\x{1F480}\x{1F484}\x{1F488}-\x{1F48E}\x{1F490}\x{1F492}-\x{1F4A9}\x{1F4AB}-\x{1F4FC}\x{1F4FF}-\x{1F53D}\x{1F54B}-\x{1F54E}\x{1F550}-\x{1F567}\x{1F5A4}\x{1F5FB}-\x{1F62D}\x{1F62F}-\x{1F634}\x{1F637}-\x{1F644}\x{1F648}-\x{1F64A}\x{1F680}-\x{1F6A2}\x{1F6A4}-\x{1F6B3}\x{1F6B7}-\x{1F6BF}\x{1F6C1}-\x{1F6C5}\x{1F6D0}-\x{1F6D2}\x{1F6D5}-\x{1F6D7}\x{1F6DD}-\x{1F6DF}\x{1F6EB}\x{1F6EC}\x{1F6F4}-\x{1F6FC}\x{1F7E0}-\x{1F7EB}\x{1F7F0}\x{1F90D}\x{1F90E}\x{1F910}-\x{1F917}\x{1F920}-\x{1F925}\x{1F927}-\x{1F92F}\x{1F93A}\x{1F93F}-\x{1F945}\x{1F947}-\x{1F976}\x{1F978}-\x{1F9B4}\x{1F9B7}\x{1F9BA}\x{1F9BC}-\x{1F9CC}\x{1F9D0}\x{1F9E0}-\x{1F9FF}\x{1FA70}-\x{1FA74}\x{1FA78}-\x{1FA7C}\x{1FA80}-\x{1FA86}\x{1FA90}-\x{1FAAC}\x{1FAB0}-\x{1FABA}\x{1FAC0}-\x{1FAC2}\x{1FAD0}-\x{1FAD9}\x{1FAE0}-\x{1FAE7}]/u', '', $text);
}
function closest_word($input, $word, &$percent = null) {
	$shortest = -1;

	$lev = levenshtein($input, $word);

	if ($lev == 0) {
		$closest = $word;
		$shortest = 0;			
	}

	if ($lev <= $shortest || $shortest < 0) {
		$closest  = $word;
		$shortest = $lev;
	}

	$percent = 1 - levenshtein($input, $closest) / max(strlen($input), strlen($closest));

	return $closest;
}
?>