<?php
session_start();

$conn = new mysqli("localhost", "EMA", "emacoficab", "EMA_DB"); 

if(isset($_POST['password']))
{
    if(!$conn){ die('Erreur au niveau de connection BD');}
    else {
        // on applique les deux fonctions mysqli_real_escape_string et htmlspecialchars
	    // pour éliminer toute attaque de type injection SQL et XSS
	    $password = mysqli_real_escape_string($conn, htmlspecialchars($_POST['password']));

        if($password !== "")
        {
            $result = mysqli_query($conn, "SELECT * FROM `zConn` where `password` = '$password' " );
            $row = mysqli_fetch_array($result); //return array
            if(!is_array($row))
            { header('Location: login.php?erreur'); 
            } else
            {
                $_SESSION["password"] = $row['password'];
                //var de décpnnexion
                $_SESSION["pass"] = 1;
                header('Location: home.php');
            }
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
        <h1 style="text-shadow: 2px 4px 3px rgba(0,0,0,0.3);text-align:center; color : #1d2951; word-spacing: 0.1em;">EMULSION MONITORING</h1><br>
        <label style='color:rgba(50, 50, 50, 1);'><b>Password</b></label>
        <input type="password" placeholder="Please enter the password" name="password" required><br>
        <?php 
        if(isset($_GET['erreur'])) 
        {echo "<p style='color:red;font-size: 0.9em;'> !: Incorrect password.</p>";} 
        ?>
        <input type="submit" id='submit' value='Login' >
        <a href="forgot-password.php" id="forgot_pswd" style='color:rgba(50, 50, 50, 1);'><p style='color:rgba(50, 50, 50, 1); text-decoration: underline gray; font-size: 0.9em;'>Change the password!</p></a>
    </form>
    </div>

</header>

</body>
</html>