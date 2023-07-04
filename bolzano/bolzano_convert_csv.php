<?php
/**
 *
 * PHP version >= 5.0
 *
 * @author		Maurizio Mazzoneschi <graffio@lynxlab.com>
 * @copyright	Copyright (c) 2023
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU Public License v.3
 * @version		0.1
 * 
 * @abstract    Bolzano data conversion
 * 				csv --> json
 * 
 *    			Affluenza data aggiornamento contenuta nel file
 *    			Voti alle liste
 *    			Voti di preferenza
 * 
 * NOTA: per consentire la rappresentazione senza modifiche agli script di front-end i dati del candidato contengono quelli della lista 
 *       mentre i dati delle liste di ogni candidato (che in realtà è una lista) contengono i dati dei voti dei candidati per ogni lista
 * 
 *       La legge elettorale per l'elezione del consiglio provinciale della Provincia Autonoma di Bolzano è un proporzionale puro. 
 *         
 * 
 */

include_once 'config.inc.php';

include_once '../Logger/KLogger02.php';

//$log = KLogger::instance(dirname(__FILE__), KLogger::DEBUG);
$log = KLogger::instance(DIR_LOG, KLogger::DEBUG);


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
$desc_prov = DESC_PROV;
$cod_prov = COD_PROV;

/**
 * Lettura Liste e Candidati 
$fileDaRecuperare = LISTA_CANDIDATURE;
$dataListaCandidatureAr = FileManagement::csv_to_array($fileDaRecuperare,$log,';');
if (!$dataListaCandidatureAr) {
	$log->logFatal('Impossibile proseguire. Impossibile recuperare il file'. $fileDaRecuperare);
	die();
}
 */

/**
 * Inizializzazione file da scrivere
 */
//$file2write_part = CONV_DIR;
$file2write_part = FILE_PATH_CONVERTITO;



/**
 * lettura Affluenza.
 * lettura da filesystem
*/
//$fileNameAffluenza = DOWN_DIR.'/'.'AFFLUENZE-SUM.CSV';
$fileNameAffluenza = REMOTE_SITE_BOLZANO.'/'.'AFFLUENZA_WAHLBETEILIGUNG.CSV';
$dataAffluenzaAr = FileManagement::csv_to_array($fileNameAffluenza,$log,"\t",false);

if (!$dataAffluenzaAr) {
	$log->logError('Impossibile proseguire. Impossibile recuperare il file'. $fileNameAffluenza);
	//Logger::error("Impossibile proseguire. Impossibile recuperare il file", $specificaLog);
	die();
}
//var_dump($dataAffluenzaAr);die();


/**
 * Depura l'array dalle rilevazioni diverse dalle ore 15 del 21.
 * Chiave: ALLE_ORE == 115
$dataAffluenzaTmpAr = $dataAffluenzaAr; 
$dataAffluenzaAr = array();
for ($i=0;$i < count($dataAffluenzaTmpAr); $i++) { 
//    echo $dataAffluenzaAr[$i]['ALLE_ORE'].'<BR>';
    if ($dataAffluenzaTmpAr[$i]['ALLE_ORE'] == '115') {
        $dataAffluenzaAr[] = $dataAffluenzaTmpAr[$i];
    }
}
 */

/**
 * Depura l'array. Prende solo l'ultima rilevazione per ogni comune.
 */
$dataAffluenzaTmpAr = $dataAffluenzaAr; 
$dataAffluenzaAr = array();
$cod_prov_tmp = null;
$cod_com_tmp = $dataAffluenzaTmpAr[0]['MUNI_NUM'];
for ($i=0;$i < count($dataAffluenzaTmpAr); $i++) { 
    if ($cod_com_tmp != null && $cod_com_tmp != $dataAffluenzaTmpAr[$i]['MUNI_NUM']) {
        $dataAffluenzaAr[] = $dataAffluenzaTmpAr[$i-1];
        $cod_com_tmp = $dataAffluenzaTmpAr[$i]['MUNI_NUM'];
    } elseif ($i == count($dataAffluenzaTmpAr)-1) {
        $dataAffluenzaAr[] = $dataAffluenzaTmpAr[$i];

    } 
}


/**
 * trasformazione in array associativo Affluenza.
 * si accede ai dati dell'affluenza del comune tramite indice Codice Istat 
 */
foreach ($dataAffluenzaAr as $comuneAffluenza) {
    $codComIstatString = $comuneAffluenza['MUNI_NUM'];
    for ($i=1; $i < 4; $i++) {
        if (strlen($codComIstatString) < 3) {
            $codComIstatString = '0'.$codComIstatString;
        } 
    }

    $comuneAffluenza['PROVISTAT'] = '021';
//    $CodIstatComune = $comuneAffluenza['PROVISTAT'] . $codComIstatString;
    $CodIstatComune =  $codComIstatString;
    $comuneAffluenza['cod_prov'] = $cod_prov;
    $comuneAffluenza['cod_com'] = substr($dataListaComuniHA[$CodIstatComune]['CODICE ELETTORALE'],-4);
    $comuneAffluenza['desc_prov'] = $desc_prov;
    $comuneAffluenza['cod_ISTAT'] = $CodIstatComune;
//    $comuneAffluenza['cod_comune_originale'] = $comuneAffluenza['COMUNEISTAT'];
    $dataAffluenzaHA[$CodIstatComune] = $comuneAffluenza;

}

