<?php

session_start();

if($_SESSION["pass"] != 1){ header('Location: login.php');}
else {

    //var filtre
    $_SESSION["date1"] = '';
    $_SESSION["date2"] = '';
    $_SESSION["DEFAULT"] = true;
    
    //inisialiser
    $CnTf = 'CnTf08';
    $OpTf = 'OpTf08';
    $erreur = false;

    //session
    $_SESSION["CnTf"] = $CnTf;
    $_SESSION["OpTf"] = $OpTf;
    
    //variable de la date
    $auj = new DateTime();
    //fixer Lundi
    $m = clone $auj;
    $m->modify('this week');
    $monday = $m->format('y-m-d');
    //fixer Dimanche
    $s = clone $auj;
    $s->modify('this week +6 days');
    $sunday = $s->format('y-m-d');

    //choisir trefileuse
    if(isset($_POST['Tf'])){   
        $trefileuse = $_POST['Tf'];
        if($trefileuse==3){ $CnTf = 'CnTf03'; $OpTf = 'OpTf03'; }
        else if($trefileuse==4){ $CnTf = 'CnTf04'; $OpTf = 'OpTf04'; }
        else if($trefileuse==7){ $CnTf = 'CnTf07'; $OpTf = 'OpTf07'; }
        else if($trefileuse==8){ $CnTf = 'CnTf08'; $OpTf = 'OpTf08'; }
        else if($trefileuse==9){ $CnTf = 'CnTf09'; $OpTf = 'OpTf09'; }
        else if($trefileuse==10){ $CnTf = 'CnTf10'; $OpTf = 'OpTf10'; }
        else if($trefileuse==11){ $CnTf = 'CnTf11'; $OpTf = 'OpTf11'; }
        else{ $erreur = true;}
    }

    //mysql
    $conn = new mysqli("localhost", "EMA", "emacoficab", "EMA_DB");
    $result = mysqli_query($conn, "SELECT * FROM $CnTf WHERE `DATE` between '$monday' AND '$sunday' AND `TYPE`='Concentration average' ");
    $tab = array();
    while($row = mysqli_fetch_assoc($result)){ $tab[] = $row;}

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
    <link rel="stylesheet" type="text/css" href="assets/css/homeStyle.css">
    <script src="assets/js/chartJs/dist/chart.umd.js"></script>
    <style type="text/css"> 
    .container {
        width:100; 
        height:50; 
        margin: 15px auto;
    } 
    input[type=number]{
    width: 50%;
    padding: 5px 15px;
    margin: 8px 0;
    color : #1d2951;
    }
    .custom-input {
    float : right;
    padding : 0px 40px 0px 0px;
    transform: translateY(-69px);
    }
    input[type=submit] {
        background-color: #1d2951;
        color: white;
        border: 1px solid #404040;  
        font-weight: bold;    
        padding: 5px 14px; 
    }
    input[type=submit]:hover {
        background-color: white;
        color: #404040;
        }
    </style>
</head>

<body>

<!-- Titre + logo + deconnexion -->

<header>

    <div>
        <ul>
            <li class="logo">
            <img src="assets/img/logoCoficab.png" alt="Logo Coficab" style="width:167.4px; height:51.4px;">      
            </li>
            <li class="menu-item hidden"><a href='home.php?deconnexion'><span style="font-size: 1.1em; color : #1d2970;">LOGOUT</span></a></li>
        </ul>
    </div>

    <div class="heroe">
        <h1 style = "text-shadow: 2px 4px 3px rgba(0,0,0,0.3);">Welcome to Emulsion Monitoring application</h1>
        <h2>
        <a href='CnHistory.php'><span style="text-decoration: underline solid #EBEBEB;">Collection Concentration History</span></a>
        <span>-</span>
        <a href='OpHistory.php'><span style="text-decoration: underline solid #EBEBEB;">Operation History</span></a>
        </h2>
    </div>

</header> 

<!-- Courbe -->

<div class="container">
    <section>
        <!-- titre -->
        <h1 style=" text-shadow: 2px 4px 3px rgba(0,0,0,0.15); color:#1d2951;"> Weekly trend :</h1>
        <!-- trefileuse -->
        <form method="POST" action="" class="custom-input">
        <input type="number" id="Tf" placeholder="TF selection" name="Tf" value="Tf">
        <input type="submit" id='submit' value='Confirm'>
        <?php 
        if($erreur){
            echo "<p style='color:red;font-size: 1em;'>!: Please select the appropriate TF.</p>";
            $erreur = false;
        }
        ?>
        </form>
        <!-- placement Courbe -->
        <canvas id="myChart"></canvas>
    </section>
</div>
<script>
        var ctx = document.getElementById("myChart");
        var myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [<?php
                    foreach($tab as $val): 
                        $date = new DateTime($val['DATE']);
                        $jour = $date->format('l');
                        $heure = date('H:i', strtotime($val['HEURS']));
                        echo ' " '. $jour ." - ". $heure .' " , ';
                    endforeach;
                    ?>],
                datasets: [
                    {
                        label: 'LSC : 8,0%                    ',
                        borderColor: ['red'],
                        borderWidth: 1.5
                    },
                    {
                        label: 'LIC : 7,0%                    ',
                        borderColor: ['red'],
                        borderWidth: 1.5
                    },
                    {
                        label: 'Concentration (%) ',
                        data: [<?php
                        foreach($tab as $val): 
                        echo ' " '. $val['VALEUR'] .' " , '; 
                        endforeach;
                        ?>],
                        borderColor: ['black'],
                        borderWidth: 1.5
                    }
                ]
            },
            options: {
                scales: {
                        x: {
                            title: {
                            color: 'blue',
                            display: true,
                            text: 'Weekdays'
                            }
                        },
                        y: {
                            title: {
                                color: 'blue',
                                display: true,
                                text: 'Concentration (%)'
                            },
                            grid: {
                                color: function(context) {
                                    if (context.tick.value == 8) {
                                        return 'red';
                                    } else if (context.tick.value == 7) {
                                        return 'red';
                                    }
                                    return 'lightgray'; //rgba(255, 255, 255, 0.75)
                                },
                            },
                        },
                }
            },
        } );
</script>

</body>
</html>

<?php }?>