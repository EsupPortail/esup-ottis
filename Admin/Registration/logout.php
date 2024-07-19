<?php
	// Initialiser la session
	session_start();
	$_SESSION["from"]="logout";
	// Détruire la session.
	if(session_destroy())
	{
		// Redirection vers la page de connexion
		header("Location: login.php");
	}
?>