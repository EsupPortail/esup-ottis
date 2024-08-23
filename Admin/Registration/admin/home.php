<?php
	// Initialiser la session
	session_start();
	// Vérifiez si l'utilisateur est connecté, sinon redirigez-le vers la page de connexion
	if(!isset($_SESSION["username"]) || !isset($_SESSION["type"]) || $_SESSION["type"]!="admin"){
		header("Location: ../login.php");
		exit(); 
	}
?>
<!DOCTYPE html>
<html>
	<head>
	<link rel="stylesheet" href="../style.css" />
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
	<script>
		function Edit(id,field,value,newval="") {
			if (newval=="") newval=prompt("Edition de la valeur "+field+" pour l'utilisateur "+id,value);
			if (newval === null) newval=value;
			var data = new FormData();
			data.append('field', field);
			data.append('value', newval);
			data.append('id', id);

			var xhr = new XMLHttpRequest();
			xhr.open('POST', 'setvalue.php', true);
			xhr.onload = function () {
			    //alert("Mise à jour de "+id+"\n"+field+"="+newval);
				location.reload();
			};
			xhr.send(data);
		}
	</script>
	<style>
	#userlist {
		width: 100%;
		text-align: center;
	}

	#userlist table {
		width: 100%;
    	border-collapse: collapse;
    	margin: 25px 0;
    	font-size: 0.9em;
    	font-family: sans-serif;
    	min-width: 400px;
    	box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
		background-color: white;
	}
	#userlist table thead tr {
    	background-color: #009879;
    	color: #ffffff;
    	text-align: left;
	}
	#userlist table th,
	#userlist table td {
    	padding: 12px 15px;
		text-align: left;
	}
	#userlist table tbody tr {
    	border-bottom: 1px solid #dddddd;
	}

	#userlist table tbody tr:nth-of-type(even) {
    	background-color: #f3f3f3;
	}

	#userlist table tbody tr:last-of-type {
    	border-bottom: 2px solid #009879;
	}
	#userlist table tbody tr:hover {
    	background-color: #e3e3e3;
	}


	</style>

	</head>
	<body>
		<div class="sucess">
		<h1>Bienvenue <?php echo $_SESSION['username']; ?>!</h1>
		<a href="add_user.php"><span class="material-symbols-outlined">person_add</span>Add user</a> | 
		<!--<a href="#">Update user</a> | 
		<a href="#">Delete user</a> | -->
		<a href="../logout.php"><span class="material-symbols-outlined">logout</span>Déconnexion</a>
		</ul>
		</div>


<div id="userlist">
<table>
	<thead>
	<tr>
		<th width="10%">Id</th><th width="20%">username</th><th width="30%">email</th><th width="15%">type</th><th width="15%">active</th><!--<th width="7%">Pending</th>--><th width="10%">Action</th>
	</tr>
</thead>
<tbody>
<?php
include("../config.php");

$query = "SELECT * FROM `users` ORDER BY id DESC";
$result = mysqli_query($conn,$query) or die(mysql_error());

while ($user=mysqli_fetch_assoc($result)) {
//	print_r($user);
	$res="";
	$res.="<td>".$user["id"]."</td>";
	$res.="<td onclick=\"Edit(".$user["id"].",'username','".$user["username"]."')\">".$user["username"]."</td>";
	$res.="<td onclick=\"Edit(".$user["id"].",'email','".$user["email"]."')\">".$user["email"]."</td>";
	$res.="<td onclick=\"Edit(".$user["id"].",'type','".($user["type"]!="admin"?"user":"admin")."','".($user["type"]!="admin"?"admin":"user")."')\">".($user["type"]!="admin"?"<span class=\"material-symbols-outlined\">person</span>User":"<span class=\"material-symbols-outlined\">manage_accounts</span>Admin")."</td>";
//	$res.="<td onclick=\"Edit(".$user["id"].",'active','".($user["active"]!="YES"?"NO":"YES")."','".($user["active"]!="YES"?"YES":"NO")."')\">".($user["active"]!="YES"?"NO":"YES")."</td>";
	$res.="<td onclick=\"Edit(".$user["id"].",'active','".($user["active"]!="YES"?"NO":"YES")."','".($user["active"]!="YES"?"YES":"NO")."')\">".($user["active"]!="YES"?"<span style=\"color: red;\" class=\"material-symbols-outlined\">toggle_off</span>":"<span style=\"color: green;\" class=\"material-symbols-outlined\">toggle_on</span>")."</td>";
//	$res.="<td onclick=\"Edit(".$user["id"].",'pending','".($user["pending"]!="YES"?"NO":"YES")."','".($user["pending"]!="YES"?"YES":"NO")."')\">".($user["pending"]!="YES"?"<span style=\"color: red;\" class=\"material-symbols-outlined\">toggle_off</span>":"<span style=\"color: green;\" class=\"material-symbols-outlined\">toggle_on</span>")."</td>";
	$res.="<td>";
	$res.="<span onclick=\"Edit(".$user["id"].",'DELETE','X','X')\" class=\"material-symbols-outlined\">delete_forever</span>";
	$res.="<a style=\"text-decoration: none;\" href=\"mailto:".$user["email"]."?subject=[omist-dev] Compte OMIST activé&body=Bonjour ".$user["username"]."%0D%0A%0D%0AVotre compte a bien été activé.%0D%0A%0D%0AVous pouvez dès à présent utiliser l'outil en vous connectant sur ce lien : https://automatictranslator.sciences.univ-nantes.fr/AutomaticTranslator/scenarios.php?external%0D%0A%0D%0AN'hésitez pas à contacter omist-dev@univ-nantes.fr si vous rencontrez le moindre problème.%0D%0A%0D%0ABonne journée%0D%0A%0D%0AL'équipe OMIST.%0D%0A\" class=\"material-symbols-outlined\">contact_mail</a>";
	$res.="</td>";
	
	echo "<tr>".$res."</tr>\n";
}

?>
</tbody>
</table>
</div>
	</body>
</html>