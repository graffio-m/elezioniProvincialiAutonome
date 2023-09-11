<?php

/**
 * @abstract Classe scrutinio. Gestisce l'oggetto relativo dello scrutinio di ogni comune
 */

class scrutinio {

    public $jsonObject;
    public $numeroCandidato = 0;
    public $numeroLista = 0;

    public function __construct($dataAffluenzaAR) {

        $this->jsonObject = new stdClass();
        $this->jsonObject->int = new stdClass();
        $this->jsonObject->int->st = STATO;
        $this->jsonObject->int->t_ele = 'Comunali';
        $this->jsonObject->int->f_ele = 'SCRUTINI';
        $this->jsonObject->int->dt_ele = DATA_ELEZIONI;
        $this->jsonObject->int->l_terr = 'COMUNE';
        $this->jsonObject->int->area = 'I';

        switch ($dataAffluenzaAR['desc_prov']) {

            case 'TRENTO':

                $nomeComune = $dataAffluenzaAR['Nome Comune'];
                $this->jsonObject->int->desc_com = strtoupper($nomeComune);
                $this->jsonObject->int->cod_com = $dataAffluenzaAR['cod_com'];;
                $this->jsonObject->int->desc_prov = $dataAffluenzaAR['desc_prov'];
                $this->jsonObject->int->cod_prov = COD_PROV; 
                $this->jsonObject->int->cod_ISTAT = $dataAffluenzaAR['Istat Comune'];
                $this->jsonObject->int->ele_m = $dataAffluenzaAR['ElettoriM'];
                $this->jsonObject->int->ele_f = $dataAffluenzaAR['ElettoriF'];
                $this->jsonObject->int->ele_t = $dataAffluenzaAR['ElettoriT'];
 
                $this->jsonObject->int->vot_m = $dataAffluenzaAR['VotantiF'];
                $this->jsonObject->int->vot_f = $dataAffluenzaAR['VotantiF'];
                $this->jsonObject->int->vot_t = $dataAffluenzaAR['VotantiT'];

                $this->jsonObject->int->dt_agg = date("YmdHis");
            break;
            case 'BOLZANO':

//                $nomeComune = $dataAffluenzaAR['DESCRIZIONEISTAT_I'].'/'.$dataAffluenzaAR['DESCRIZIONEISTAT_D'].'/'.$dataAffluenzaAR['DESCRIZIONEISTAT_L'];
                $nomeComune = $dataAffluenzaAR['MUNI_DESC_I'];
                $nomeComune_DE = $dataAffluenzaAR['MUNI_DESC_D'];
                $nomeComune_LAD = $dataAffluenzaAR['MUNI_DESC_L'];
                $this->jsonObject->int->desc_com = strtoupper($nomeComune);
                $this->jsonObject->int->desc_com_DE = strtoupper($nomeComune_DE);
                $this->jsonObject->int->desc_com_LAD = strtoupper($nomeComune_LAD);

                $this->jsonObject->int->cod_com = $dataAffluenzaAR['MUNI_NUM'];;
                $this->jsonObject->int->desc_prov = $dataAffluenzaAR['desc_prov'];
                $this->jsonObject->int->desc_prov_DE = DESC_PROV_DE;
                $this->jsonObject->int->desc_prov_LAD = DESC_PROV_LAD;                
                $this->jsonObject->int->cod_prov = COD_PROV; 
                $this->jsonObject->int->cod_ISTAT = $dataAffluenzaAR['cod_ISTAT'];
                $this->jsonObject->int->cod_comune_originale = $dataAffluenzaAR['NUMI_NUM'];

                $this->jsonObject->int->ele_m = 0;
                $this->jsonObject->int->ele_f = 0;;
                $this->jsonObject->int->ele_t = $dataAffluenzaAR['MUNI_RIGHT_T'];
 
                $this->jsonObject->int->vot_m = $dataAffluenzaAR['MUNI_VOTERS_M'];
                $this->jsonObject->int->vot_f = $dataAffluenzaAR['MUNI_VOTERS_F'];
                $this->jsonObject->int->vot_t = $dataAffluenzaAR['MUNI_VOTERS_T'];

                $this->jsonObject->int->sz_tot = $dataAffluenzaAR['MUNI_SECT'];
                $this->jsonObject->int->sz_pres = $dataAffluenzaAR['MUNI_SECP'];
                $this->jsonObject->int->sz_perv = $dataAffluenzaAR['MUNI_SECP'];
                $this->jsonObject->int->sk_bianche = 0;
                $this->jsonObject->int->sk_nulle = 0;

                $percVoti = 0;
                if ($this->jsonObject->int->ele_t > 0 && $this->jsonObject->int->vot_t > 0) {
                    $percVoti = round((($this->jsonObject->int->vot_t/$this->jsonObject->int->ele_t)*100),2);
                }
                $this->jsonObject->int->perc_vot = $percVoti;
    
                $this->jsonObject->int->dt_agg = date("YmdHis");
            break;

        }
        $this->jsonObject->cand = array();

    }

    public function __destruct() {
        unset($this->jsonObject);

        
    }    
    /**
     * @abstract Imposta i dati delle liste collegate al candidato Presidente. chiama le funzioni di impostazioni specifiche di ogni regione/provincia
     *
     * @param array $dataVotiListeAr
     * @return void
     */
    public function setVotiListeCandidato($dataVotiListeAr) {
        $idPresidente = $this->jsonObject->cand[$this->numeroCandidato]->id_Presidente;
        if (!isset($this->jsonObject->cand[$this->numeroCandidato]->liste)) {
            $this->jsonObject->cand[$this->numeroCandidato]->liste = array();
        }

        switch ($this->jsonObject->int->desc_prov) {
            case 'TRENTO':
                $this->setVotiListeCandidatoTrento($dataVotiListeAr);
                break;
            case 'BOLZANO':
                $this->setVotiListeCandidatoBolzano($dataVotiListeAr);
                break;
        }

    }
    /**
     * @abstract Imposta i dati del candidato Presidente. chiama le funzioni di impostazioni specifiche di ogni regione/provincia
     *
     * @param array $candidatoAr
     * @return void
     */
    public function setCandidato($candidatoAr) {
        if (!array_key_exists($this->numeroCandidato, $this->jsonObject->cand)) {
            $this->jsonObject->cand[$this->numeroCandidato] = new stdClass();
            $this->numeroLista = 0;
        }
        switch ($this->jsonObject->int->desc_prov) {
            case 'TRENTO':
                $this->setCandidatoTrento($candidatoAr);
                break;
            case 'BOLZANO':
                $this->setCandidatoBolzano($candidatoAr);
                break;

        }

    }
    /**
     * Imposta i dati delle liste collegate al candidato Presidente
     * Trento
     *
     * @param array $dataVotiListeHA
     * @return void
     */

