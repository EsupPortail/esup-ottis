<?php
    // include("index.php");
	// Initialiser la session
	session_start();
    $external=true;
	// Vérifiez si l'utilisateur est connecté, sinon redirigez-le vers la page de connexion
	if(!isset($_SESSION["username"])){
		header("Location: Admin/Registration/login.php");
		exit(); 
	}
	include("index.php");
?>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0">
<script>
	document.getElementById('logonextbar').innerHTML+='<a href="Admin/Registration/logout.php"><span title="Logout" class="material-symbols-outlined">logout</span></a>';
</script>
