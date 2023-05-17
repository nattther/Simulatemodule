<?php
// Fonction pour vérifier si le module doit planter
function randomFailure()
{
    $randomNumber = rand(1, 20);
    if ($randomNumber <= 1) {
        return true;
    } else {
        return false;
    }
}

// Fonction pour vérifier si le module doit redémarrer
function Restart()
{
    $randomNumber = rand(1,20);
    if ($randomNumber <= 1) {
        return true;
    } else {
        return false;
    }
}


session_start();


if (!isset($_SESSION['temperature'])) {
    $_SESSION['temperature'] = rand(10, 50);
}

if (!isset($_SESSION['puissance'])) {
    $_SESSION['puissance'] = rand(100, 500);
}

if (!isset($_SESSION['etat'])) {
    $_SESSION['etat'] = false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['moduleId'])) {
    $moduleId = $_POST['moduleId'];
    // Connexion à la base de données
    $db = mysqli_connect("localhost", "root", "", "monitor");

    // Récupération des valeurs depuis les variables de session
    $temperature = $_SESSION['temperature'];
    $puissance = $_SESSION['puissance'];
    $etat = $_SESSION['etat'];

    // Test de plantage ou de redémarrage
    if (randomFailure()) {
        $etat = true;
    }

    if ($etat) {
        if (Restart()) {
            $etat= false;
            $temperature = rand(10, 50);
            $puissance = rand(100, 500);
        } else {
            $temperature = 0;
            $puissance = 0;
        }
    }
    else{
        $temperature = rand(10, 50);
        $puissance = rand(100, 500);

    }

    // Mise à jour des variables de session
    $_SESSION['temperature'] = $temperature;
    $_SESSION['puissance'] = $puissance;
    $_SESSION['etat'] = $etat;

    // Insertion des données
    $sql = "INSERT INTO $moduleId (Date, Puissance, température) VALUES (NOW(), $puissance, $temperature)";
    mysqli_query($db, $sql);

    // Fermeture de la connexion à la base de données
    mysqli_close($db);
}
?>