    public function setVotiListeCandidatoTrento($dataVotiListeAr) {
        $idPresidente = $this->jsonObject->cand[$this->numeroCandidato]->id_Presidente;
        $presidenteInCorso = null;
        $dataVotiListeInCorsoAr = array();
        $numeroCandidato = -1;
        /**
         * Ciclare $dataVotiListeHA[$idPresidente]
         * 
         * 
         * RIPRENDERE DA QUI
         */

        foreach ($dataVotiListeAr as $singolaLista) {
            if ($this->jsonObject->int->cod_ISTAT == $singolaLista['Istat Comune']) {
                $dataVotiListeInCorsoAr[] = $singolaLista;
            }
        } 
        if (count($dataVotiListeInCorsoAr) > 0 ) {
            
            Ordinamenti::OrdinaPerChiaveValore($dataVotiListeInCorsoAr, 'Presidente Id');
            //$this->OrdinaPerChiaveValore($dataVotiListeInCorsoAr);
            $singolaLista = [];

        }
         foreach ($dataVotiListeInCorsoAr as $singolaLista) {
            if ($this->jsonObject->cand[$this->numeroCandidato]->id_Presidente == $singolaLista['Presidente Id']) {

                if ($this->jsonObject->int->cod_ISTAT == $singolaLista['Istat Comune']) {

                    if (!array_key_exists($this->numeroLista, $this->jsonObject->cand[$this->numeroCandidato]->liste)) {
                        $this->jsonObject->cand[$this->numeroCandidato]->liste[$this->numeroLista] = new stdClass();
    
                    }
                    $this->jsonObject->cand[$this->numeroCandidato]->liste[$this->numeroLista]->desc_lis_c = $singolaLista['Nome Lista']; 
                    $this->jsonObject->cand[$this->numeroCandidato]->liste[$this->numeroLista]->voti = $singolaLista['Voti']; 
                    $this->jsonObject->cand[$this->numeroCandidato]->liste[$this->numeroLista]->pos = $singolaLista['Progressivo Lista']; 
    
                    $percVotiLista = 0;
                    if ($singolaLista['Voti'] > 0 && $this->jsonObject->int->vot_t > 0) {
                        $percVotiLista = round((($singolaLista['Voti']/$this->jsonObject->int->vot_t)*100),2);
                    }
                    $this->jsonObject->cand[$this->numeroCandidato]->liste[$this->numeroLista]->perc = $percVotiLista; 
    
                    $this->jsonObject->cand[$this->numeroCandidato]->liste[$this->numeroLista]->img_lis_c = '';                
                    $this->jsonObject->cand[$this->numeroCandidato]->liste[$this->numeroLista]->seggi = 0; 
                    $this->jsonObject->cand[$this->numeroCandidato]->liste[$this->numeroLista]->sort_lis = 0; 
                    $this->jsonObject->cand[$this->numeroCandidato]->liste[$this->numeroLista]->id_presidente = $singolaLista['Presidente Id'];
    
    
                    $this->numeroLista++;
                }
            }
    
        }

    } 

    public function OrdinaPerChiaveValore($dataVotiListeInCorsoAr) {
        usort($dataVotiListeInCorsoAr, function($a,$b) {
            return strcmp($a['Presidente Id'], $b['Presidente Id']);
/*             if ($a['Presidente Id'] == $b['Presidente Id']) {
                return 0;
            }
            return ($a['Presidente Id'] < $b['Presidente Id']) ? -1 : 1;
 */        });
   
    }

    /**
     * Imposta i dati delle liste collegate al candidato Presidente
     * Bolzano
     *
     * @param array $dataVotiListeHA indice: codice comune (versione della provincia di Bolzano) + ordine candidatura ($idPresidente) 
     * @return void
     */
    public function setVotiListeCandidatoBolzano($dataVotiListeAr) {
        // $idPresidente = $this->jsonObject->cand[$this->numeroCandidato]->id_Presidente;
        $codComTmp = $this->jsonObject->int->cod_comune_originale;

        /**
         * Ciclare 
         */
        $posCandidato = $this->jsonObject->cand[$this->numeroCandidato]->pos;

        $codComTmp =  $this->jsonObject->int->cod_com;
        foreach ($dataVotiListeAr as $singolaLista) {
            foreach ($singolaLista as $singoloCand) {


                if ($posCandidato == $singoloCand['LIST_NUM']) {
                    if (!array_key_exists($this->numeroLista, $this->jsonObject->cand[$this->numeroCandidato]->liste)) {
                        $this->jsonObject->cand[$this->numeroCandidato]->liste[$this->numeroLista] = new stdClass();
                    }
                    $this->jsonObject->cand[$this->numeroCandidato]->liste[$this->numeroLista]->desc_lis_c = $singoloCand['CAND_NAME']; 
                    $this->jsonObject->cand[$this->numeroCandidato]->liste[$this->numeroLista]->voti = $singoloCand['CAND_VOTES']; 
                    $this->jsonObject->cand[$this->numeroCandidato]->liste[$this->numeroLista]->pos = $singoloCand['LIST_NUM']; 
                    
                    $percVotiLista = 0;
                    if ($singoloCand['CAND_VOTES'] > 0 && $this->jsonObject->int->vot_t > 0) {
                        $percVotiLista = round((($singoloCand['CAND_VOTES']/$this->jsonObject->int->vot_t)*100),2);
                    }
                    $this->jsonObject->cand[$this->numeroCandidato]->liste[$this->numeroLista]->perc = $percVotiLista; 

                    $this->jsonObject->cand[$this->numeroCandidato]->liste[$this->numeroLista]->img_lis = '';                
                    $this->jsonObject->cand[$this->numeroCandidato]->liste[$this->numeroLista]->seggi = 0; 
                    $this->jsonObject->cand[$this->numeroCandidato]->liste[$this->numeroLista]->sort_lis = 0; 
                    $this->numeroLista++;

                }
            }
        }

    } 

