<?php

$urlTemplate = 'https://www.etermin.net/api/timeslots?date=%s&serviceid=371663&rangesearch=1&caching=false&capacity=1&duration=0&cluster=false&slottype=0&fillcalendarstrategy=0&showavcap=true&appfuture=1095&appdeadline=0&appdeadlinewm=0&oneoff=null&msdcm=0&calendarid=';

$acceptableDates = [
	'2024-10-01',
	'2024-11-01',
	'2024-12-01',
	'2025-01-01',
	'2025-02-01',
	'2025-03-01',
	'2025-04-01',
];

foreach ($acceptableDates as $date) {
	$url = sprintf($urlTemplate, $date);
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_HTTPHEADER, [
		'webid: rhein-kreis-neuss',
	]);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($curl);
	$responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	$responseError = curl_error($curl);
	if (empty($responseError) && 200 === $responseCode) {
		$data = json_decode($response);
		if (!empty($data)) {
			notify("SUCCESS", "$date is available");
		}
	} else {
		echo "ERROR\n";
		var_dump($date, $url, $responseCode, $responseError);
		notify("ERROR", "Date: $date\n  URL: $url\n  Response code: $responseCode\n  " . var_export($responseError, true));
	}
	
	notify("INFO", "Script fiished");
}

function notify($subject, $body) {
	var_dump($subject, $body);
}