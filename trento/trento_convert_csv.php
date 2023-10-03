<?php
/**
 *
 * PHP version >= 5.0
 *
 * @author		Maurizio Mazzoneschi <graffio@lynxlab.com>
 * @copyright	Copyright (c) 2020
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU Public License v.3
 * @version		0.1
 * 
 * @abstract    Trento data conversion
 * 				csv --> json
 * 
 *    			Affluenza 19.00 del 20-09
 *    			Affluenza 23.00 del 20-09
 *    			Affluenza 15.00 finale del 21-09
 *    			Voti ai candidati Presidente
 *    			Voti alle liste
 *    			Voti di preferenza
 */

include_once 'config.inc.php';

include_once '../Logger/KLogger02.php';

//$log = KLogger::instance(dirname(__FILE__), KLogger::DEBUG);
$log = KLogger::instance(DIR_LOG, KLogger::DEBUG);

/**
 * Esempi d'uso del logger
 * 
$log->logInfo('Info Test');
$log->logNotice('Notice Test');
$log->logWarn('Warn Test');
$log->logError('Error Test');
$log->logFatal('Fatal Test');
$log->logAlert('Alert Test');
$log->logCrit('Crit test');
$log->logEmerg('Emerg Test');
*/


//include_once '../Logger/Logger53.php';
include_once '../utility.inc.php';
include_once '../oggetti.inc.php';

/**
 * Lettura Lista comuni
 */
$fileDaRecuperare = LISTA_COMUNI;
$dataListaComuniHA = FileManagement::json_to_array($fileDaRecuperare,$log);
if (!$dataListaComuniHA) {
	$log->logFatal('Impossibile proseguire. Impossibile recuperare il file'. $fileDaRecuperare);
	die();
}
$desc_prov = 'TRENTO';
$cod_prov = COD_PROV;


/**
 * Inizializzazione file da scrivere
 */
//$file2write_part = CONV_DIR;
$file2write_part = FILE_PATH_CONVERTITO;
$file2write_provincia_part = FILE_PATH_PROVINCIA_CONVERTITO;

/**
 * Lettura voti Presidente da file locale

$fileNameVotiPresidente = DOWN_DIR .'/'.'VotiPresidenti.txt';
$dataVotiPresidenteAr = FileManagement::csv_to_array($fileNameVotiPresidente,$log,';');
var_dump($dataVotiPresidenteAr);
 */

/**
 * Lettura voti Presidente da file remoto
 */
$fileDaRecuperare = REMOTE_SITE_TRENTO.'/'.'VotiPresidenti.txt';
$dataVotiPresidenteAr = FileManagement::getFileFromRemote($fileDaRecuperare,$log);
$dataVotiPresidenteAr = FileManagement::csv_to_array($fileDaRecuperare,$log,';',false);
$specificaLog[] = $fileDaRecuperare;
if (!$dataVotiPresidenteAr) {
	$log->logFatal('Impossibile proseguire. Impossibile recuperare il file'. $fileDaRecuperare);
//	Logger::error("Impossibile proseguire. Impossibile recuperare il file", $specificaLog);
	die();
}

/**
 * Lettura candidati e immagini
 * Lettura da locale
 */
$fileNamePicCandidati = './dati_scaricati/'.'candidati_foto.csv'; 
$dataPicCandidatiAr = array(); 
$dataPicCandidatiAr = FileManagement::csv_to_array($fileNamePicCandidati,$log,";",false);
if (!$dataPicCandidatiAr) {
	$log->logFatal('Impossibile proseguire. Impossibile recuperare il file'. $fileNamePicCandidati);
	die();
}
$dataPicCandListaAr = array();
$numCandTmp = 0;
foreach ($dataPicCandidatiAr as $singolCandPic) {
    if ($singolCandPic['Progressivo Presidente'] > $numCandTmp +1) {
        $numCandTmp++;
    }
    $dataPicCandListaAr[$numCandTmp][] = $singolCandPic;
} 