    /**
     * Imposta i dati delle liste (lo fa in setCandidatoBolzano per coerenza con gli script di visualizzazione della pagina web)
     * Bolzano
     *
     * @param array $candidatoAr
     * @return void
     */
    public function setCandidatoBolzano($candidatoAr) {

        $this->jsonObject->cand[$this->numeroCandidato]->cogn = $candidatoAr['LIST_NAME']; 
        $this->jsonObject->cand[$this->numeroCandidato]->nome = ''; 
        $this->jsonObject->cand[$this->numeroCandidato]->a_nome = ''; 
        $this->jsonObject->cand[$this->numeroCandidato]->pos = $candidatoAr['LIST_NUM']; 
        $this->jsonObject->cand[$this->numeroCandidato]->voti = $candidatoAr['LIST_VOTES']; 
        $this->jsonObject->cand[$this->numeroCandidato]->id_Presidente = $candidatoAr['ORDINECANDIDATURA']; 
        $this->jsonObject->cand[$this->numeroCandidato]->img_lis_r = $candidatoAr['img_lis_r'];


        $percVoti = 0;
        $votiValidi = $this->jsonObject->int->vot_t - ($candidatoAr['MUNI_BLANK'] + $candidatoAr['MUNI_NULLS']);
        if ($candidatoAr['LIST_VOTES'] > 0 && $this->jsonObject->int->vot_t > 0) {
            $percVoti = round((($candidatoAr['LIST_VOTES']/$votiValidi)*100),2);
        }
        $this->jsonObject->cand[$this->numeroCandidato]->perc = $percVoti; 
        $this->jsonObject->cand[$this->numeroCandidato]->d_nasc = ''; 
        $this->jsonObject->cand[$this->numeroCandidato]->l_nasc = ''; 
        $this->jsonObject->cand[$this->numeroCandidato]->eletto = ''; 
        $this->jsonObject->cand[$this->numeroCandidato]->sg_ass = 0; 
        $this->jsonObject->cand[$this->numeroCandidato]->sort_coal = null; 
        $this->jsonObject->cand[$this->numeroCandidato]->sg_sort_coal = null; 

        /** Duplicato di voti e perc
         */
        $this->jsonObject->cand[$this->numeroCandidato]->tot_vot_lis = $candidatoAr['LIST_VOTES']; 
        $this->jsonObject->cand[$this->numeroCandidato]->perc_lis = $percVoti; 


        /**
         *  dati generali
         *  sono ripetuti nel record di ogni candidato Presidente
         */
        $this->jsonObject->int->sk_bianche = $candidatoAr['MUNI_BLANK'];
        $this->jsonObject->int->sk_nulle = $candidatoAr['MUNI_NULLS'];

        if (!isset($this->jsonObject->int->sz_tot)) {
            $this->jsonObject->int->sz_tot = $candidatoAr['MUNI_SECT'];
            $this->jsonObject->int->sz_p_sind = $candidatoAr['MUNI_SECT'];
            $this->jsonObject->int->sz_p_cons = $candidatoAr['NUMSEZPERVENUTE'];
            $this->jsonObject->int->sz_perv = $candidatoAr['NUMSEZPERVENUTE'];
            $this->jsonObject->int->sk_contestate = 0;

            $this->jsonObject->int->fine_rip = '';
            $this->jsonObject->int->sg_spett = 0;
            $this->jsonObject->int->sg_ass = 0;
            $this->jsonObject->int->tot_vot_cand = 0;
            $this->jsonObject->int->tot_vot_lis = 0;
            $this->jsonObject->int->non_valid = '';
            $this->jsonObject->int->data_prec_elez = '';
            $this->jsonObject->int->reg_sto = REG_STO;
            $this->jsonObject->int->prov_sto = $this->jsonObject->int->cod_prov;
            $this->jsonObject->int->comu_sto = $this->jsonObject->int->cod_com;

        }
    }
    /**
     * Imposta i dati del candidato Presidente
     * Trento
     *
     * @param array $candidatoAr
     * @return void
     */

    public function setCandidatoTrento($candidatoAr) {
        $this->jsonObject->cand[$this->numeroCandidato]->cogn = $candidatoAr['Cognome']; 
        $this->jsonObject->cand[$this->numeroCandidato]->nome = $candidatoAr['Nome']; 
        $this->jsonObject->cand[$this->numeroCandidato]->a_nome = $candidatoAr['Nome Detto']; 
        $this->jsonObject->cand[$this->numeroCandidato]->pos = $candidatoAr['Progressivo Presidente']; 
        $this->jsonObject->cand[$this->numeroCandidato]->voti = $candidatoAr['Voti']; 
        $this->jsonObject->cand[$this->numeroCandidato]->id_Presidente = $candidatoAr['Presidente Id']; 

        ;

        $percVoti = 0;
        $votiValidi = $this->jsonObject->int->vot_t - ($candidatoAr['Schede Bianche'] + $candidatoAr['Schede nulle o contenenti solo voti nulli'] + $candidatoAr['Schede contestate e non attribuite']);

        if ($candidatoAr['Voti'] > 0 && $this->jsonObject->int->vot_t > 0) {
            $percVoti = round((($candidatoAr['Voti']/$votiValidi)*100),2);
        }
        $this->jsonObject->cand[$this->numeroCandidato]->perc = $percVoti; 
        $this->jsonObject->cand[$this->numeroCandidato]->d_nasc = ''; 
        $this->jsonObject->cand[$this->numeroCandidato]->l_nasc = ''; 
        $this->jsonObject->cand[$this->numeroCandidato]->eletto = ''; 
        $this->jsonObject->cand[$this->numeroCandidato]->sg_ass = 0; 
        $this->jsonObject->cand[$this->numeroCandidato]->sort_coal = null; 
        $this->jsonObject->cand[$this->numeroCandidato]->sg_sort_coal = null; 

        /** Duplicato di voti e perc
         */
        $this->jsonObject->cand[$this->numeroCandidato]->tot_vot_lis = $candidatoAr['Voti']; 
        $this->jsonObject->cand[$this->numeroCandidato]->perc_lis = $percVoti; 


        /**
         *  dati generali
         *  A Trento sono ripetuti nel record di ogni candidato Presidente
         */

        if (!isset($this->jsonObject->int->sz_tot)) {
            $this->jsonObject->int->sz_tot = $candidatoAr['Sez.Totali'];
            $this->jsonObject->int->sz_tot = $candidatoAr['Sez.Totali'];
            $this->jsonObject->int->sz_tot = $candidatoAr['Sez.Totali'];
            $this->jsonObject->int->sz_p_sind = $candidatoAr['Sez.Pervenute'];
            $this->jsonObject->int->sz_p_cons = $candidatoAr['Sez.Pervenute'];
            $this->jsonObject->int->sz_perv = $candidatoAr['Sez.Pervenute'];
            $this->jsonObject->int->sk_bianche = $candidatoAr['Schede Bianche'];
            $this->jsonObject->int->sk_nulle = $candidatoAr['Schede nulle o contenenti solo voti nulli'];
            $this->jsonObject->int->sk_contestate = $candidatoAr['Schede contestate e non attribuite'];

            $percVoti = 0;
            if ($this->jsonObject->int->ele_t > 0 && $this->jsonObject->int->vot_t > 0) {
                $percVoti = round((($this->jsonObject->int->vot_t/$this->jsonObject->int->ele_t)*100),2);
            }
            $this->jsonObject->int->perc_vot = $percVoti;
            $this->jsonObject->int->fine_rip = '';
            $this->jsonObject->int->sg_spett = 0;
            $this->jsonObject->int->sg_ass = 0;
            $this->jsonObject->int->tot_vot_cand = 0;
            $this->jsonObject->int->tot_vot_lis = 0;
            $this->jsonObject->int->non_valid = '';
            $this->jsonObject->int->data_prec_elez = '';
            $this->jsonObject->int->reg_sto = REG_STO;
            $this->jsonObject->int->prov_sto = $this->jsonObject->int->cod_prov;
            $this->jsonObject->int->comu_sto = $this->jsonObject->int->cod_com;

        }

    }

