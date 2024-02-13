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
$sftp_server = 'server.sft.it';
$sftp_username = 'user';
$sftp_password = 'password';
$remote_directory = '/var/www/html/sardegna/';

$csvFilePartitiPath = 'dati_elezioni_partiti_feb_2024.csv'; 


$csvFilePath = 'dati_elezioni_feb_2024.csv'; 

 // Specifica il percorso del file JSON
$jsonFilePath = 'metadati_sardegna.json';

// URL of the JSON data
$jsonUrl = 'https://amministrazioneaperta.regione.sardegna.it/feedprovasier2019.php';
$imageBaseURL = 'https://amministrazioneaperta.regione.sardegna.it';
