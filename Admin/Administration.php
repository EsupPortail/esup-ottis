<?php
// Empêche la mise en cache
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: no-cache, must-revalidate');

?>
<?php

include("../Security/Authentification.php");

$uid="";
if (isset($_SERVER['PHP_AUTH_USER'])) $uid = $_SERVER['PHP_AUTH_USER'];
if ($uid == "") $uid="anonymous"; //$uid="e18XXX"; // en attendant de rétablir le CAS....
if (strpos($_SERVER['SERVER_NAME'],"univ-nantes.fr") === false) $uid="anonymous";

if (! isset($external)) $external=false;

if ($external) $uid="external";

//if ($_SERVER['PHP_AUTH_USER'] == "bourdon-j" && isset($_GET["fakeuser"])) {$fake=true; $uid=$_GET["fakeuser"];} else {$fake=false;}
$fake=false;

//AuthenticateNotStudent($uid);
$filenameids = "../tables/authorizedStudents.txt";
AuthenticateNotStudentorReader($uid,$filenameids);

// Mettre en place une liste de privilégiés
// =1 peut inviter et fixer la durée de l'invitation
// =2 peut inviter pour 24h
// sinon ne peut pas inviter

$userprivileges=getPrivilege($uid,"../tables/listAdminID.txt",2);
if ($userprivileges>1) {
    echo "Access forbidden !";
    exit;
}

$theme="flat";
if (isset($_GET["theme"])) $theme=$_GET["theme"];

?>
<!doctype html>
<html lang="en">
<head>
	<title>Slide Manipulation Tools</title>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if ($theme != "") {
    echo '<link href="../jquery/'.$theme.'/jquery-ui.min.css" rel="stylesheet">';
    echo '<link href="../jquery/'.$theme.'/extras.css" rel="stylesheet">';
  } else {
    echo '<link href="../jquery/jquery-ui.min.css" rel="stylesheet">';
  }
	?>
    <link rel="icon" type="image/png" href="../favicon.png" >
    <?php if ($theme != "") {
    echo '<script src="../jquery/'.$theme.'/external/jquery/jquery.js"></script>';
    echo '<script src="../jquery/'.$theme.'/jquery-ui.js"></script>';
  } else {
    echo '<script src="../jquery/jquery.js"></script>';
    echo '<script src="../jquery/jquery-ui.min.js"></script>';
  }
  ?>
    <link rel="stylesheet" href="../libs/quickmenu/lib/font-awesome/css/font-awesome.min.css">
<script>
function Edit(readfilename) {
    document.getElementById('dialog-filename').innerHTML=readfilename;
    $( "#dialog-edit" ).dialog("open");
    // Read the file content
    document.getElementById('dialog-content').value="Reading, wait a few seconds.....";
    fetch("savefile2.php", {
        method: "post",
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            filename: readfilename,
            content: 'TEST'
        })
    })
    .then( (response) => { 
        var res='';
        for (var p in response) res+=p+'='+response[p]+'\n';
        alert("File readed...\n"+res);
    });
    //.then(function(p) {p.text().then(function (content) {document.getElementById('dialog-content').value=content;})});
}

function Save(savetofilename,content) {
  fetch("savefile.php", {
  method: "post",
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  },

  body: JSON.stringify({
    filename: savetofilename,
    filecontent: content
  })
})
.then( (response) => { 
   alert("File Saved...");
});
}

$( function() {
    $( "#dialog-edit" ).dialog({
      autoOpen: false,
      modal: true,
      width: '95%',
      buttons: {
        "Save": function() {
          Save(document.getElementById('dialog-filename').innerHTML, document.getElementById('dialog-content').value);
          $( this ).dialog( "close" );
        },
        "Cancel": function() {
          $( this ).dialog( "close" );
        }
      }
    });
    $("button").button();
  } );
</script>
<style>
@import url('https://fonts.googleapis.com/css2?family=Concert+One&display=swap');

h1 {
  font-family: "Concert One", sans-serif;
  font-weight: 400;
  font-style: normal;
}

</style>

</head>
<body>
    <main>
        <h1>OMIST : Administration</h1>
    </main>
    <button onclick="Edit('../tables/tokens.json');"><i class="fa fa-key" aria-hidden="true"></i>&nbsp; Edit tokens</button>
    <button onclick="Edit('../tables/authorizedStudents.txt');"><i class="fa fa-graduation-cap" aria-hidden="true"></i>&nbsp;Edit Authorized students</button>
    <button onclick="Edit('../tables/listAdminID.txt');"><i class="fa fa-user" aria-hidden="true"></i>&nbsp;Edit privileged users</button>
    <button onclick="Edit('../tables/test.txt');">Edit test</button>

<div id="dialog-edit" title="Edit a file" style="width: 95%;">
  <p>
    Filename : <span id="dialog-filename"></span>
  </p>
  <p>
    <form method="POST">
    <textarea id="dialog-content" style="width: 100%; height: 400px"></textarea>
    </form>
  </p>
</div>
 
</body>
</html>
