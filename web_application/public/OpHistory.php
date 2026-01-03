<?php

session_start();

if($_SESSION["pass"] != 1){ header('Location: login.php');}
else {

$conn = new mysqli("localhost", "EMA", "emacoficab", "EMA_DB");
$tab = array();
$Texp = array();
$erreur1 = false;
$erreur2 = false;

if(isset($_POST['Tf']) && isset($_POST['date1']) && isset($_POST['date2'])){

    $trefileuse = $_POST['Tf'];
    if($trefileuse==3){ $_SESSION["CnTf"] = 'CnTf03'; $_SESSION["OpTf"] = 'OpTf03'; }
    else if($trefileuse==4){ $_SESSION["CnTf"] = 'CnTf04'; $_SESSION["OpTf"] = 'OpTf04'; }
    else if($trefileuse==7){ $_SESSION["CnTf"] = 'CnTf07'; $_SESSION["OpTf"] = 'OpTf07'; }
    else if($trefileuse==8){ $_SESSION["CnTf"] = 'CnTf08'; $_SESSION["OpTf"] = 'OpTf08'; }
    else if($trefileuse==9){ $_SESSION["CnTf"] = 'CnTf09'; $_SESSION["OpTf"] = 'OpTf09'; }
    else if($trefileuse==10){ $_SESSION["CnTf"] = 'CnTf10'; $_SESSION["OpTf"] = 'OpTf10'; }
    else if($trefileuse==11){ $_SESSION["CnTf"] = 'CnTf11'; $_SESSION["OpTf"] = 'OpTf11'; }
    else{ $erreur1 = true; }

    $_SESSION["date1"] = $_POST['date1'];
    $_SESSION["date2"] = $_POST['date2'];
    $_SESSION["DEFAULT"] = false;
}

if($_SESSION["DEFAULT"]){
    $OpTf = $_SESSION["OpTf"];
    $result = mysqli_query($conn, "SELECT * FROM $OpTf ORDER BY `ID` DESC");
    $row = mysqli_fetch_array($result);
    do{$tab[] = $row;}while($row = mysqli_fetch_assoc($result));
    //exporting
    $RESexp = mysqli_query($conn, "SELECT * FROM $OpTf ORDER BY `ID` ASC");
    while($ROWexp = mysqli_fetch_assoc($RESexp)){$Texp[] = $ROWexp;}
} else {
    $dateA = $_SESSION["date1"];
    $dateB = $_SESSION["date2"];
    $OpTf = $_SESSION["OpTf"];
    $result = mysqli_query($conn, "SELECT * FROM $OpTf WHERE `DATE` between '$dateA' AND '$dateB' ORDER BY `ID` DESC");
    $row = mysqli_fetch_array($result);

    if(!is_array($row)){
        $erreur2 = true;
    } else {
        do{$tab[] = $row;}while($row = mysqli_fetch_assoc($result));
        //exporting
        $RESexp = mysqli_query($conn, "SELECT * FROM $OpTf WHERE `DATE` between '$dateA' AND '$dateB' ORDER BY `ID` ASC");
        while($ROWexp = mysqli_fetch_assoc($RESexp)){$Texp[] = $ROWexp;}
        //var
        $erreur1 = false;
        $erreur2 = false;
    }
}

//exporter les données à Excel 
function Exporting($array) {
    $heading = false;
    if(!empty($array ))
        foreach($array  as $row) {
        if(!$heading) {
          echo implode("\t", array_keys($row)) . "\n";
          $heading = true;
        }
        echo implode("\t", array_values($row)) . "\n";
        }
    exit;
}
if(isset($_POST["export"])){
    $fileName = "EMA : Collection Concentration History".date('d-m-Y').".xls";
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"$fileName\"");
    Exporting($Texp);
    exit();
}


//deconnexion
if(isset($_GET['deconnexion'])) {
    // Supprime toutes les variables de session
    session_unset();
    // Détruit la session
    session_destroy();
    //deconnecte de BD
    $conn->connect_error;
    // Redirige vers la page de connexion
    header("Location: login.php");
    exit(); // Assure que le script s'arrête ici
}

?>