/**
 * lettura Affluenza.
 * lettura da filesystem
 * Essendo iniziato lo scrutinio si può prendere l'ultimo aggiornamento dell'affluenza

$fileNameAffluenza = DOWN_DIR.'/'.'Affluenza15-del-21-09.txt';
$specificaLog[0] = $fileNameAffluenza;
$dataAffluenzaAr = FileManagement::csv_to_array($fileNameAffluenza,$log,';');

if (!$dataAffluenzaAr) {
	$log->logError('Impossibile proseguire. Impossibile recuperare il file'. $fileNameAffluenza);
	//Logger::error("Impossibile proseguire. Impossibile recuperare il file", $specificaLog);
	die();
}
 */

/**
 * lettura Affluenza da remoto.
 * Essendo iniziato lo scrutinio si può prendere l'ultimo aggiornamento dell'affluenza
 */

$fileNameAffluenza = REMOTE_SITE_TRENTO.'/'.'Affluenza22.txt';
$specificaLog[0] = $fileNameAffluenza;
$dataAffluenzaAr = FileManagement::getFileFromRemote($fileNameAffluenza,$log);
if (!$dataAffluenzaAr) {
	$log->logFatal('Impossibile proseguire. Impossibile recuperare il file'. $fileNameAffluenza);
//	Logger::error("Impossibile proseguire. Impossibile recuperare il file", $specificaLog);
	die();
}

/**
 * Inizializzazione array per totale affluenza
 */
$dataAffluenzaProvinciaHA = array();
$dataAffluenzaProvinciaHA['cod_prov'] = $cod_prov;
$dataAffluenzaProvinciaHA['desc_prov'] = $desc_prov;
$dataAffluenzaProvinciaHA['Sez.Pervenute'] = 0;
$dataAffluenzaProvinciaHA['Sez.Totali'] = 0;
$dataAffluenzaProvinciaHA['ElettoriM'] = 0;
$dataAffluenzaProvinciaHA['ElettoriF'] = 0;
$dataAffluenzaProvinciaHA['ElettoriT'] = 0;
$dataAffluenzaProvinciaHA['VotantiM'] = 0;
$dataAffluenzaProvinciaHA['VotantiF'] = 0;
$dataAffluenzaProvinciaHA['VotantiT'] = 0;

/**
 * trasformazione in array associativo Affluenza.
 * si accede ai dati dell'affluenza del comune tramite indice Codice Istat 
 */
foreach ($dataAffluenzaAr as $comuneAffluenza) {
		$CodIstatComune = $comuneAffluenza['Istat Comune'];
		$comuneAffluenza['cod_prov'] = $cod_prov;
		$comuneAffluenza['cod_com'] = substr($dataListaComuniHA[$CodIstatComune]['CODICE ELETTORALE'],-4);
		$comuneAffluenza['desc_prov'] = $desc_prov;
		$dataAffluenzaHA[$CodIstatComune] = $comuneAffluenza;

		// Totale affluenza Provincia
		$dataAffluenzaProvinciaHA['Sez.Pervenute'] += $comuneAffluenza['Sez.Pervenute'];
		$dataAffluenzaProvinciaHA['Sez.Totali'] += $comuneAffluenza['Sez.Totali'];
		$dataAffluenzaProvinciaHA['ElettoriM'] += $comuneAffluenza['ElettoriM'];
		$dataAffluenzaProvinciaHA['ElettoriF'] += $comuneAffluenza['ElettoriF'];
		$dataAffluenzaProvinciaHA['ElettoriT'] += $comuneAffluenza['ElettoriT'];
		$dataAffluenzaProvinciaHA['VotantiM'] += $comuneAffluenza['VotantiM'];
		$dataAffluenzaProvinciaHA['VotantiF'] += $comuneAffluenza['VotantiF'];
		$dataAffluenzaProvinciaHA['VotantiT'] += $comuneAffluenza['VotantiT'];


}

/**
 * Lettura voti Liste da locale
$fileNameVotiListe = DOWN_DIR.'/'.'VotiListe.csv';
$dataVotiListeAr = FileManagement::csv_to_array($fileNameVotiListe,';');
$specificaLog[] = $fileNameVotiListe;
if (!$dataVotiListeAr) {
	Logger::error("Impossibile proseguire. Impossibile recuperare il file", $specificaLog);
	die();
}
 */

/**
 * Lettura voti Liste
 * Lettura da remoto
 */
