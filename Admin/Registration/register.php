<!DOCTYPE html>
<html>
<head>
<title>OMIST - Register</title>
<link rel="icon" type="image/png" href="../../images/favicon.svg" >
<link rel="stylesheet" href="style.css" />
</head>
<body>
<?php
require('config.php');
if (isset($_REQUEST['username'], $_REQUEST['email'], $_REQUEST['password'], $_REQUEST['host'], $_REQUEST['needs'])){
  // récupérer le nom d'utilisateur 
  $username = stripslashes($_REQUEST['username']);
  $username = mysqli_real_escape_string($conn, $username); 
  // récupérer l'email 
  $email = stripslashes($_REQUEST['email']);
  $email = mysqli_real_escape_string($conn, $email);
  // récupérer le mot de passe 
  $password = stripslashes($_REQUEST['password']);
  $password = mysqli_real_escape_string($conn, $password);
  $host = stripslashes($_REQUEST['host']);
  $host = mysqli_real_escape_string($conn, $host);
  $needs = stripslashes($_REQUEST['needs']);
  $needs = mysqli_real_escape_string($conn, $needs);
  $query = "INSERT into `users` (username, email, type, password, host, needs)
        VALUES ('$username', '$email', 'user', '".hash('sha256', $password)."','$host','$needs')";
  $res = mysqli_query($conn, $query);
    if($res){
       echo "<div class='sucess'>
             <h3>Vous êtes inscrit avec succès.</h3>
             <p>Cependant, votre compte doit encore être activé pour être utilisable, ceci peut prendre plusieurs heures. Si vous avez besoin qu'il soit activé plus rapidement, veuillez envoyer un message à <a href=\"mailto:omist-dev@univ-nantes.fr?subject=[OMIST Activation]%20".$username."\">omist-dev@univ-nantes.fr</a>.</p>
             <p>Lorsque votre compte sera activé, cliquez ici pour vous <a href='login.php'>connecter</a></p>
       </div>";



       
       //$mail     = 'no-reply@'.str_replace('www.', '', $_SERVER['SERVER_NAME']);
       $type = 'plain'; // or HTML
       $charset = 'utf-8';
       $mail     = "bourdon-j@univ-nantes.fr";
       $uniqid   = md5(uniqid(time()));
       $headers  = 'From: '.$mail."\n";
       $headers .= 'Reply-to: '.$mail."\n";
       $headers .= 'Return-Path: '.$mail."\n";
       $headers .= 'Message-ID: <'.$uniqid.'@'.$_SERVER['SERVER_NAME'].">\n";
       $headers .= 'MIME-Version: 1.0'."\n";
       $headers .= 'Date: '.gmdate('D, d M Y H:i:s', time())."\n";
       $headers .= 'X-Priority: 3'."\n";
       $headers .= 'X-MSMail-Priority: Normal'."\n";
       $headers .= 'Content-Type: multipart/mixed;boundary="----------'.$uniqid.'"'."\n";
       $headers .= '------------'.$uniqid."\n";
       $headers .= 'Content-type: text/'.$type.';charset='.$charset.''."\n";
       $headers .= 'Content-transfer-encoding: 7bit';

       $subject = "[OMIST ACCOUNT] ".$username;
       $to = "bourdon-j@univ-nantes.fr";

       $message = "Compte à activer : ".$username.", ".$email."\n";

       //mail($to, $subject, $message, $headers);

       $subject = "[OMIST ACCOUNT VALIDATION] ".$username;
       $to = $email;
       $type = 'plain'; // or HTML
       $charset = 'utf-8';
       $message = "Bonjour ".$username." \n\n Votre demande de création de compte a été transmise au gestionnaire.\n\nIl est maintenant en attente d'activation.\n\n Merci d'utiliser nos services.\n\nCordialement \n\nJérémie Bourdon.\n\n\n\n---\n\n\n\n Hello ".$username." \n\n Your request to create an account has been sent to the administrator. \n\n It is now awaiting activation. \n\n Thank you for using our services.\n\n Sincerely \n\n Jérémie Bourdon.";

       //mail($to, $subject, $message, $headers);
      
      
    }
}else{
?>
<form class="box" action="" method="post">
  <h1 class="box-logo box-title">
    OMIST Tools (external use)
  </h1>
    <h1 class="box-title">Sign up</h1>
  <input type="text" class="box-input" name="username" 
  placeholder="User name" required />
  
  <input type="text" class="box-input" name="host" 
  placeholder="Institutiont" required />

  <input type="text" class="box-input" name="needs" 
  placeholder="Describe your requirements (less than 200 characters)" required maxlength="200"/>


    <input type="text" class="box-input" name="email" 
  placeholder="Email" required />
  
    <input type="password" class="box-input" name="password" 
  placeholder="Password" required />
  
    <input type="submit" name="submit" 
  value="Sign up" class="box-button" />
  
    <p class="box-register">Déjà inscrit? 
  <a href="login.php">Connectez-vous ici</a></p>
</form>
<?php } ?>
</body>
</html>