    /**
     * 
     */
    public function OrdinaListe() {
        // Ordina gli oggetti utilizzando la funzione di confronto personalizzata per voti discendente
        $numeroCandidato = 0;
        foreach ($this->jsonObject->cand as $singleCand) {
            $singlePref = $singleCand->liste;
            usort($singlePref, function($a,$b) {
                if ($a->voti == $b->voti) {
                    return 0;
                }
                return ($a->voti < $b->voti) ? 1 : -1;
            
            });
            $this->jsonObject->cand[$numeroCandidato]->liste = $singlePref;
            $numeroCandidato ++;
        }
    }    
 
}


/**
 * @abstract Classe Enti. Gestisce l'oggetto relativo agli enti coinvolti nello scrutinio
 */

class enti {

    public $jsonObject;
    public $numeroEnte = 0;

    public function __construct() {

        $this->jsonObject = new stdClass();
        $this->jsonObject->int = new stdClass();
        $this->jsonObject->int->area = 'I';
        $this->jsonObject->int->file = 'GEOPOLITICA COMUNALI';
        $this->jsonObject->int->t_ele = 'Comunali';
        $this->jsonObject->int->dt_ele = DATA_ELEZIONI;

        $this->jsonObject->enti = array();
//        $this->jsonObject->enti = new stdClass();


        switch (DESC_PROV) {
            case 'TRENTO':
                if (!array_key_exists(0, $this->jsonObject->enti)) {
                    $this->jsonObject->enti[0] = new stdClass();
                }
                    //if (!array_key_exists($this->numeroCandidato, $this->jsonObject->cand)) {
                    $this->jsonObject->enti[0]->desc = 'TRENTINO ALTO ADIGE';
                    $this->jsonObject->enti[0]->cod = '040000000000';
                    $this->jsonObject->enti[0]->tipo = 'RE';
                    $this->jsonObject->enti[0]->dt_agg = date("YmdHis");
                    $this->jsonObject->enti[0]->tipo_comune = null;

                    $this->jsonObject->enti[1] = new stdClass();
                    $this->jsonObject->enti[1]->desc = 'PROVINCIA AUTONOMA TRENTO';
                    $this->jsonObject->enti[1]->cod = '040830000000';
                    $this->jsonObject->enti[1]->tipo = 'CR';
                    $this->jsonObject->enti[1]->dt_agg = date("YmdHis");
                    $this->jsonObject->enti[1]->tipo_comune = null;

                    $this->jsonObject->enti[1] = new stdClass();
                    $this->jsonObject->enti[1]->desc = 'PROVINCIA AUTONOMA TRENTO';
                    $this->jsonObject->enti[1]->cod = '040830083000';
                    $this->jsonObject->enti[1]->tipo = 'PR';
                    $this->jsonObject->enti[1]->dt_agg = date("YmdHis");
                    $this->jsonObject->enti[1]->tipo_comune = null;

                    $this->numeroEnte = 2;
                break;    

            case 'BOLZANO':
                if (!array_key_exists(0, $this->jsonObject->enti)) {
                    $this->jsonObject->enti[0] = new stdClass();
                }
                    //if (!array_key_exists($this->numeroCandidato, $this->jsonObject->cand)) {
                    $this->jsonObject->enti[0]->desc = 'TRENTINO ALTO ADIGE';
                    $this->jsonObject->enti[0]->cod = '040000000000';
                    $this->jsonObject->enti[0]->tipo = 'RE';
                    $this->jsonObject->enti[0]->dt_agg = date("YmdHis");
                    $this->jsonObject->enti[0]->tipo_comune = null;

                    $this->jsonObject->enti[1] = new stdClass();
                    $this->jsonObject->enti[1]->desc = 'PROVINCIA AUTONOMA BOLZANO';
                    $this->jsonObject->enti[1]->cod = '040140000000';
                    $this->jsonObject->enti[1]->tipo = 'CR';
                    $this->jsonObject->enti[1]->dt_agg = date("YmdHis");
                    $this->jsonObject->enti[1]->tipo_comune = null;

                    $this->jsonObject->enti[1] = new stdClass();
                    $this->jsonObject->enti[1]->desc = 'PROVINCIA AUTONOMA BOLZANO';
                    $this->jsonObject->enti[1]->cod = '040140140000';
                    $this->jsonObject->enti[1]->tipo = 'PR';
                    $this->jsonObject->enti[1]->dt_agg = date("YmdHis");
                    $this->jsonObject->enti[1]->tipo_comune = null;

                    $this->numeroEnte = 2;
                break;
            }
        }