$fileNameVotiListe = REMOTE_SITE_TRENTO.'/'.'VotiListe.txt';
$dataVotiListeAr = FileManagement::getFileFromRemote($fileNameVotiListe,$log,';');
$specificaLog[0] = $fileNameVotiListe;
if (!$dataVotiListeAr) {
	$log->logFatal('Impossibile proseguire. Impossibile recuperare il file'. $fileNameVotiListe);
	Logger::error("Impossibile proseguire. Impossibile recuperare il file", $specificaLog);
	die();
}


/**
 * trasformazione in array associativo VotiListe.
 * si accede ai dati dei voti delle liste tramite indice ID Presidente 
 */
$PresidenteId = 0;
foreach ($dataVotiListeAr as $dataVotiSingolaLista) {
	if ($PresidenteId <> $dataVotiSingolaLista['Presidente Id'] ) {
		$PresidenteId = $dataVotiSingolaLista['Presidente Id'];
	}
	$dataVotiListeHA[$PresidenteId][] = $dataVotiSingolaLista;
}

ksort($dataVotiListeHA);

/**
 * Creazione oggetto ENTI x json
 * modello Ministero dell'Interno
 * scrutinio_comunali_1t.json
 */

$comuneInCorso = '';

$objectEnte = new enti();
$tot_com = 0;

/**
 * Cicla Voti Presidente
 * crea nuovo oggetto per ogni comune
 * Imposta dati generali (parte in new scrutinio, parte in setCandidato. Alcuni dati generali sono nel file dei voti del Presidente)
 * Imposta Voti lista per ogni Presidente in setVotiListeCandidato
 */

foreach ($dataVotiPresidenteAr as $singleDataVotiPresidenteAr) {
	if ($singleDataVotiPresidenteAr['Istat Comune'] == $comuneInCorso) { 
		$objectComune->numeroCandidato = $objectComune->numeroCandidato + 1;
/** 
		$singleDataVotiPresidenteAr[]
		$dataVotiSingolaLista['img_lis_r'] = $dataNameLoghiListeAr[$ordineLista]['LIST_PICTURE'];
*/
		$objectComune->setCandidato($singleDataVotiPresidenteAr);
		// Aggiunge voti di lista per ogni candidato
		$objectComune->setVotiListeCandidato($dataVotiListeAr, $comuneInCorso); 
//		$objectComune->setVotiListeCandidato($dataVotiListeHA); 

		// Aggiorna provincia
		$objectProvincia->numeroCandidatoProvincia = $objectProvincia->numeroCandidatoProvincia + 1;
		$objectProvincia->setCandidatoProvincia($singleDataVotiPresidenteAr);
		$objectProvincia->setVotiListeCandidatoProvincia($dataVotiListeHA, $comuneInCorso); 

	} else {
		if (isset($objectComune)) { //->jsonObject->desc_com)) {

			$objectProvincia->setCalcoliProvinciaTrento($singleDataVotiPresidenteAr);

			//ordina per voti
			$cand = $objectComune->jsonObject->cand; 
			usort($cand, 'confrontaVoti');
			$objectComune->jsonObject->cand = $cand; 

			$objectComune->OrdinaListe(); // nel caso di trento ordina i candidati di ogni lista per il numero di voti


			// scrive file
			$cod_com = $objectComune->jsonObject->int->cod_com;
			$file2write = $file2write_part.$cod_com.'/response.json';

//			$file2write = $file2write_part.$comuneInCorso.'response.json';
			FileManagement::save_object_to_json($objectComune->jsonObject,$file2write,$log); 

			//Upload file to dl
			if (MAKE_UPLOAD) {
				FileManagement::upload_to_dl($file2write, $url=UPLOAD_URL, $cod_prov, $cod_com, $log);	
			}
			echo $tot_com . ': '.$objectComune->jsonObject->int->cod_com.' - '. $cod_com. ' - '. $CodIstatComune . ' - '. $objectComune->jsonObject->int->desc_com . '<br>';

			//Aggiunge comune a Ente
			$objectEnte->setComune($objectComune->jsonObject);

			// distrugge oggetto
			unset($objectComune);
			$objectProvincia->numeroCandidatoProvincia = 0;


		}
		$comuneInCorso = $singleDataVotiPresidenteAr['Istat Comune'];
		// crea oggetto
		$objectComune = new scrutinio($dataAffluenzaHA[$comuneInCorso]);
		$tot_com++;

		// Aggiungi candidato
		$objectComune->setCandidato($singleDataVotiPresidenteAr);

		// Aggiunge voti di lista per ogni candidato
		$objectComune->setVotiListeCandidato($dataVotiListeAr, $comuneInCorso); 
//		$objectComune->setVotiListeCandidato($dataVotiListeHA); 

		// Aggiorna i dati della provincia
		if (!isset($objectProvincia)) {
			$objectProvincia = new scrutinioProvincia($dataAffluenzaProvinciaHA);
		} 
		$objectProvincia->setCandidatoProvincia($singleDataVotiPresidenteAr);
		$objectProvincia->setVotiListeCandidatoProvincia($dataVotiListeHA, $comuneInCorso);



	}
}
/* Scrive ultimo comune
*/
if (isset($objectComune)) { //->jsonObject->desc_com)) {

	//ordina per voti
	$cand = $objectComune->jsonObject->cand; 
	usort($cand, 'confrontaVoti');
	$objectComune->jsonObject->cand = $cand; 

	$objectComune->OrdinaListe();


	// scrive file
	$cod_com = $objectComune->jsonObject->int->cod_com;
	
	$file2write = $file2write_part.$cod_com.'/response.json';
//			$file2write = $file2write_part.$comuneInCorso.'response.json';
	FileManagement::save_object_to_json($objectComune->jsonObject,$file2write,$log); 

	//Upload file to dl
	if (MAKE_UPLOAD) {
		FileManagement::upload_to_dl($file2write, $url=UPLOAD_URL, $cod_prov, $cod_com, $log);	
	}
	echo $tot_com . ': '.$objectComune->jsonObject->int->cod_com.' - '. $cod_com. ' - '. $CodIstatComune . ' - '. $objectComune->jsonObject->int->desc_com . '<br>';

	//Aggiunge comune a Ente
	$objectEnte->setComune($objectComune->jsonObject);

	// distrugge oggetto
	unset($objectComune);

}

