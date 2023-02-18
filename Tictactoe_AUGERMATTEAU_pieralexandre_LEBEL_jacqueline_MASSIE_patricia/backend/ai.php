<?php

// CORS
header("Access-Control-Allow-Origin: " . (isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*'));
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Auth-Token, Origin, Authorization");
header("Access-Control-Allow-Credentials: true");

// Récupère en GET ou en POST la grille et le pion
// ex: backend/ai.php?grille=["","","","","","","","",""]&pion=x
$grille = empty($_POST["grille"]) ? json_decode($_GET["grille"]) : json_decode($_POST["grille"]);
$pion = empty($_POST["pion"]) ? $_GET["pion"] : $_POST["pion"];

// ---------------------------------------------------------------------

// Initiation du tableau des cases disponibles
$disponibles = [];

// Trouver les cases disponibles
foreach($grille as $index => $place) {
    if ($place == "") {
        $disponibles[] = $index;
    }
}

// Informations sur l'adversaire ******************
$pion_adversaire = $pion == "x" ? "o" : "x" ;    //
$cases_adversaire = [];                          //
                                                 //
// Trouver les cases jouées par l'adversaire     //
foreach($grille as $index => $place) {           //
    if ($place == $pion_adversaire) {            //
        $cases_adversaire[] = $index;            //
    }                                            //
}                                                //
// ************************************************

$lignes = [
    // Horizontales
    [0, 1, 2],
    [3, 4, 5],
    [6, 7, 8],
    // Verticales
    [0, 3, 6],
    [1, 4, 7],
    [2, 5, 8],
    // Diagonales
    [0, 4, 8],
    [2, 4, 6],
];

$coins = [0, 2, 6, 8];
$cotes = [1, 3, 5, 7];

/**
 * Trouve une case aléatoire libre parmi les coins ou les côtés
 *
 * @param array $tab_cible
 * @param array $tab_dispo
 * @return int L'index de la grille 
 */
function trouverCaseAleatoire($tab_cible, $tab_dispo){
    $places_libres = array_intersect($tab_cible, $tab_dispo);

    if(count($places_libres) == 0) {
        $place_aleatoire = array_rand($tab_dispo, 1);
        return $tab_dispo[$place_aleatoire];
    }

    $place_aleatoire = array_rand($places_libres, 1);

    return $places_libres[$place_aleatoire];
}

/**
 * Détermine si l'adversaire a joué dans un coin
 *
 * @param array $tab_cible
 * @param array $tab_dispo
 * @return boolean
 */
function verifierCaseJouer($tab_cible, $tableau){
    $places_libres = array_intersect($tab_cible, $tableau);

    if(count($places_libres) == 0 ){
        return true;
    }

    return false;
}

// Détermine pair ou impair

// IMPAIR
if((count($disponibles))%2 != 0) {

    // Récupère le nombre de case vide
    // 9 case dispo = début de la partie
    if(count($disponibles) == 9) {
        $place_choisie = 0;

    } else if(count($disponibles) == 7) {
        if($grille[8] == "") {
            $place_choisie = 8;
        } else {
            $place_choisie = trouverCaseAleatoire($coins, $disponibles);
        }
    } else if(count($disponibles) == 5) {
        if($grille[5] == "") {
            $place_choisie = 5;
        } else {
            $place_choisie = trouverCaseAleatoire($cotes, $disponibles);
        }
    } else if(count($disponibles) == 3) {
        if($grille[6] == "") {
            $place_choisie = 6;
        } else {
            $place_choisie = trouverCaseAleatoire($cotes, $disponibles);
        }
    } else if (count($disponibles) == 1) {
        $place_choisie = $disponibles[0];
    }

// PAIR
} else {
    if(count($disponibles) == 8) {
        // 2e coup
        if(verifierCaseJouer($coins, $cases_adversaire)){
            $place_choisie = 0;
        } else {
            $place_choisie = 4;
        }
    } else if(count($disponibles) == 6) {
        if($grille[3] == "") {
            $place_choisie = 3;
        } else if($grille[1] == ""){
            $place_choisie = 1;
        } else {
            $place_choisie = trouverCaseAleatoire($cotes, $disponibles);
        }
    } else if(count($disponibles) == 4) {
        if($grille[1] == "") {
            $place_choisie = 1;
        } else if($grille[4] == ""){
            $place_choisie = 4;
        } else {
            $place_choisie = trouverCaseAleatoire($cotes, $disponibles);
        }
    } else if (count($disponibles) == 2) {
        if($grille[2] == "") {
            $place_choisie = 2;
        } else if($grille[4] == ""){
            $place_choisie = 4;
        } else {
            $place_choisie = trouverCaseAleatoire($coins, $disponibles);
        }
    }
}

// Vérification finale pour gagner ou bloquer
foreach($lignes as $ligne) {
    $texte = $grille[$ligne[0]] . $grille[$ligne[1]] . $grille[$ligne[2]];

    // CODE LOCAL
    if ($texte == $pion . $pion) {
        foreach($ligne as $place) {
            if($grille[$place] == "") {
                $place_choisie = $place;
            }
        }

    // ADVERSAIRE
    } else if ($texte == $pion_adversaire . $pion_adversaire) {
        foreach($ligne as $place) {
            if($grille[$place] == "") {
                $place_choisie = $place;
                
            }
        } 
    } 
}


// Jouer
$grille[$place_choisie] = $pion;
// Retourner le résultat
echo json_encode($grille);
