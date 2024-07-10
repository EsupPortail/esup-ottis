<?php
	// Initialiser la session
	session_start();
	// Vérifiez si l'utilisateur est connecté, sinon redirigez-le vers la page de connexion
	if(!isset($_SESSION["username"])){
		header("Location: ../login.php");
		exit(); 
	}

    include("../config.php");

$field="";
if(isset($_POST["field"])) $field=$_POST["field"];
$value="";
if(isset($_POST["value"])) $value=$_POST["value"];
$id="";
if(isset($_POST["id"])) $id=$_POST["id"];

if ($field !="" && $id!="") {
    $query = "update users set ".$field."='".$value."' where id=".$id.";";
    $result = mysqli_query($conn,$query) or die(mysql_error());
}



?>