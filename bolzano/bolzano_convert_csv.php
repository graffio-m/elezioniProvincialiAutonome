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
$file2write_provincia_part = FILE_PATH_PROVINCIA_CONVERTITO;



/**
 * lettura Affluenza.
 * lettura da filesystem
*/
//$fileNameAffluenza = DOWN_DIR.'/'.'AFFLUENZE-SUM.CSV';
$fileNameAffluenza = REMOTE_SITE_BOLZANO.'AFFLUENZA_WAHLBETEILIGUNG.CSV';
//$dataAffluenzaAr = FileManagement::csv_to_array($fileNameAffluenza,$log,"\t",false);
$dataAffluenzaAr = FileManagement::csv_to_array($fileNameAffluenza,$log,";",false);

if (!$dataAffluenzaAr) {
	$log->logError('Impossibile proseguire. Impossibile recuperare il file'. $fileNameAffluenza);
	//Logger::error("Impossibile proseguire. Impossibile recuperare il file", $specificaLog);
	die();
}


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
 * Viene calcolata l'affluenza totale
 */
$affluenzaTotaleHA = array();
$affluenzaTotaleHA['ele_m'] = 0;
$affluenzaTotaleHA['ele_f'] = 0;;
$affluenzaTotaleHA['ele_t'] = 0;
$affluenzaTotaleHA['vot_m'] = 0;
$affluenzaTotaleHA['vot_f'] = 0;
$affluenzaTotaleHA['vot_t'] = 0;

$affluenzaTotale = 0;
$affluenzaTotale_m = 0;
$affluenzaTotale_f = 0;
/*
$dataAffluenzaTmpAr = $dataAffluenzaAr; 
$dataAffluenzaAr = array();
$cod_prov_tmp = null;
$cod_com_tmp = $dataAffluenzaTmpAr[0]['MUNI_NUM'];
 for ($i=0;$i < count($dataAffluenzaTmpAr); $i++) { 
    if ($cod_com_tmp != null && $cod_com_tmp != $dataAffluenzaTmpAr[$i]['MUNI_NUM']) {
        $dataAffluenzaAr[] = $dataAffluenzaTmpAr[$i-1];
        $cod_com_tmp = $dataAffluenzaTmpAr[$i]['MUNI_NUM'];
        $affluenzaTotale += $dataAffluenzaTmpAr[$i]['MUNI_VOTERS_T'];
    } elseif ($i == count($dataAffluenzaTmpAr)-1) {
        $dataAffluenzaAr[] = $dataAffluenzaTmpAr[$i];
        $affluenzaTotale += $dataAffluenzaTmpAr[$i]['MUNI_VOTERS_T'];

    } 
}

 */
foreach ($dataAffluenzaAr as $dataAffluenzaRilevazioneSingola) {
    if ($dataAffluenzaRilevazioneSingola['MUNI_HH'] == '21') {
        if ($dataAffluenzaRilevazioneSingola['MUNI_NUM'] <> '21120') { // Voti per corrispondenza
            $dataAffluenzaAr[] = $dataAffluenzaRilevazioneSingola;
            $affluenzaTotaleHA['ele_m'] = 0;
            $affluenzaTotaleHA['ele_f'] = 0;;
            $affluenzaTotaleHA['ele_t'] += $dataAffluenzaRilevazioneSingola['MUNI_RIGHT_T'];
            $affluenzaTotaleHA['vot_m'] += $dataAffluenzaRilevazioneSingola['MUNI_VOTERS_M'];
            $affluenzaTotaleHA['vot_f'] += $dataAffluenzaRilevazioneSingola['MUNI_VOTERS_F'];
            $affluenzaTotaleHA['vot_t'] += $dataAffluenzaRilevazioneSingola['MUNI_VOTERS_T'];
            $affluenzaTotaleHA['vot_f'] += $dataAffluenzaRilevazioneSingola['MUNI_SEC'];
            $affluenzaTotaleHA['sz_tot'] += $dataAffluenzaRilevazioneSingola['MUNI_SECT'];
            $affluenzaTotaleHA['sz_perv'] += $dataAffluenzaRilevazioneSingola['MUNI_SECP'];
            $affluenzaTotaleHA['sz_pres'] += $dataAffluenzaRilevazioneSingola['MUNI_SECP'];
    
        }
    }
}    


