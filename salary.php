<?php
function isWeekend($date){
	$day = $date->format("l");

	//if the day is saturday or sunday return true
        if ($day == 'Saturday' || $day == 'Sunday'){
                return true;
        }
	else{
		return false;
	}
}

function isHoliday($date, $year){

	//PHP function to get the easter date
	$easter = date("m-d", easter_date($year));

	//2 days before easter
	$suurreede = strtotime('-2 days', easter_date($year));
	$suurreede = date("m-d", $suurreede);


	//49 days after easter
	$nelipyhad = strtotime('+49 days', easter_date($year));
	$nelipyhad = date("m-d", $nelipyhad);

	$estonianholidays = array('01-01', '02-24', $suurreede, $easter, $nelipyhad, 
				'05-01', '06-23', '06-24', '08-20', '12-24', '12-25', '12-26' );

	$day = $date->format("m-d");

	//if the date is in the list of holidays, return true
	if (in_array($day, $estonianholidays)){
		return true;
	}
	return false;
}

//check if there is any inputs
if (!isset($argv[1])){
        echo "Please enter a valid year \n";
        return;
}

//check if the input is a number
if (!ctype_digit($argv[1])){
	echo "Please enter a valid year \n";
	return;
}

//first potential date
$begin = new DateTime($argv[1].'-01-10');

//last potential date
$end = new DateTime(($argv[1]).'-12-11');

//cycle through the begin to end 1 month at a time
$interval = DateInterval::createFromDateString('1 month');
$period = new DatePeriod($begin, $interval, $end);

//csv headers
$csv = array(
	['Payement Date', 'Accountant Reminder'],
	);

//Go over the 10th of each month
foreach ($period as $dt) {
	//reminder and payment same day
    	$day = clone $dt;
    	$reminder = clone $dt;
    	while (true) {
		//if the payment day is a holiday or on weekend -1 day
        	if (isHoliday($day, $argv[1]) || isWeekend($day)) {
            		$day->modify("-1 day");
            		$reminder->modify("-1 day");
        	} else {
			//counter for reminder (3 = 3 working days earlier than payment)
			$i = 0;
            		while (true) {
				//if counter is on holiday or weekend, -1 days without counter
				//because we only count working days
                		if (isHoliday($reminder, $argv[1]) || isWeekend($reminder)) {
                    			$reminder->modify("-1 day");
				//if not a holiday and not on weekend + counter is 3 days
                		} else if($i == 3) {
					break;
				//is a working day 
				} else {
                    			$reminder->modify("-1 day");
                    			$i++;
                		}
            		}
			//add dates to csv array
            		array_push($csv, [
                		$day->format("Y-m-d"),
                		$reminder->format("Y-m-d"),
            		]);
            		break;
        	}
    	}
}

//open the csv file or create if does not exist
$csvfile = fopen($argv[1].'.csv', 'w');

//add each row to the csv
foreach ($csv as $field) {
    fputcsv($csvfile, $field);
}

//close the file
fclose($csvfile);
?>
