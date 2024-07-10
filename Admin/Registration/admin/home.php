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
	<script>
		function Edit(id,field,value,newval="") {
			if (newval=="") newval=prompt("Edition de la valeur "+field+" pour l'utilisateur "+id,value);
			var data = new FormData();
			data.append('field', field);
			data.append('value', newval);
			data.append('id', id);

			var xhr = new XMLHttpRequest();
			xhr.open('POST', 'setvalue.php', true);
			xhr.onload = function () {
			    alert("Mise à jour de "+id+"\n"+field+"="+newval);
				window.reload();
			};
			xhr.send(data);
		}
	</script>
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


<div style="width: 90%; background-color: white;"><h1>All users</h1>
<table border="1" style="width: 90%; border: 1 solid; border-collapse: collapsed;">
	<tr>
		<th>Id</th><th>username</th><th>email</th><th>type</th><th>active</th>
	</tr>
<?php
include("../config.php");

$query = "SELECT * FROM `users`";
$result = mysqli_query($conn,$query) or die(mysql_error());

while ($user=mysqli_fetch_assoc($result)) {
//	print_r($user);
	$res="";
	$res.="<td>".$user["id"]."</td>";
	$res.="<td>".$user["username"]."</td>";
	$res.="<td>".$user["email"]."</td>";
	$res.="<td>".$user["type"]."</td>";
	$res.="<td onclick=\"Edit(".$user["id"].",'active','".($user["active"]!="YES"?"NO":"YES")."')\">".($user["active"]!="YES"?"NO":"YES")."</td>";
	echo "<tr>".$res."</tr>\n";
}
?>
</table>
</div>
	</body>
</html>