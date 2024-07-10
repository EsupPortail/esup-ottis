<?php
	// Initialiser la session
	session_start();
	// Vérifiez si l'utilisateur est connecté, sinon redirigez-le vers la page de connexion
	if(!isset($_SESSION["username"])){
		header("Location: ../login.php");
		exit(); 
	}
?>
<!DOCTYPE html>
<html>
	<head>
	<link rel="stylesheet" href="../style.css" />
	</head>
	<body>
		<div class="sucess">
		<h1>Bienvenue <?php echo $_SESSION['username']; ?>!</h1>
		<p>C'est votre espace admin.</p>
		<a href="add_user.php">Add user</a> | 
		<a href="#">Update user</a> | 
		<a href="#">Delete user</a> | 
		<a href="../logout.php">Déconnexion</a>
		</ul>
		</div>


<div><h1>All users</h1>
<table>
	<tr>
		<th>username</th><th>email</th><th>type</th><th>active</th>
	</tr>
<?php
include("../config.php");

$query = "SELECT * FROM `users`";
$result = mysqli_query($conn,$query) or die(mysql_error());
for ($i=0; $i<mysqli_num_rows($result); $i++) {
	$user=mysqli_fetch_assoc($result);
	$res="<td>".$user["username"]."</td>";
	$res="<td>".$user["email"]."</td>";
	$res="<td>".$user["type"]."</td>";
	$res="<td>".$user["active"]."</td>";
	echo "<tr>".$res."</tr>\n";
}
?>
</table>
</div>
	</body>
</html>