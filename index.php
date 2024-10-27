<?php
include("config.php");

$localConfigFile="config.local.php";
if (file_exists($localConfigFile)) {
//	echo "Loading $localConfigFile\n";
} else {
	echo "$localConfigFile file does not exists. Create it first!\n";
	exit;
}

// Download and parse dataset
$url='https://bet.szerencsejatek.hu/cmsfiles/otos.json';
$file = file_get_contents($url);
$data = json_decode($file, true);

// throw unnecessary data part away
//unset($data['numberStatistics']);

$results = array();

$currentWeekNumber=date("W");

foreach ($games as $player => $game) {
	if ($currentWeekNumber >= $game['firstWeek'] && $currentWeekNumber <= $game['lastWeek']) {	// filter out stil valid games
		$ret = isMatch($data, $game['year'], $game['firstWeek'], $game['lastWeek'], $game['numbers']);
		if (!empty($ret)) {
			$results[$player][] = $ret;
		}
	}
}

// Print summarized results
// Check if there is a game with at least 2 matching numbers which is not too old
$emailMessage = "";
$currentWeekNo=date("W");
foreach ($results as $player => $playerResults) {
	foreach ($playerResults as $i => $fieldResults) {
		foreach ($fieldResults as $ii => $result) {
			if ($result['match'] >= 2) {
				$emailMessage = $emailMessage . $player."! ".$result['match']." numbers matches on ".$result['date']."! Prize: ".$result['prize']."\n";
			}
		}
	}
}

if ($emailMessage == "") {
	$emailMessage = "No match at all :(\n";
}

// Print detailed results
if (!empty($results)) {
	$emailMessage = $emailMessage . print_r($results, true);
	mail($emailTo, "lotto5", $emailMessage);
} 
function isMatch($data, $year, $firstWeek, $lastWeek, $numbers) {
	$ret = array();

	// iterate throw the games in the dataset
	foreach ($data['drawings'] as $draw) {
		// filter games what we are interrested in
		if ( $draw['week'] >= $firstWeek && $draw['week'] <= $lastWeek && $draw['year'] == $year) {
			// check all fields we used
			foreach ($numbers as $field => $myBet) {
				if (count($myBet) <> 5) {
					die('Incorrect net found!');
				}

				$match = 0;
				foreach ($myBet as $myNumber) {
					// check numbers in the drawing
					foreach ($draw['numbers'] as $number) {
						// count matching numbers
						if ( $number == $myNumber ) {
							$match++;
						}
					}
				}
		
				$prize = 0;
				switch ($match) {
				    case 1:
					$prize = isset($draw['1HitPrize']) ? $draw['1HitPrize'] : "?";
					break;
				    case 2:
					$prize = isset($draw['2HitPrize']) ? $draw['2HitPrize'] : "?";
					break;
				    case 3:
					$prize = isset($draw['3HitPrize']) ? $draw['3HitPrize'] : "?";
					break;
				    case 4:
					$prize = isset($draw['4HitPrize']) ? $draw['4HitPrize'] : "?";
					break;
				    case 5:
					$prize = isset($draw['5HitPrize']) ? $draw['5HitPrize'] : "?";
					break;
				}	

				$ret[] = array(
						'year' => $year,
						'week' => $draw['week'],
						'date' => $draw['date'],
						'match' => $match,
						'field' => $field,
						'prize' => $prize
					);
			}
		}
	}

	return $ret;
}
?>
