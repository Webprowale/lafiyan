<?php
require 'vendor/autoload.php';

$apiKey = $_ENV['API_KEY'];  
$projectId = $_ENV['PROJECT_ID'];      
$csvFile = "contacts.csv";           

function scheduleSMS($phone, $message, $date) {
    global $apiKey, $projectId;
    $dateArray = explode("/", $date);
    if (count($dateArray) !== 3) {
        return "Invalid date format";
    }
    $timestamp = strtotime("{$dateArray[2]}-{$dateArray[1]}-{$dateArray[0]}");

    $url = "https://api.telerivet.com/v1/projects/$projectId/scheduled_messages";
    $data = [
        "to_number" => $phone,
        "content" => $message,
        "start_time" => $timestamp
    ];
    $options = [
        "http" => [
            "header" => "Content-Type: application/json\r\n" .
                "Authorization: Basic " . base64_encode("$apiKey:"),
            "method" => "POST",
            "content" => json_encode($data),
        ],
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    return $result ? "Scheduled for $phone" : "Failed for $phone";
}
if (($handle = fopen($csvFile, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $phone = $data[0];   
        $valid = strtolower($data[1]);  
        $sendDate = $data[2]; 
        $message = $data[3];

        if ($valid === "yes" || $valid === "1") { 
            echo scheduleSMS($phone, $message, $sendDate) . "\n";
        }
    }
    fclose($handle);
} else {
    echo "Error opening CSV file.";
}
?>