<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>EMA - Emulsion Monitoring Application</title>
    <link rel="stylesheet" type="text/css" href="homeStyle.css">
    <style>
    input[type=number]{
        width: 13%;
        padding: 5px 15px;
        margin: 8px 0;
        color : #1d2951;
    }
    input[type=date]{
        width: 10%;
        padding: 5px 15px;
        margin: 8px 0;
        color : #1d2951;
    }
    input[id='Filtre'] {
        background-color: #1d2951;
        color: white;
        border: 1px solid #454545;  
        font-weight: bold;    
        padding: 5px 16px; 
    }
    input[id='Filtre']:hover {
        background-color: white;
        color: #454545;
    }

    /**/
    button[id='Reset'] {
        background-color: #1d2951;
        color: white;
        border: 1px solid #454545;  
        font-weight: bold;    
        padding: 5px 16px; 
    }
    button[id='Reset']:hover {
        background-color: white;
        color: #454545;
    }

    /**/
    button[id='export'] {
        background-color: #339950;
        color: white;
        border: 1px solid #404040;  
        font-weight: bold;    
        padding: 5px 16px; 
    }
    button[id='export']:hover {
        background-color: white;
        color: #404040;
    }

    /* tableau */
    tbody tr:nth-child(odd) {
        background-color: #f2f2f2;
    }  
    table {
        border-collapse: collapse;
    }
    th, td {
        border: 0.5px solid black;
        text-align: left;
        padding: 10px;
    }
    </style>
</head>

<body>

<!-- Titre + logo + deconnexion -->

<header>

    <div>
        <ul>
            <li class="logo">
            <img src="logoCoficab.png" alt="Logo Coficab" style="width:167.4px; height:51.4px;">      
            </li>
            <li class="menu-item hidden"><a href='home.php?deconnexion'><span style="font-size: 1.1em; color : #1d2970;">LOGOUT</span></a></li>
        </ul>
    </div>

    <div class="heroe">
        <h1 style = "text-shadow: 2px 4px 3px rgba(0,0,0,0.3);">Operation History</h1>
        <h2>
        <a href='home.php'><span style="text-decoration: underline solid #EBEBEB;">Home</span></a>
        <span>-</span>
        <a href='CnHistory.php'><span style="text-decoration: underline solid #EBEBEB;">Collection Concentration History</span></a>
        </h2>
    </div>

</header>


<div>
    <section> 

    <!-- form -->
    <?php 
        if($erreur1){
            echo "<p style='color:red;font-size: 1em;'>!: Please select the appropriate TF.</p>";
            $erreur1 = $erreur2 = false;
        } else if($erreur2){
            echo "<p style='color:red;font-size: 1em;'>!: The selected date is not valid.</p>";
            $erreur2 = $erreur1 = false;
        }
    ?>
    <form method="POST" action="" style="transform: translateY(0px);">
        <label style="color: #1d2951 ;font-size: 1.1em;"><b>TF :</b></label>
        <input type="number" id="Tf" placeholder="TF selection" name="Tf" value="Tf">
        <label style="color: #1d2951 ;font-size: 1.1em; padding: 0px 0px 0px 25px;"><b>Date :</b></label>
        <input type="date" id="date1" name="date1">
        <label for="date2" style="color: #1d2951 ;font-size: 1.1em;">To</label>
        <input type="date" id="date2" name="date2">
        <input type="submit" id='Filtre' value='Filter' style="transform: translateX(25px);">
    </form>

    <!-- export -->
    <form method="POST" action="">
        <button type="submit" id="export" name="export" style="float:right; transform: translateY(-35px);">Export to Excel</button>
    </form>

    
    <!-- History -->
    <table width="100%" cellspacing="0">

        <thead style="background-color: silver"><tr>
        <th>DATE</th>
        <th>TIME</th>
        <th>OPERATION</th>
        </tr></thead>

        <?php foreach($tab as $Trow):?>
        <tr>
        <td> <?php echo $Trow['DATE']; ?> </td>
        <td> <?php echo date('H:i', strtotime($Trow['HEURS'])); ?> </td>
        <td> <?php echo $Trow['ACTION']; ?> </td>
        </tr>
        <?php endforeach; ?>

    </table>
    </section>
</div>

</body>
</html>

<?php }?>