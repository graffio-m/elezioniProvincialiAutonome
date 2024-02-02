<?php

// URL of the JSON data
$jsonUrl = 'https://amministrazioneaperta.regione.sardegna.it/feedprovasier2019.php';

// Fetch JSON content from the URL
$jsonData = file_get_contents($jsonUrl);

if ($jsonData === false) {
    die('Error fetching JSON data from the URL');
}

// Decode JSON data
$data = json_decode($jsonData, true);
$regionali = $data['stats']['regionale']['risultati'];
$voti_presidente = $regionali['voti_presidente'];
$voti_presidente['dati_generali'] = 'sezioni scrutinate: '. $voti_presidente['sezioni_scrutinate'];
unset($voti_presidente['sezioni_scrutinate']);
$voti_presidente['dati_generali'] .= '\nvoti_tot: '. $voti_presidente['voti_tot'];
unset($voti_presidente['voti_tot']);
$voti_presidente['dati_generali'] .= '\nAggiornamento: '. $voti_presidente['aggiornamento'];
unset($voti_presidente['aggiornamento']);
unset($voti_presidente['consolidato']);
$dati_generali = $voti_presidente['dati_generali'];
unset($voti_presidente['dati_generali']);

// Sort the array based on the "voti" key in descending order
usort($voti_presidente, function($a, $b) {
    return $b['voti'] - $a['voti'];
});

//print_r($voti_presidente);
//die();
// CSV file path
$csvFilePath = 'output.csv';

// Open the CSV file for writing
$csvFile = fopen($csvFilePath, 'w');

/* 
// Write the header row to the CSV file
if (!empty($data)) {
    $header = array_keys($data[0]);
    fputcsv($csvFile, $header);
}

 */
/* 
// Write the data rows to the CSV file
foreach ($data as $row) {
    fputcsv($csvFile, $row);
}
 */
// Close the CSV file

// Write the header row to the CSV file
if (!empty($voti_presidente)) {
    $header = array_keys($voti_presidente[key($voti_presidente)]);
    fputcsv($csvFile, $header);
}

// Write the data rows to the CSV file
foreach ($voti_presidente as $row) {
    fputcsv($csvFile, $row);
}

fclose($csvFile);

echo 'CSV file has been generated successfully at ' . $csvFilePath . PHP_EOL;

// Ottieni la data corrente
$currentDateTime = date('d.m.Y H:i');

// Definisci l'array associativo
$data = [
    'annotate' => [
        'notes' => "Ultimo Aggiornamento: $currentDateTime"
    ]
];
// Converte l'array associativo in una stringa JSON
$jsonString = json_encode($data, JSON_PRETTY_PRINT);

// Specifica il percorso del file JSON
$filePath = 'output.json';

// Scrivi la stringa JSON nel file
file_put_contents($filePath, $jsonString);

echo "Il file JSON è stato creato con successo.\n";

?>
