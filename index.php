<pre>
<?php
// numbers we used in bet
$numbers = array(
									'field1'=>array( 06, 16, 17, 18, 80 ),
									'field2'=>array( 07, 16, 25, 55, 58 ),
									'field3'=>array( 11, 22, 33, 44, 90 ),
									'field4'=>array(  3,  8, 21, 36, 38 ),
									'field5'=>array( 15, 30, 32, 64, 69 ),
									'field6'=>array( 15, 22, 49, 73, 87 )
								);

// year when we bet
$year = 2018;
// bet valid from week...
$firstWeek=23;
// bet valid until week...
$lastWeek=27;

// json data source

$url='https://bet.szerencsejatek.hu/cmsfiles/otos.json';
$file = file_get_contents($url);
$data = json_decode($file, true);

# throw unnecessary data part away
unset($data['numberStatistics']);

// header
echo "week\tdate\t\tmatches<br/>";

// iterate throw the games in the dataset
foreach ($data['drawings'] as $key => $game)
{
	// filter games what we are interrested in
	if ( $game['week'] >= $firstWeek && $game['week'] <= $lastWeek && $game['year'] == $year)
	{
		$match = 0;
		
		// check numbers in the game
		foreach ($game['numbers'] as $key => $number)
		{
			// check all fields and numbers we used	
			foreach ($numbers as $key => $myNumber)
			{
				// count matching numbers
				if ( $number == $myNumber )
				{
					$match++;
				}
			}			
		}
		
		// print how many numbers are matching
		echo $game['week']."\t".$game['date']."\t".$match." <br/>";
	}
}
?>
</pre>