/**
 *  Scrive la provincia
 */ 
if (isset($objectProvincia)) {

	// scrive file
	$file2write = $file2write_provincia_part.'/response.json';
//			$file2write = $file2write_part.$comuneInCorso.'response.json';

// Ordina l'array di oggetti secondo la proprietà "voti"
	$cand = $objectProvincia->jsonObject->cand;
	usort($cand, 'confrontaVoti');
	$objectProvincia->jsonObject->cand = $cand;

    $objectProvincia->OrdinaListe();
	
	FileManagement::save_object_to_json($objectProvincia->jsonObject,$file2write,$log); 

	//Upload file to dl
	if (MAKE_UPLOAD) {
		FileManagement::upload_to_dl($file2write, $url=UPLOAD_URL, REG_STO, $cod_com, $log);	
	}
	echo $tot_com . ': '.$objectProvincia->jsonObject->int->cod_pro.' - '. $cod_com. ' - '. $CodIstatComune . ' - '. $objectComune->jsonObject->int->desc_com . '<br>';

	// distrugge oggetto
	unset($objectProvincia);
}

/**
 * Scrive il file Enti
 */
if (AGGIORNA_ENTI) {
	$file2write = FILE_PATH_CONVERTITO.'responseTrento.json';
	FileManagement::save_object_to_json($objectEnte->jsonObject,$file2write,$log); 
	
	//Upload file to dl
	if (MAKE_UPLOAD) {
		FileManagement::upload_generic_to_dl($file2write, $log, $upload_path=DL_PATH_ENTI, $url=UPLOAD_URL);
	}
	
}

// Funzione di confronto per l'ordinamento
function confrontaVoti($a, $b) {
    if ($a->voti == $b->voti) {
        return 0;
    }
//    return ($a->voti < $b->voti) ? -1 : 1; // ascendente
	return ($a->voti < $b->voti) ? 1 : -1; // discendente
}


echo "<h2>Conversione della provincia di Trento terminata con successo</h2>";