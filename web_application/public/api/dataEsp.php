<?php

$mdp = $tab = $VALEUR = $TYPE = "";

if($_SERVER["REQUEST_METHOD"]=="POST"){

$conn = new mysqli("localhost", "EMA", "emacoficab", "EMA_DB");

if (!$conn) { die("Connection failed: " . mysqli_connect_error());}

$mdp = test_input($_POST["mdp"]);
$tab = test_input($_POST["tab"]);

if($mdp == "GEAmdpREQUEST")
    {
        if($tab == "P")
        {
            //les var à transmettre
            $VALEUR = test_input($_POST["VALEUR"]);
            $TYPE = test_input($_POST["TYPE"]);

            $sql = "INSERT INTO `concentrations1` (`ID`, `DATE`, `HEURS`, `VALEUR`, `TYPE`) VALUES (NULL, current_timestamp(), current_timestamp(), '".$VALEUR."', '".$TYPE."');";
            if (mysqli_query($conn, $sql)) { echo "--VALIDE-- Valeur : ".$VALEUR." -- Type : ".$TYPE;} 
            else { echo "Error: " . $sql . "<br>" . mysqli_error($conn);}

        }elseif($tab == "A")
        {
            //les var à transmettre
            $ACTION = test_input($_POST["ACTION"]);

            $sql = "INSERT INTO `actions1` (`ID`, `DATE`, `HEURS`, `ACTION`) VALUES (NULL, current_timestamp(), current_timestamp(), '".$ACTION."');";
            if (mysqli_query($conn, $sql)) { echo "--VALIDE-- Action : ".$ACTION;} 
            else { echo "Error: " . $sql . "<br>" . mysqli_error($conn);}
            
        }else { echo "la table n'est pas spécifiée";}
        
    }else { echo "MDP faux";}

    mysqli_close($conn);

}else {echo "Erreur d'envoie";}


//cette fonction assure la validation des données
/**
 * Summary of test_input
 * @param mixed $data
 * @return string
 */
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
  }

?>