/**
 * trasformazione in array associativo Affluenza.
 * si accede ai dati dell'affluenza del comune tramite indice Codice Istat 
 */
foreach ($dataAffluenzaAr as $comuneAffluenza) {
    echo $comuneAffluenza['MUNI_NUM']. ' - '.$comuneAffluenza['MUNI_DESC_I'].' - '.$comuneAffluenza['MUNI_SECP'];
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
    $comuneAffluenza['desc_prov_DE'] = DESC_PROV_DE;
    $comuneAffluenza['desc_prov_LAD'] = DESC_PROV_LAD;    
    $comuneAffluenza['cod_ISTAT'] = $CodIstatComune;
//    $comuneAffluenza['cod_comune_originale'] = $comuneAffluenza['COMUNEISTAT'];
    $dataAffluenzaHA[$CodIstatComune] = $comuneAffluenza;
//    $affluenzaTotale += $comuneAffluenza['MUNI_VOTERS_T'];

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
$numListaTmp = 0;
foreach ($dataPicCandidatiAr as $singolCandPic) {
    if ($singolCandPic['LIST_NUM'] > $numListaTmp +1) {
        $numListaTmp++;
    }
    $dataPicCandListaAr[$numListaTmp][] = $singolCandPic;
} 

/**
 * Lettura Liste e loghi
 * Lettura da locale
 */
$fileNameLoghiListe = './dati_scaricati/'.'partiti_foto.csv'; 
$listaLoghiAr = array(); 
$dataNameLoghiListeAr = FileManagement::csv_to_array($fileNameLoghiListe,$log,";",false);
if (!$dataNameLoghiListeAr) {
	$log->logFatal('Impossibile proseguire. Impossibile recuperare il file'. $fileNameLoghiListe);
	die();
}
 

/**
 * Lettura voti Liste
 * Lettura da remoto
 */
$fileNameVotiListe = REMOTE_SITE_BOLZANO.'VOTILISTA_LISTENSTIMMEN.CSV'; 
 
//$fileNameVotiListe = REMOTE_SITE_BOLZANO.'/'.'VOTILISTE-SUM.CSV'; 
//$dataVotiListeAr = FileManagement::csv_to_array($fileNameVotiListe,$log,"\t",false);
$dataVotiListeAr = FileManagement::csv_to_array($fileNameVotiListe,$log,";",false);
if (!$dataVotiListeAr) {
	$log->logFatal('Impossibile proseguire. Impossibile recuperare il file'. $fileNameVotiListe);
	die();
}

/**
 * trasformazione in array associativo VotiListe.
 * si accede ai dati dei voti delle liste tramite indice codice comune + ordine lista  
 */
$ordineLista = '0';
$comuneIstatTmp = 0;
$votiValidiAr = array();
$votiValidi = 0;
$votiValidiProv = 0;
$i = 0;
$totRighe = count($dataVotiListeAr); 
foreach ($dataVotiListeAr as $dataVotiSingolaLista) {
    $muni_num = trim($dataVotiSingolaLista["MUNI_NUM"]);
    $i++;
	if ($comuneIstatTmp <> $dataVotiSingolaLista['MUNI_NUM']) {
        $votiValidiAr[$comuneIstatTmp] = $votiValidi;
        $votiValidiProv = $votiValidiProv + $votiValidi;
        $comuneIstatTmp = $dataVotiSingolaLista['MUNI_NUM'];
        $dataVotiSingolaLista['voti_validi'] = 0;
        $votiValidi = 0;
        $ordineLista = 0;
//        $votiValidiAr[$comuneIstatTmp][$ordineLista] = 0;
    }

    if ($dataVotiSingolaLista['LIST_NUM'] != '') {
        $ordineLista = $dataVotiSingolaLista['LIST_NUM'];
        if ($dataVotiSingolaLista['LIST_VOTES'] > 0) {
            $votiValidi = $votiValidi + $dataVotiSingolaLista['LIST_VOTES']; 
        }
        $dataVotiSingolaLista['img_lis_r'] = iconv('UTF-8', 'UTF-8//IGNORE',$dataNameLoghiListeAr[$ordineLista-1]['LIST_PICTURE']);
        $dataVotiListeHA[$comuneIstatTmp][$ordineLista] = $dataVotiSingolaLista;
        if ($i == $totRighe) {
            $votiValidiProv = $votiValidiProv + $votiValidi;
        }
    } 
}
    // cicla di nuovo per scrivere numero voti validi
    foreach ($dataVotiListeAr as $dataVotiSingolaLista) {
        $muni_num = trim($dataVotiSingolaLista["MUNI_NUM"]);
        if ($comuneIstatTmp <> $dataVotiSingolaLista['MUNI_NUM']) {
            $comuneIstatTmp = $dataVotiSingolaLista['MUNI_NUM'];
            $ordineLista = 0;
        }
    
        if ($dataVotiSingolaLista['LIST_NUM'] != '') {
            $ordineLista = $dataVotiSingolaLista['LIST_NUM'];
            $dataVotiSingolaLista['voti_validi'] = $votiValidiAr[$comuneIstatTmp];
            $dataVotiSingolaLista['voti_validi_prov'] = $votiValidiProv;
            $dataVotiSingolaLista['img_lis_r'] = iconv('UTF-8', 'UTF-8//IGNORE',$dataNameLoghiListeAr[$ordineLista-1]['LIST_PICTURE']);
            $dataVotiListeHA[$comuneIstatTmp][$ordineLista] = $dataVotiSingolaLista;
        } 
    
}

/**
 * Lettura preferenze ai candidati collegati alle liste
 */
$fileNameVotiPreferenze = REMOTE_SITE_BOLZANO.'PREFERENZE_VORZUGSSTIMMEN.CSV'; 
//$dataVotiPreferenzeAr = FileManagement::csv_to_array($fileNameVotiPreferenze,$log,"\t",false);
$dataVotiPreferenzeAr = FileManagement::csv_to_array($fileNameVotiPreferenze,$log,";",false);
if (!$dataVotiPreferenzeAr) {
	$log->logFatal('Impossibile proseguire. Impossibile recuperare il file'. $fileNameVotiListe);
	die();
}

/**
 * trasformazione in array associativo VotiPreferenze.
 * si accede ai dati dei voti dei candidati tramite indice codice comune + ordine lista  
 */
$ordineLista = '0';
$ordineCand = 0;
$comuneIstatTmp = null;
foreach ($dataVotiPreferenzeAr as $dataVotiPreferenzeSingolaAr) {
//    echo 'preferenze comune: '.$dataVotiPreferenzeSingolaAr['MUNI_NUM'] .' - '.$comuneIstatTmp.' - '.$dataVotiPreferenzeSingolaAr['LIST_NUM'].PHP_EOL;

	if ($comuneIstatTmp <> $dataVotiPreferenzeSingolaAr['MUNI_NUM']) {
        $comuneIstatTmp = $dataVotiPreferenzeSingolaAr['MUNI_NUM'];
        $ordineLista = $dataVotiPreferenzeSingolaAr['LIST_NUM'];
        $ordineCand = -1;
    }


    if ($dataVotiPreferenzeSingolaAr['LIST_NUM'] != '' && $dataVotiPreferenzeSingolaAr['LIST_NUM'] == $ordineLista) {
        $ordineCand++;
        $dataVotiPreferenzeSingolaAr['img_lis_c'] = iconv('UTF-8', 'UTF-8//IGNORE', $dataPicCandListaAr[$ordineLista-1][$ordineCand]['CAND_PICTURE']);
        $dataVotiPreferenzeHA[$comuneIstatTmp][$ordineLista][$ordineCand] = $dataVotiPreferenzeSingolaAr;
//        $dataVotiPreferenzeHA[$comuneIstatTmp][$ordineLista][$ordineCand]['img_lis_r'] = $dataNameLoghiListeAr[$ordineLista]['LIST_PICTURE'];
    } else {
        $ordineLista = $dataVotiPreferenzeSingolaAr['LIST_NUM'];
//        $dataVotiPreferenzeHA[$comuneIstatTmp][$ordineLista][$ordineCand]['img_lis_r'] = $dataNameLoghiListeAr[$ordineLista]['LIST_PICTURE'];
        $ordineCand = 0;
        $dataVotiPreferenzeSingolaAr['img_lis_c'] = iconv('UTF-8', 'UTF-8//IGNORE', $dataPicCandListaAr[$ordineLista-1][$ordineCand]['CAND_PICTURE']);
        $dataVotiPreferenzeHA[$comuneIstatTmp][$ordineLista][$ordineCand] = $dataVotiPreferenzeSingolaAr;
    } 
//    echo 'preferenze comune: '.(int)$comuneIstatTmp.PHP_EOL;

}

/**
 * Creazione oggetto enti che vanno al voto x json
 * modello Ministero dell'Interno
 */

$comuneInCorso = '';
$objectEnte = new enti();
$tot_com = 0;
/**
 * 
 * Cicla Voti Lista
 * crea nuovo oggetto per ogni comune
 * Imposta dati generali (parte in new scrutinio, parte in setCandidato. Alcuni dati generali sono nel file dei voti del sindaco)
 * Imposta Voti lista per ogni sindaco in setVotiListeCandidato
 * 
 * NOTA: in setCandidato viene impostata la lista
 *       in setVotiListeCandidato vengono impostati i risultati dei candidati  
 */
foreach ($dataVotiListeHA as $singoloComuneListe) {

    foreach ($singoloComuneListe as $singolaLista) {
        $codComIstatString = $singolaLista['MUNI_NUM'];
        if (($singolaLista ['MUNI_NUM'] == $comuneInCorso) && isset($objectComune)) {
            $objectComune->numeroCandidato = $objectComune->numeroCandidato + 1;
            $objectComune->setCandidato($singolaLista);
            $candidatiListaComuneAr = $dataVotiPreferenzeHA[$codComIstatString];
            $objectComune->setVotiListeCandidato($candidatiListaComuneAr);

            // Aggiorna provincia
            $objectProvincia->numeroCandidatoProvincia = $objectProvincia->numeroCandidatoProvincia + 1;
            $datiAffluenzaComuneInCorso = $dataAffluenzaHA[$comuneInCorso];
            $objectProvincia->setCandidatoProvincia($singolaLista, $datiAffluenzaComuneInCorso);
            $objectProvincia->setVotiListeCandidatoProvincia($candidatiListaComuneAr, $comuneInCorso); 


        } else {
            if (isset($objectComune)) { //->jsonObject->desc_com)) {
                // scrive file
                $cod_com = $objectComune->jsonObject->int->cod_com;

            // Ordina l'array di oggetti secondo la proprietà "voti"
                $cand = $objectComune->jsonObject->cand;
                usort($cand, 'confrontaVoti');
                $objectComune->jsonObject->cand = $cand;
                                
                $objectComune->OrdinaListe(); 

            /**
             *  Scrittura file italiano
             */    
                $file2write = $file2write_part.$cod_com.'/response.json';
    //			$file2write = $file2write_part.$comuneInCorso.'response.json';
                FileManagement::save_object_to_json($objectComune->jsonObject,$file2write,$log); 

                //Upload file to dl
                if (MAKE_UPLOAD) {
                    FileManagement::upload_to_dl($file2write, $url=UPLOAD_URL, $cod_prov, $cod_com, $log);	
                }

            /**
             *  Scrittura file tedesco (suffisso de)
             */    
                $objectComune->jsonObject->int->desc_prov_it = $objectComune->jsonObject->int->desc_prov;
                $objectComune->jsonObject->int->desc_prov = $objectComune->jsonObject->int->desc_prov_DE;
                $objectComune->jsonObject->int->desc_com_it = $objectComune->jsonObject->int->desc_com;
                $objectComune->jsonObject->int->desc_com = $objectComune->jsonObject->int->desc_com_DE;                
                $file2write = $file2write_part.$cod_com.'/response_de.json';
    //			$file2write = $file2write_part.$comuneInCorso.'response.json';
                FileManagement::save_object_to_json($objectComune->jsonObject,$file2write,$log); 

                //Upload file to dl
                if (MAKE_UPLOAD) {
                    FileManagement::upload_to_dl($file2write, $url=UPLOAD_URL, $cod_prov, $cod_com, $log);	
                }

            /**
             *  Scrittura file ladino (suffisso la)
             */    
                $objectComune->jsonObject->int->desc_prov_it = $objectComune->jsonObject->int->desc_prov;
                $objectComune->jsonObject->int->desc_prov = $objectComune->jsonObject->int->desc_prov_LAD;
//                $objectComune->jsonObject->int->desc_com_it = $objectComune->jsonObject->int->desc_com;
                $objectComune->jsonObject->int->desc_com = $objectComune->jsonObject->int->desc_com_LAD;                
                $file2write = $file2write_part.$cod_com.'/response_la.json';
    //			$file2write = $file2write_part.$comuneInCorso.'response.json';
                FileManagement::save_object_to_json($objectComune->jsonObject,$file2write,$log); 

                //Upload file to dl
                if (MAKE_UPLOAD) {
                    FileManagement::upload_to_dl($file2write, $url=UPLOAD_URL, $cod_prov, $cod_com, $log);	
                }
                
                echo $tot_com . ': '.$objectComune->jsonObject->int->cod_com.' - '. $cod_com. ' - '. $CodIstatComune . ' - '. $objectComune->jsonObject->int->desc_com . PHP_EOL;
    
                //Aggiunge comune a Ente
                $objectEnte->setComune($objectComune->jsonObject);
    
                // distrugge oggetto
                unset($objectComune);
                $objectProvincia->numeroCandidatoProvincia = 0;

            }
            $comuneInCorso = $singolaLista ['MUNI_NUM'];
         
            /**
             * crea oggetto del comune
             */  
    
            $objectComune = new scrutinio($dataAffluenzaHA[$comuneInCorso]); 
            $tot_com++;
            // Aggiungi candidato
            $objectComune->setCandidato($singolaLista);
    
            // Aggiunge voti di lista per ogni candidato
            $candidatiListaComuneAr = $dataVotiPreferenzeHA[$codComIstatString];
            $objectComune->setVotiListeCandidato($candidatiListaComuneAr);

            // Aggiorna i dati della provincia
            if (!isset($objectProvincia)) {
                $objectProvincia = new scrutinioProvincia($dataAffluenzaHA[$comuneInCorso],$affluenzaTotaleHA);
            } 
            $objectProvincia->setCandidatoProvincia($singolaLista, $dataAffluenzaHA[$comuneInCorso]);
            $objectProvincia->setVotiListeCandidatoProvincia($candidatiListaComuneAr, $comuneInCorso);
    
        }


    }


	}

/* Scrive ultimo comune
*/
if (isset($objectComune)) { //->jsonObject->desc_com)) {
    // scrive file
    $cod_com = $objectComune->jsonObject->int->cod_com;

    $cand = $objectComune->jsonObject->cand;
    // Ordina l'array di oggetti secondo la proprietà "voti"
    usort($cand, 'confrontaVoti');
    $objectComune->jsonObject->cand = $cand;

    $objectComune->OrdinaListe(); 
    
    $file2write = $file2write_part.$cod_com.'/response.json';
//			$file2write = $file2write_part.$comuneInCorso.'response.json';
    FileManagement::save_object_to_json($objectComune->jsonObject,$file2write,$log); 

    //Upload file to dl
    if (MAKE_UPLOAD) {
        FileManagement::upload_to_dl($file2write, $url=UPLOAD_URL, $cod_prov, $cod_com, $log);	
    }

    /**
     *  Scrittura file tedesco (suffisso de)
     */    
        $objectComune->jsonObject->int->desc_prov_it = $objectComune->jsonObject->int->desc_prov;
        $objectComune->jsonObject->int->desc_prov = $objectComune->jsonObject->int->desc_prov_DE;
        $objectComune->jsonObject->int->desc_com_it = $objectComune->jsonObject->int->desc_com;
        $objectComune->jsonObject->int->desc_com = $objectComune->jsonObject->int->desc_com_DE;                
        $file2write = $file2write_part.$cod_com.'/response_de.json';
//			$file2write = $file2write_part.$comuneInCorso.'response.json';
        FileManagement::save_object_to_json($objectComune->jsonObject,$file2write,$log); 

        //Upload file to dl
        if (MAKE_UPLOAD) {
            FileManagement::upload_to_dl($file2write, $url=UPLOAD_URL, $cod_prov, $cod_com, $log);	
        }

    /**
     *  Scrittura file ladino (suffisso la)
     */    
        $objectComune->jsonObject->int->desc_prov_it = $objectComune->jsonObject->int->desc_prov;
        $objectComune->jsonObject->int->desc_prov = $objectComune->jsonObject->int->desc_prov_LAD;
        $objectComune->jsonObject->int->desc_com_it = $objectComune->jsonObject->int->desc_com;
        $objectComune->jsonObject->int->desc_com = $objectComune->jsonObject->int->desc_com_LAD;                
        $file2write = $file2write_part.$cod_com.'/response_la.json';
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

    // Ordina l'array di oggetti secondo la proprietà "voti"
	$cand = $objectProvincia->jsonObject->cand;
	usort($cand, 'confrontaVoti');
	$objectProvincia->jsonObject->cand = $cand;

    $objectProvincia->OrdinaListe();

    FileManagement::save_object_to_json($objectProvincia->jsonObject,$file2write,$log); 

	//Upload file to dl
	if (MAKE_UPLOAD) {
		//FileManagement::upload_to_dl($file2write, $url=UPLOAD_URL, REG_STO, $cod_com, $log);	
		FileManagement::upload_generic_to_dl($file2write, $log, $upload_path=DL_PATH_PROVINCIA, $url=UPLOAD_URL);	
	}

	/**
     *  scrive file tedesco (suffisso de)
     * */ 
	$file2write = $file2write_provincia_part.'/response_de.json';

    $objectProvincia->jsonObject->int->desc_reg_it = $objectProvincia->jsonObject->int->desc_reg;
    $objectProvincia->jsonObject->int->desc_reg = $objectProvincia->jsonObject->int->desc_reg_DE;

    FileManagement::save_object_to_json($objectProvincia->jsonObject,$file2write,$log); 

	//Upload file to dl
	if (MAKE_UPLOAD) {
//		FileManagement::upload_to_dl($file2write, $url=UPLOAD_URL, REG_STO, $cod_com, $log);	
		FileManagement::upload_generic_to_dl($file2write, $log, $upload_path=DL_PATH_PROVINCIA, $url=UPLOAD_URL);	
	}

	/**
     *  scrive file ladino (suffisso la)
     * */ 
	$file2write = $file2write_provincia_part.'/response_la.json';

    $objectProvincia->jsonObject->int->desc_reg_it = $objectProvincia->jsonObject->int->desc_reg;
    $objectProvincia->jsonObject->int->desc_reg = $objectProvincia->jsonObject->int->desc_reg_LAD;

    FileManagement::save_object_to_json($objectProvincia->jsonObject,$file2write,$log); 

	//Upload file to dl
	if (MAKE_UPLOAD) {
//		FileManagement::upload_to_dl($file2write, $url=UPLOAD_URL, REG_STO, $cod_com, $log);	
		FileManagement::upload_generic_to_dl($file2write, $log, $upload_path=DL_PATH_PROVINCIA, $url=UPLOAD_URL);	
	}

    echo $tot_com . ': '.$objectProvincia->jsonObject->int->cod_pro.' - '. $cod_com. ' - '. $CodIstatComune . ' - '. $objectComune->jsonObject->int->desc_com . PHP_EOL;

	// distrugge oggetto
	unset($objectProvincia);
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

// Funzione di confronto per l'ordinamento
function confrontaVoti($a, $b) {
    if ($a->voti == $b->voti) {
        return 0;
    }
//    return ($a->voti < $b->voti) ? -1 : 1; // ascendente
	return ($a->voti < $b->voti) ? 1 : -1; // discendente
}


echo "<h2>Conversione della provincia di Bolzano terminata con successo</h2>".PHP_EOL;