    public function setComune($objectComune) {
            if (!array_key_exists($this->numeroEnte,$this->jsonObject->enti)) {
                $this->jsonObject->enti[$this->numeroEnte] = new stdClass();
                $this->jsonObject->enti[$this->numeroEnte]->desc = $objectComune->int->desc_com;
                $this->jsonObject->enti[$this->numeroEnte]->cod = '04'.$objectComune->int->cod_prov.$objectComune->int->cod_com;
                $this->jsonObject->enti[$this->numeroEnte]->tipo = 'CM';
                $this->jsonObject->enti[$this->numeroEnte]->dt_agg = date("YmdHis");
                $this->jsonObject->enti[$this->numeroEnte]->tipo_comune = 'N';
                $this->numeroEnte++;


            }
        }
        

}

/**
 * @abstract Classe scrutinio. Gestisce l'oggetto relativo dello scrutinio della Provincia
 */

 class scrutinioProvincia {

    public $jsonObject;
    public $numeroCandidatoProvincia = 0;
    public $numeroListaProvincia = 0;
    public $comuneInCorso = null;

    public function __construct($dataAffluenzaProvinciaHA, $affluenzaTotaleHA = array()) {

        $this->jsonObject = new stdClass();
        $this->jsonObject->int = new stdClass();
        $this->jsonObject->int->st = STATO;
        $this->jsonObject->int->t_ele = 'Regionali Speciali';
        $this->jsonObject->int->f_elet = 'SCRUTINI';
        $this->jsonObject->int->dt_ele = DATA_ELEZIONI;
        $this->jsonObject->int->l_terr = 'PROVINCIA'; //(forse va scritto Regione)
        $this->jsonObject->int->area = 'I';

        switch ($dataAffluenzaProvinciaHA['desc_prov']) {

            case 'TRENTO':

/***
                $nomeComune = $dataAffluenzaAR['Nome Comune'];
                $this->jsonObject->int->desc_com = strtoupper($nomeComune);
                $this->jsonObject->int->cod_com = $dataAffluenzaAR['cod_com'];;
 * 
 */
                $this->jsonObject->int->desc_reg = $dataAffluenzaProvinciaHA['desc_prov'];

                $this->jsonObject->int->cod_prov = COD_PROV; 

                //$this->jsonObject->int->cod_ISTAT = $dataAffluenzaProvinciaHA['Istat Comune'];
                $this->jsonObject->int->ele_m = $dataAffluenzaProvinciaHA['ElettoriM'];
                $this->jsonObject->int->ele_f = $dataAffluenzaProvinciaHA['ElettoriF'];
                $this->jsonObject->int->ele_t = $dataAffluenzaProvinciaHA['ElettoriT'];
 
                $this->jsonObject->int->vot_m = $dataAffluenzaProvinciaHA['VotantiF'];
                $this->jsonObject->int->vot_f = $dataAffluenzaProvinciaHA['VotantiF'];
                $this->jsonObject->int->vot_t = $dataAffluenzaProvinciaHA['VotantiT'];

                $this->jsonObject->int->dt_agg = date("YmdHis");
                $percVoti = round((($this->jsonObject->int->vot_t/$this->jsonObject->int->ele_t)*100),2);
                $this->jsonObject->int->perc_vot = $percVoti;

                // Inizializzazione totali
                $this->jsonObject->int->sz_tot = 0;
                $this->jsonObject->int->sz_perv = 0;
                $this->jsonObject->int->sz_p_cons = 0;
                $this->jsonObject->int->sk_bianche = 0;
                $this->jsonObject->int->sk_nulle = 0;
                $this->jsonObject->int->sk_contestate = 0;


            break;
            case 'BOLZANO':

                 // Inizializzazione totali
                $this->jsonObject->int->desc_reg = $dataAffluenzaProvinciaHA['desc_prov'];
                $this->jsonObject->int->desc_reg_DE = DESC_PROV_DE;
                $this->jsonObject->int->desc_reg_LAD = DESC_PROV_LAD;

                $this->jsonObject->int->cod_prov = COD_PROV; 
                $this->jsonObject->int->sz_tot = $affluenzaTotaleHA['sz_tot'];
                $this->jsonObject->int->sz_pres = $affluenzaTotaleHA['sz_perv'];
                $this->jsonObject->int->sz_perv = $affluenzaTotaleHA['sz_perv'];
                $this->jsonObject->int->sk_bianche = 0;
                $this->jsonObject->int->sk_nulle = 0;
                $this->jsonObject->int->ele_m = $affluenzaTotaleHA['ele_m'];
                $this->jsonObject->int->ele_f = $affluenzaTotaleHA['ele_f'];
                $this->jsonObject->int->ele_t = $affluenzaTotaleHA['ele_t'];
                $this->jsonObject->int->vot_m = $affluenzaTotaleHA['vot_m'];
                $this->jsonObject->int->vot_f = $affluenzaTotaleHA['vot_f'];
                $this->jsonObject->int->vot_t = $affluenzaTotaleHA['vot_t'];

                $percVoti = round((($this->jsonObject->int->vot_t/$this->jsonObject->int->ele_t)*100),2);
                $this->jsonObject->int->perc_vot = $percVoti;
                

                $this->jsonObject->int->dt_agg = date("YmdHis");
            break;

        }
        $this->jsonObject->cand = array();

    }

    public function __destruct() {
        unset($this->jsonObject);

        
    }    
    /**
     * @abstract Imposta i dati delle liste collegate al candidato Presidente. chiama le funzioni di impostazioni specifiche di ogni regione/provincia
     *
     * @param array $dataVotiListeHA
     * @return void
     */
    public function setVotiListeCandidatoProvincia($dataVotiListeHA, $comuneInCorso) {
        $idPresidente = $this->jsonObject->cand[$this->numeroCandidatoProvincia]->id_Presidente;
        if (!isset($this->jsonObject->cand[$this->numeroCandidatoProvincia]->liste)) {
            $this->jsonObject->cand[$this->numeroCandidatoProvincia]->liste = array();
        }

/*         switch ($this->jsonObject->int->desc_prov) {
            case 'TRENTO':
                $this->setVotiListeCandidatoProvinciaTrento($dataVotiListeHA);
                break;
            case 'BOLZANO':
                $this->setVotiListeCandidatoProvinciaBolzano($dataVotiListeHA);
                break;
        }
 */        switch ($this->jsonObject->int->cod_prov) {
            case '083':
                $this->setVotiListeCandidatoProvinciaTrento($dataVotiListeHA, $comuneInCorso);
                break;
            case '014':
                $this->setVotiListeCandidatoProvinciaBolzano($dataVotiListeHA);
                break;
        }

    }
    /**
     * @abstract Imposta i dati del candidato Presidente. chiama le funzioni di impostazioni specifiche di ogni regione/provincia
     *
     * @param array $candidatoAr
     * @return void
     */
    public function setCandidatoProvincia($candidatoAr, $datiAffluenzaComuneInCorso = null) {
        if (!array_key_exists($this->numeroCandidatoProvincia, $this->jsonObject->cand)) {
            $this->jsonObject->cand[$this->numeroCandidatoProvincia] = new stdClass();
            $this->numeroListaProvincia = 0;
        }
        // switch ($this->jsonObject->int->desc_prov) {
        switch ($this->jsonObject->int->cod_prov) {
            case '083': // 'TRENTO':
                $this->setCandidatoProvinciaTrento($candidatoAr);
                break;
            case '014':
                $this->setCandidatoProvinciaBolzano($candidatoAr, $datiAffluenzaComuneInCorso);
                break;

        }

    }
    /**
     * Imposta i dati delle liste collegate al candidato Presidente
     * Trento
     *
     * @param array $dataVotiListeHA
     * @return void
     */

    public function setVotiListeCandidatoProvinciaTrento($dataVotiListeHA, $comuneInCorso) {
        $idPresidente = $this->jsonObject->cand[$this->numeroCandidatoProvincia]->id_Presidente;
        /**
         * Ciclare $dataVotiListeHA[$idPresidente]
         */
        $listeSingoloPresidente = $dataVotiListeHA[$idPresidente];
        Ordinamenti::OrdinaPerChiaveValore($listeSingoloPresidente, 'Lista Id');

        $this->numeroListaProvincia = 0;

        foreach ($listeSingoloPresidente as $singolaLista) {
            if ($comuneInCorso == $singolaLista['Istat Comune']) {

                if (array_key_exists($this->numeroListaProvincia, $this->jsonObject->cand[$this->numeroCandidatoProvincia]->liste)) {
                    if ($singolaLista['Lista Id'] != $this->jsonObject->cand[$this->numeroCandidatoProvincia]->liste[$this->numeroListaProvincia]->lista_id) {
                        $this->numeroListaProvincia++;
                    }
                }

                if (!array_key_exists($this->numeroListaProvincia, $this->jsonObject->cand[$this->numeroCandidatoProvincia]->liste)) {

                    $this->jsonObject->cand[$this->numeroCandidatoProvincia]->liste[$this->numeroListaProvincia] = new stdClass();

                    $this->jsonObject->cand[$this->numeroCandidatoProvincia]->liste[$this->numeroListaProvincia]->desc_lis_c = $singolaLista['Nome Lista']; 
                    $this->jsonObject->cand[$this->numeroCandidatoProvincia]->liste[$this->numeroListaProvincia]->pos = $singolaLista['Progressivo Lista']; 
                    $this->jsonObject->cand[$this->numeroCandidatoProvincia]->liste[$this->numeroListaProvincia]->lista_id = $singolaLista['Lista Id']; 

                    $this->jsonObject->cand[$this->numeroCandidatoProvincia]->liste[$this->numeroListaProvincia]->voti = $singolaLista['Voti']; 

                    $percVotiLista = 0;
                    if ($singolaLista['Voti'] > 0 && $this->jsonObject->int->vot_t > 0) {
                        $percVotiLista = round((($singolaLista['Voti']/$this->jsonObject->int->vot_t)*100),2);
                    }
                    $this->jsonObject->cand[$this->numeroCandidatoProvincia]->liste[$this->numeroListaProvincia]->perc = $percVotiLista; 

                    $this->jsonObject->cand[$this->numeroCandidatoProvincia]->liste[$this->numeroListaProvincia]->img_lis = '';                
                    $this->jsonObject->cand[$this->numeroCandidatoProvincia]->liste[$this->numeroListaProvincia]->seggi = 0; 
                    $this->jsonObject->cand[$this->numeroCandidatoProvincia]->liste[$this->numeroListaProvincia]->sort_lis = 0; 
                } else {
                    $this->jsonObject->cand[$this->numeroCandidatoProvincia]->liste[$this->numeroListaProvincia]->voti += $singolaLista['Voti']; 
                    

                    $percVotiLista = 0;
                    if ($this->jsonObject->cand[$this->numeroCandidatoProvincia]->liste[$this->numeroListaProvincia]->voti > 0 && $this->jsonObject->int->vot_t > 0) {
                        $percVotiLista = round((($this->jsonObject->cand[$this->numeroCandidatoProvincia]->liste[$this->numeroListaProvincia]->voti/$this->jsonObject->int->vot_t)*100),2);
                    }
                    $this->jsonObject->cand[$this->numeroCandidatoProvincia]->liste[$this->numeroListaProvincia]->perc = $percVotiLista; 

                }
        }



        }

    } 

    /**
     * Imposta i dati delle liste collegate al candidato Presidente
     * Bolzano
     *
     * @param array $dataVotiListeHA indice: codice comune (versione della provincia di Bolzano) + ordine candidatura ($idPresidente) 
     * @return void
     */
    public function setVotiListeCandidatoProvinciaBolzano($dataVotiListeAr) {
        $codComTmp = $this->jsonObject->int->cod_comune_originale;
        $posCandidato = $this->jsonObject->cand[$this->numeroCandidatoProvincia]->pos;

        $codComTmp =  $this->jsonObject->int->cod_com;

        /**
         * Cicla l'array contenente i voti di preferenza dei candidati associati ad una lista di un singolo comune 
         */

         foreach ($dataVotiListeAr as $singoloComune) {
            $this->numeroListaProvincia = 0;
            foreach ($singoloComune as $singoloCand) {
                if ($posCandidato == $singoloCand['LIST_NUM']) {
                    if (!array_key_exists($this->numeroListaProvincia, $this->jsonObject->cand[$this->numeroCandidatoProvincia]->liste)) {
                        $this->jsonObject->cand[$this->numeroCandidatoProvincia]->liste[$this->numeroListaProvincia] = new stdClass();
                        $this->jsonObject->cand[$this->numeroCandidatoProvincia]->liste[$this->numeroListaProvincia]->voti = $singoloCand['CAND_VOTES']; 
                    } else {
                        $this->jsonObject->cand[$this->numeroCandidatoProvincia]->liste[$this->numeroListaProvincia]->voti += $singoloCand['CAND_VOTES']; 
                    }
                    $this->jsonObject->cand[$this->numeroCandidatoProvincia]->liste[$this->numeroListaProvincia]->desc_lis_c = $singoloCand['CAND_NAME']; 
                    $this->jsonObject->cand[$this->numeroCandidatoProvincia]->liste[$this->numeroListaProvincia]->pos = $singoloCand['LIST_NUM']; 
                    
                    $percVotiLista = 0;
                    $voti = $this->jsonObject->cand[$this->numeroCandidatoProvincia]->liste[$this->numeroListaProvincia]->voti;
                    if ($voti > 0 && $this->jsonObject->int->vot_t > 0) {
                        $percVotiLista = round((($voti/$this->jsonObject->int->vot_t)*100),2);
                    }
                    $this->jsonObject->cand[$this->numeroCandidatoProvincia]->liste[$this->numeroListaProvincia]->perc = $percVotiLista; 
    
                    $this->jsonObject->cand[$this->numeroCandidatoProvincia]->liste[$this->numeroListaProvincia]->img_lis = '';                
                    $this->jsonObject->cand[$this->numeroCandidatoProvincia]->liste[$this->numeroListaProvincia]->seggi = 0; 
                    $this->jsonObject->cand[$this->numeroCandidatoProvincia]->liste[$this->numeroListaProvincia]->sort_lis = 0; 
                    $this->numeroListaProvincia++;
                }
    
            }
        }
         
/*
        $nomeCandidato = $this->jsonObject->cand[$this->numeroCandidatoProvincia]->cogn;
        foreach ($dataVotiListeHA[$codComTmp] as $singolaLista) {

            if ($nomeCandidato == $singolaLista['NOMINATIVO']) {
                if (!array_key_exists($this->numeroListaProvincia, $this->jsonObject->cand[$this->numeroCandidatoProvincia]->liste)) {
                    $this->jsonObject->cand[$this->numeroCandidatoProvincia]->liste[$this->numeroListaProvincia] = new stdClass();
                }
                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->liste[$this->numeroListaProvincia]->descr_lista = $singolaLista['DESCRIZIONELISTA']; 
                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->liste[$this->numeroListaProvincia]->voti = $singolaLista['VOTILISTA']; 
                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->liste[$this->numeroListaProvincia]->pos = $singolaLista['ORDINELISTA']; 

                $percVotiLista = 0;
                if ($singolaLista['VOTILISTA'] > 0 && $singolaLista['VOTIVALIDIDILISTE'] > 0) {
                    $percVotiLista = round((($singolaLista['VOTILISTA']/$singolaLista['VOTIVALIDIDILISTE'])*100),2);
                }
                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->liste[$this->numeroListaProvincia]->perc = $percVotiLista; 

                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->liste[$this->numeroListaProvincia]->img_lis = '';                
                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->liste[$this->numeroListaProvincia]->seggi = 0; 
                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->liste[$this->numeroListaProvincia]->sort_lis = 0; 
                $this->numeroListaProvincia++;
            }
        }
*/
    } 
    

    /**
     * Imposta i dati del candidato Presidente
     * Bolzano
     *
     * @param array $candidatoAr
     * @return void
     */
    public function setCandidatoProvinciaBolzano($candidatoAr, $datiAffluenzaComuneInCorso) {

        $this->jsonObject->cand[$this->numeroCandidatoProvincia]->cogn = $candidatoAr['LIST_NAME']; 
        $this->jsonObject->cand[$this->numeroCandidatoProvincia]->nome = ''; 
        $this->jsonObject->cand[$this->numeroCandidatoProvincia]->a_nome = ''; 
        if ($candidatoAr['MUNI_NUM'] != $this->comuneInCorso) {
            $this->comuneInCorso = $candidatoAr['MUNI_NUM'];
            $this->jsonObject->int->sk_bianche += (int)$candidatoAr['MUNI_BLANK']; 
            $this->jsonObject->int->sk_nulle += (int)$candidatoAr['MUNI_NULLS']; 
        } 

        if (!property_exists($this->jsonObject->cand[$this->numeroCandidatoProvincia], 'pos')) {
            $this->jsonObject->cand[$this->numeroCandidatoProvincia]->pos = $candidatoAr['LIST_NUM']; 
            $this->jsonObject->cand[$this->numeroCandidatoProvincia]->voti = $candidatoAr['LIST_VOTES']; 
        } else {
            $this->jsonObject->cand[$this->numeroCandidatoProvincia]->voti += $candidatoAr['LIST_VOTES']; 
        }
        if ($candidatoAr['MUNI_NULLS'] == "021120") {
            $percVoti = 0;
        }
        $percVoti = 0;
        $votiValidi = $this->jsonObject->int->vot_t - ($this->jsonObject->int->sk_nulle + $this->jsonObject->int->sk_bianche);
        if ($this->jsonObject->cand[$this->numeroCandidatoProvincia]->voti > 0 && $this->jsonObject->int->vot_t > 0) {
            $percVoti = round((($this->jsonObject->cand[$this->numeroCandidatoProvincia]->voti/$votiValidi)*100),2);
        }
        $this->jsonObject->cand[$this->numeroCandidatoProvincia]->perc = $percVoti; 
        $this->jsonObject->cand[$this->numeroCandidatoProvincia]->d_nasc = ''; 
        $this->jsonObject->cand[$this->numeroCandidatoProvincia]->l_nasc = ''; 
        $this->jsonObject->cand[$this->numeroCandidatoProvincia]->eletto = ''; 
        $this->jsonObject->cand[$this->numeroCandidatoProvincia]->sg_ass = 0; 
        $this->jsonObject->cand[$this->numeroCandidatoProvincia]->sort_coal = null; 
        $this->jsonObject->cand[$this->numeroCandidatoProvincia]->sg_sort_coal = null; 
        $this->jsonObject->cand[$this->numeroCandidatoProvincia]->img_lis_r = $candidatoAr['img_lis_r'];


        /** Duplicato di voti e perc
         */
        $this->jsonObject->cand[$this->numeroCandidatoProvincia]->tot_vot_lis = $candidatoAr['LIST_VOTES']; 
        $this->jsonObject->cand[$this->numeroCandidatoProvincia]->perc_lis = $percVoti; 


    }
    /**
     * Aggiorna i dati dei candidati con quelli provenienti dagli scrutini del comune
     * PROVINCIA DI TRENTO
     */
    public function setCandidatoProvinciaTrento($candidatoAr) {


            if (!property_exists($this->jsonObject->cand[$this->numeroCandidatoProvincia], 'pos')) {

                // Aggiornamento dati complessivi della provincia

                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->cogn = $candidatoAr['Cognome']; 
                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->nome = $candidatoAr['Nome']; 
                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->a_nome = $candidatoAr['Nome Detto']; 
                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->pos = $candidatoAr['Progressivo Presidente']; 
                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->id_Presidente = $candidatoAr['Presidente Id']; 

                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->voti = $candidatoAr['Voti']; 

                // dati necessari per calcolo finale percentuale voti
                $percVoti = 0;
                $votiValidi = $this->jsonObject->int->vot_t - ($candidatoAr['Schede Bianche'] + $candidatoAr['Schede nulle o contenenti solo voti nulli'] + $candidatoAr['Schede contestate e non attribuite']);

                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->bianche = $candidatoAr['Schede Bianche']; 
                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->nulle = $candidatoAr['Schede nulle o contenenti solo voti nulli']; 
                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->nonAttribuite = $candidatoAr['Schede contestate e non attribuite'];
                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->votiValidi = $votiValidi;

                if ($candidatoAr['Voti'] > 0 && $this->jsonObject->int->vot_t > 0) {
                    $percVoti = round((($candidatoAr['Voti']/$votiValidi)*100),2);
                }
                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->perc = $percVoti; 

                // dati non disponibili, ma proprietà necessarie per la compatibilità con json 
                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->d_nasc = ''; 
                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->l_nasc = ''; 
                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->eletto = ''; 
                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->sg_ass = 0; 
                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->sort_coal = null; 
                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->sg_sort_coal = null; 
        
                /** Duplicato di voti e perc
                 */
                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->tot_vot_lis = $candidatoAr['Voti']; 
                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->perc_lis = $percVoti; 

            } else {
                // se già impostato il candidato vanno addizionati i voti...

                //Anagrafica
                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->cogn = $candidatoAr['Cognome']; 
                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->nome = $candidatoAr['Nome']; 
                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->a_nome = $candidatoAr['Nome Detto']; 
                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->pos = $candidatoAr['Progressivo Presidente']; 
                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->id_Presidente = $candidatoAr['Presidente Id']; 

                // Voti
                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->voti += $candidatoAr['Voti']; 

                /**
                 * ATTENZIONE VERIFICARE CALCOLO VOTI VALIDI (FORSE VA CALCOLATO CON IL GENERALE)
                 */
                $percVoti = 0;
                $votiValidi = $this->jsonObject->int->vot_t - ($candidatoAr['Schede Bianche'] + $candidatoAr['Schede nulle o contenenti solo voti nulli'] + $candidatoAr['Schede contestate e non attribuite']);

                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->bianche += $candidatoAr['Schede Bianche']; 
                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->nulle += $candidatoAr['Schede nulle o contenenti solo voti nulli']; 
                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->nonAttribuite += $candidatoAr['Schede contestate e non attribuite'];
                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->votiValidi += $votiValidi;

                if ($this->jsonObject->cand[$this->numeroCandidatoProvincia]->voti > 0 && $this->jsonObject->int->vot_t > 0) {
                    $percVoti = round((($this->jsonObject->cand[$this->numeroCandidatoProvincia]->voti/$votiValidi)*100),2);
                }
                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->perc = $percVoti; 

            /**
             *  Dati inizializzati la prima volta che viene impostato il candidato  - NON necessario eseguirlo di nuovo
                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->d_nasc = ''; 
                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->l_nasc = ''; 
                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->eletto = ''; 
                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->sg_ass = 0; 
                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->sort_coal = null; 
                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->sg_sort_coal = null; 
            */
            $this->jsonObject->cand[$this->numeroCandidatoProvincia]->sg_sort_coal = null;
                
        
                /** Duplicato di voti e perc
                 */
                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->tot_vot_lis = $this->jsonObject->cand[$this->numeroCandidatoProvincia]->voti; 
                $this->jsonObject->cand[$this->numeroCandidatoProvincia]->perc_lis = $percVoti; 

            }
        //}
    }


    /**
     * Effettua i calcoli sulle percentuali dei voti 
     */
    public function setCalcoliProvinciaTrento($candidatoAr) {

        /**
         *  dati generali
         *  A Trento sono ripetuti nel record di ogni candidato Presidente nei risultati comunali
         */

         $this->jsonObject->int->sz_p_cons += $candidatoAr['Sez.Pervenute'];
         $this->jsonObject->int->sk_bianche += $candidatoAr['Schede Bianche'];
         $this->jsonObject->int->sk_nulle += $candidatoAr['Schede nulle o contenenti solo voti nulli'];
         $this->jsonObject->int->sk_contestate += $candidatoAr['Schede contestate e non attribuite'];

         $this->jsonObject->int->sz_tot += $candidatoAr['Sez.Totali'];
         $this->jsonObject->int->sz_perv += $candidatoAr['Sez.Pervenute'];

         $percVoti = 0;
         if ($this->jsonObject->int->ele_t > 0 && $this->jsonObject->int->vot_t > 0) {
             $percVoti = round((($this->jsonObject->int->vot_t/$this->jsonObject->int->ele_t)*100),2);
         }
         $this->jsonObject->int->perc_vot = $percVoti;

    }

    /**
     * 
     */
    public function OrdinaListe() {
        // Ordina gli oggetti utilizzando la funzione di confronto personalizzata per voti discendente
        $numeroCandidato = 0;
        foreach ($this->jsonObject->cand as $singleCand) {
            $singlePref = $singleCand->liste;
            usort($singlePref, function($a,$b) {
                if ($a->voti == $b->voti) {
                    return 0;
                }
                return ($a->voti < $b->voti) ? 1 : -1;
            
            });
            $this->jsonObject->cand[$numeroCandidato]->liste = $singlePref;
            $numeroCandidato ++;
        }
    }
}
