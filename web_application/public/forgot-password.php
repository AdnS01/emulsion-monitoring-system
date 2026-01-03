<?php
session_start();
$conn = new mysqli("localhost", "EMA", "emacoficab", "EMA_DB");

if(!$conn){ die('Erreur au niveau de connection BD');}
elseif(isset($_POST['OLDpassword']) && isset($_POST['NVpassword']) && isset($_POST['confirm_pwd']))
{
    $Opwd = mysqli_real_escape_string($conn, htmlspecialchars($_POST['OLDpassword']));
    $Npwd = mysqli_real_escape_string($conn, htmlspecialchars($_POST['NVpassword']));
    $Cpwd = mysqli_real_escape_string($conn, htmlspecialchars($_POST['confirm_pwd']));

    if($Opwd == $Npwd || $Npwd!=$Cpwd){header('Location: forgot-password.php?erreur');}
    else {
        $result = mysqli_query($conn, "SELECT * FROM `zConn` where `password` = '$Opwd' " );
        $row = mysqli_fetch_array($result); //return array
        if(!is_array($row))
        { header('Location: forgot-password.php?erreur'); 
        } else {
            $result = mysqli_query($conn, "UPDATE `zConn` SET `password`='$Npwd' WHERE `id`='1'" );
            header('Location: login.php');
        }
    }
}
?>


<!DOCTYPE html>
<html style="background-color: rgba(247, 248, 249, 1);">

<head>
    <meta charset="UTF-8">
    <title>EMA - Emulsion Monitoring Application</title>
    <link rel="stylesheet" type="text/css" href="assets/css/homeStyle.css">
    <link rel="stylesheet" type="text/css" href="assets/css/formStyle.css">
</head>

<body>

<!-- Titre + logo -->

<header>

    <div>
        <ul>
            <li class="logo">
            <img src="assets/img/logoCoficab.png" alt="Logo Coficab" style="width:167.4px; height:51.4px;">      
            </li>
        </ul>
    </div>

    <div id="container">
    <form method="POST" action="">
        <h1 style="text-shadow: 2px 2px 4px gray;text-align:center;">EMULSION MONITORING</h1><br>
        <label style='color:rgba(50, 50, 50, 1);'><b>Current password</b></label>
        <input type="password" placeholder="Current password" name="OLDpassword" required><br><br>
        <label style='color:rgba(50, 50, 50, 1);'><b>New password</b></label>
        <input type="password" placeholder="New password" name="NVpassword" required><br><br>
        <label style='color:rgba(50, 50, 50, 1);'><b>Confirm the new password</b></label>
        <input type="password" placeholder="Confirm the new password" name="confirm_pwd" required><br><br>
        <input type="submit" id='submit' value='Confirm' >
        <?php 
        if(isset($_GET['erreur'])) 
        {echo "<p style='color:red; font-size: 0.9em;'> !: Please fill out the form correctly </p>";} 
        ?>
    </form>
    </div>

</header>

</body>
</html>