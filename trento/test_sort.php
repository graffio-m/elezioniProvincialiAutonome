<?php
// Array associativo di esempio
$studenti = array(
    array('nome' => 'Alice', 'voto' => 85),
    array('nome' => 'Bob', 'voto' => 92),
    array('nome' => 'Charlie', 'voto' => 78),
    array('nome' => 'Dave', 'voto' => 90),
);


$candidati = array(
    array('nome Comune' => "Ala",'lista'=>"pippo",'Presidente Id'=>'1443'),
    array('nome Comune'=>"Ala",'lista'=>"pluto",'Presidente Id'=>'1441'),
    array('nome Comune'=>"Ala",'lista'=>"pape",'Presidente Id'=>'1443'),
    array('nome Comune'=>"Ala",'lista'=>"paperino",'Presidente Id'=>'1442'),
    );

// Funzione di confronto personalizzata per ordinare l'array in base al voto
function confrontaPerVoto($a, $b) {
    return strcmp($a['Presidente Id'], $b['Presidente Id']);
}

// Ordina l'array associativo secondo il voto
usort($candidati, 'confrontaPerVoto');

// Stampa l'array ordinato
foreach ($candidati as $candidato) {
    echo $candidato['Presidente Id'] . ' - ' . $candidato['lista'] . "\n";
}


// Array di oggetti
$arrayOggetti = [
    (object) [
        'nome' => 'PAOLO',
        'voti' => 384
    ],
    (object) [
        'nome' => 'FILIPPO',
        'voti' => 1247
    ],
    // Aggiungi gli altri oggetti qui
];

// Estrai la colonna "voti" come un array separato per l'ordinamento
$voti = array_column($arrayOggetti, 'voti');

// Ordina l'array degli oggetti in base all'array dei voti
array_multisort($voti, SORT_DESC, $arrayOggetti);

// Visualizza l'array di oggetti ordinato
print_r($arrayOggetti);

?>
