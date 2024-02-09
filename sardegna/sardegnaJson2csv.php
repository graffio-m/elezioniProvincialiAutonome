<?php

/**
 * Impostazione nomi file e URL
 */

/**
 * 

 Path dati per dataWrapper
 https://www.rainews.it/dl/rainews/elezioni2024/sardegna/dati_elezioni_feb_2024.csv

 Path metadati per dataWrapper
 https://www.rainews.it/dl/rainews/elezioni2024/sardegna/metadati_sardegna.json

 */


// Server SFTP settings
$sftp_server = 'oventic.graffio.org';
$sftp_username = 'graffio';
$sftp_password = 'Maur1208#';
$remote_directory = '/var/www/html/grav/sardegna/';



$csvFilePath = 'dati_elezioni_feb_2024.csv'; 

 // Specifica il percorso del file JSON
$jsonFilePath = 'metadati_sardegna.json';

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

/** dati per aggiornamento (Json)
 * 
 */ 
$sezioniScrutinate = $voti_presidente['sezioni_scrutinate'];
$sezioniTotali = $data['stats']['regionale']['sezioni'];
$oraAggiornamento = $voti_presidente['aggiornamento'];
// Ottieni la data corrente
$currentDateTime = date('d.m.Y H:i');
$oraAggiornamento = $currentDateTime; 

/**
 * Fine dati per aggiornamento
 */

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

// Open the CSV file for writing
$csvFile = fopen($csvFilePath, 'w');

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

echo 'CSV file has been generated successfully at ' . $csvFilePath . PHP_EOL.'<BR>\n';

// Ottieni la data corrente
$currentDateTime = date('d.m.Y H:i');

// Definisci l'array associativo
$data = [
    'annotate' => [
        'notes' => "Ultimo Aggiornamento: $oraAggiornamento"
    ],
    'describe' => [
        'intro' => "Voti ai candidati presidenti. Sezioni scrutinate: $sezioniScrutinate su $sezioniTotali"
    ]
];
// Converte l'array associativo in una stringa JSON
$jsonString = json_encode($data, JSON_PRETTY_PRINT);

// Scrivi la stringa JSON nel file
file_put_contents($jsonFilePath, $jsonString);

echo "<br>Il file JSON è stato creato con successo alle .$currentDateTime\n";


// Local files to upload
$file1 = '/path/to/local/file1.txt';
$file2 = '/path/to/local/file2.txt';

// Remote directory on the SFTP server

// Connect to SFTP server
$sftp_conn = ssh2_connect($sftp_server);

// Login to SFTP server
if (ssh2_auth_password($sftp_conn, $sftp_username, $sftp_password)) {
    // Open an SFTP session
    $sftp = ssh2_sftp($sftp_conn);

    // Upload file 1
    $remote_file1 = "ssh2.sftp://$sftp$remote_directory" . basename($csvFilePath);
    if (copy($csvFilePath, $remote_file1)) {
        echo "File 1 uploaded successfully.\n";
    } else {
        echo "Error uploading file 1.\n";
    }

    // Upload file 2
    $remote_file2 = "ssh2.sftp://$sftp$remote_directory" . basename($jsonFilePath);
    if (copy($jsonFilePath, $remote_file2)) {
        echo "File 2 uploaded successfully.\n";
    } else {
        echo "Error uploading file 2.\n";
    }

    // Close SFTP connection
    ssh2_disconnect($sftp_conn);
} else {
    echo "SFTP connection or login failed.\n";
}

?>


?>