/**
 * Lettura voti Liste
$fileNameVotiListe = DOWN_DIR.'/'.'VotiListe.txt';
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
$fileNameVotiListe = REMOTE_SITE_BOLZANO.'/'.'VOTILISTA_LISTENSTIMMEN.CSV'; 
 
//$fileNameVotiListe = REMOTE_SITE_BOLZANO.'/'.'VOTILISTE-SUM.CSV'; 
$dataVotiListeAr = FileManagement::csv_to_array($fileNameVotiListe,$log,"\t",false);
if (!$dataVotiListeAr) {
	$log->logFatal('Impossibile proseguire. Impossibile recuperare il file'. $fileNameVotiListe);
	die();
}

/**
 * trasformazione in array associativo VotiListe.
 * si accede ai dati dei voti delle liste tramite indice codice comune + ordine lista  
 */
$ordineLista = '0';
$comuneIstatTmp = '0';
foreach ($dataVotiListeAr as $dataVotiSingolaLista) {
	if ($comuneIstatTmp <> $dataVotiSingolaLista['MUNI_NUM']) {
        $comuneIstatTmp = $dataVotiSingolaLista['MUNI_NUM'];
        $ordineLista = 0;
    }

    if ($dataVotiSingolaLista['LIST_NUM'] != '') {
        $ordineLista = $dataVotiSingolaLista['LIST_NUM'];
        $dataVotiListeHA[$comuneIstatTmp][$ordineLista] = $dataVotiSingolaLista;
    } 

}

/**
 * Lettura preferenze ai candidati collegati alle liste
 */
$fileNameVotiPreferenze = REMOTE_SITE_BOLZANO.'/'.'VOTILISTA_LISTENSTIMMEN.CSV'; 
 
$dataVotiPreferenzeAr = FileManagement::csv_to_array($fileNameVotiListe,$log,"\t",false);
if (!$dataVotiPreferenzeAr) {
	$log->logFatal('Impossibile proseguire. Impossibile recuperare il file'. $fileNameVotiListe);
	die();
}
/**
 * FINO QUI
 */

/**
 * Creazione oggetto x json
 * modello Ministero dell'Interno
 */

$comuneInCorso = '';
$objectEnte = new enti();
$tot_com = 0;
/**
 * Cicla Voti Lista
 * crea nuovo oggetto per ogni comune
 * Imposta dati generali (parte in new scrutinio, parte in setCandidato. Alcuni dati generali sono nel file dei voti del sindaco)
 * Imposta Voti lista per ogni sindaco in setVotiListeCandidato
 */
foreach ($dataVotiSindacoAr as $singleDataVotiSindacoAr) {

    $codComIstatString = $singleDataVotiSindacoAr['COMUNEISTAT'];
    for ($i=1; $i < 4; $i++) {
        if (strlen($codComIstatString) < 3) {
            $codComIstatString = '0'.$codComIstatString;
        } 
    }
    $CodIstatComune = PROV_ISTAT.$codComIstatString;
	if ($singleDataVotiSindacoAr['COMUNEISTAT'] == $comuneInCorso && isset($objectComune)) { 
		$objectComune->numeroCandidato = $objectComune->numeroCandidato + 1;
		$objectComune->setCandidato($singleDataVotiSindacoAr);
		// Aggiunge voti di lista per ogni candidato
		$objectComune->setVotiListeCandidato($dataVotiListeHA);
	} else {
		if (isset($objectComune)) { //->jsonObject->desc_com)) {
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
        $comuneInCorso = $singleDataVotiSindacoAr['COMUNEISTAT']; // Codice ISTAT senza la parte di Provincia
         
        /**
         * crea oggetto Se esiste il dato dell'affluenza.
         * Altrimenti assume che non si sia votato.
         */  

        if (array_key_exists($CodIstatComune, $dataAffluenzaHA)) {
            $objectComune = new scrutinio($dataAffluenzaHA[$CodIstatComune]); 
            $tot_com++;
            // Aggiungi candidato
            $objectComune->setCandidato($singleDataVotiSindacoAr);

            // Aggiunge voti di lista per ogni candidato
            $objectComune->setVotiListeCandidato($dataVotiListeHA);
        }

	}
}
/* Scrive ultimo comune
*/
if (isset($objectComune)) { //->jsonObject->desc_com)) {
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
 * Scrive il file Enti
 */
if (AGGIORNA_ENTI) {
	$file2write = FILE_PATH_CONVERTITO.'responseBolzano.json';
	FileManagement::save_object_to_json($objectEnte->jsonObject,$file2write,$log); 
	
	//Upload file to dl
	if (MAKE_UPLOAD) {
		FileManagement::upload_generic_to_dl($file2write, $log, $upload_path=DL_PATH_ENTI, $url=UPLOAD_URL);
	}
	
}

echo "<h2>Conversione della provincia di Bolzano terminata con successo</h2>";