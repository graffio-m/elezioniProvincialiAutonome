<?php

include_once 'config.inc.php';

$ch = curl_init($jsonUrl);

$userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';
curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// Set other options if needed

$jsonData = curl_exec($ch);

if ($jsonData === false) {
    echo 'cURL error: ' . curl_error($ch);
}

curl_close($ch);

// Fetch JSON content from the URL
//$jsonData = file_get_contents($jsonUrl);

if ($jsonData === false) {
    die('Error fetching JSON data from the URL');
}

// Decode JSON data
$data = json_decode($jsonData, true);

$regionali = $data['stats']['regionale']['risultati'];
$voti_presidente = $regionali['voti_presidente'];
$voti_presidente['dati_generali'] = 'sezioni scrutinate: '. $voti_presidente['sezioni_scrutinate'];

$voti_lista = $regionali['voti_lista'];
unset($voti_lista['sezioni_scrutinate']);
unset($voti_lista['voti_tot']);
unset($voti_lista['consolidato']);
unset($voti_lista['aggiornamento']);



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

foreach ($voti_presidente as $key => &$item) {
    $item['id_presidente'] = $key;
}
/**
 * Ordinamento dell'array $voti_liste per id_presidente e voti
 */
// Definisci la funzione di confronto per usort
function confronto($a, $b) {
    if ($a['id_presidente'] == $b['id_presidente']) {
        // Se id_presidente è uguale, ordina per voti in ordine decrescente
        return $b['voti'] - $a['voti'];
    }
    // Altrimenti, ordina per id_presidente in ordine crescente
    return $a['id_presidente'] - $b['id_presidente'];
}

// Ordina il secondo array utilizzando la funzione di confronto
usort($voti_lista, 'confronto');


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

/**
 * costruzione array per tabella candidato e liste
* Unisci i due array in base all'id_presidente
*/
$voti_presidente_liste = array();
foreach ($voti_presidente as $presidente) {
    $imageValue = "![Candidato](".$imageBaseURL.$presidente['image'].')';
    unset($presidente['image']);
    $presidente = array('image' => $imageValue) + $presidente;
    $presidente['denominazione'] = '**'.$presidente['denominazione'].'**';
    unset($presidente['denominazione_coalizione']);
    unset($presidente['voti_coalizione']);
    unset($presidente['percent_coalizione']);
    $voti_presidente_liste[] = $presidente;
    foreach ($voti_lista as $item) {
        if ($item['id_presidente'] == $presidente['id_presidente']) {
            $id_presidente = $item['id_presidente'];
            unset($item['id_presidente']);
            $item['id_presidente'] = $id_presidente;

            //$imageValue = $item['image'];
            $imageValue = null;
            unset($item['image']);
            $item = array('image' => $imageValue) + $item;

            $voti_presidente_liste[] = $item;

        }
    }
    
}
/* var_dump($voti_presidente_liste);
die();
 */

/**
 * Scrittura csv per tabella
 */
// Open the CSV file for writing
$csvFile = fopen($csvFilePartitiPath, 'w');

// Write the header row to the CSV file
if (!empty($voti_presidente)) {
    $headerP = array (
        '','Candidati e liste','Voti','%'
    );
    fputcsv($csvFile, $headerP);
}

// Write the data rows to the CSV file
foreach ($voti_presidente_liste as $row) {
    fputcsv($csvFile, $row);
}

fclose($csvFile);

echo 'CSV file has been generated successfully at ' . $csvFilePartitiPath . PHP_EOL.'<BR>\n';



// Ottieni la data corrente
$currentDateTime = date('d.m.Y H:i');

// Definisci l'array associativo
$data = [
    'annotate' => [
        'notes' => "Ultimo Aggiornamento: $oraAggiornamento"
    ],
    'describe' => [
        'intro' => "Sezioni scrutinate: $sezioniScrutinate su $sezioniTotali. Ultimo Aggiornamento: $oraAggiornamento"
    ]
];
// Converte l'array associativo in una stringa JSON
$jsonString = json_encode($data, JSON_PRETTY_PRINT);

// Scrivi la stringa JSON nel file
file_put_contents($jsonFilePath, $jsonString);

echo "<br>Il file JSON è stato creato con successo alle .$currentDateTime\n";


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


    // Upload file 3
    $remote_file3 = "ssh2.sftp://$sftp$remote_directory" . basename($csvFilePartitiPath);
    if (copy($csvFilePartitiPath, $remote_file3)) {
        echo "File 3 uploaded successfully.\n";
    } else {
        echo "Error uploading file 3.\n";
    }

    // Close SFTP connection
    ssh2_disconnect($sftp_conn);
} else {
    echo "SFTP connection or login failed.\n";
}

?>
