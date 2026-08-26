<!doctype html>
<html lang="fr">
<head>
	<title>OMIST - Remote</title>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./styles/remote.css">
    <link rel="icon" type="image/png" href="./images/favicon.svg" >
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.6/purify.min.js"></script>
    <script src="js/common-func.js"></script>
</head>
<body>
    <input type="hidden" id="classroomid" value="<?php echo $this->escape($classroomid); ?>">
    <input type="hidden" id="appid" value="<?php echo $this->escape($appid); ?>">
    <input type="hidden" id="LANG" value="<?php echo $this->escape($lang); ?>">
    <?php echo csrfField(); ?>

    <div id="remote-container">
        <!-- Remote control interface -->
    </div>

    <script>
    // Remote control functions
    function remote(myorder) {
        if (document.getElementById('classroomid').value != "") {
            var f = new FormData();
            f.append('ROOMID', document.getElementById('classroomid').value);
            f.append('ORDER', myorder);
            
            // Add CSRF token
            var csrfToken = document.querySelector('input[name="csrf_token"]').value;
            if (csrfToken) {
                f.append('csrf_token', csrfToken);
            }
            
            createXhrRequest("POST", "saveremoteorder.php", f, function(responseText) {
                var order = responseText;
            });
        }
    }

    function remotespeech() {
        if (document.getElementById('classroomid').value != "") {
            var f = new FormData();
            f.append('ROOMID', document.getElementById('classroomid').value);
            f.append('SPEECH', 'read');
            
            createXhrRequest("POST", "savespeechforremote.php", f, function(responseText) {
                var data = responseText;
                var Tspeech = data.split(':|:');
                // Process speech data
            });
        }
    }
    </script>
</body>
</html>
