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
    '2025-05-01',
    '2025-06-01',
    '2025-07-01',
    '2025-08-01',
    '2025-09-01',
    '2025-10-01',
    '2025-11-01',
    '2025-12-01',
];

foreach ($acceptableDates as $date) {
    $url = sprintf($urlTemplate, $date);
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'webid: rhein-kreis-neuss',
    ]);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $responseError = curl_error($curl);
    if (empty($responseError) && 200 === $responseCode) {
        $data = json_decode($response);
        if (!empty($data)) {
            notify("SUCCESS", "$date is available\n Visit https://www.etermin.net/Rhein-Kreis-Neuss?servicegroupid=128874");
        }
    } else {
        notify("ERROR", "Date: $date\n  URL: $url\n  Response code: $responseCode\n  " . var_export($responseError, true));
    }
}


notify("INFO", "Script finished");

function notify($subject, $body) {
    $curl = curl_init('https://api.sendgrid.com/v3/mail/send');
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . getenv('SENDGRID_API_KEY'),
        'Content-Type: application/json',
    ]);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    $postData = json_encode([
        'personalizations' => [
            [
                'to' => [
                    ['email' => getenv('EMAIL_ADDRESS_TO')],
                ],
            ],
        ],
        'from' => [
            'email' => getenv('EMAIL_ADDRESS_FROM'),
        ],
        'subject' => $subject,
        'content' => [
            [
                'type' => 'text/plain',
                'value' => $body,
            ],
        ],
    ]);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
    $response = curl_exec($curl);
    $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $responseError = curl_error($curl);
    if (!empty($responseError) || 202 !== $responseCode) {
        var_dump('ERROR wile sending email', $response, $responseError, $responseCode);
    }
}
