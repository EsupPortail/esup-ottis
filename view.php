<?php
if (isset($_GET["file"])) {
$file = $_GET["file"];
$tmpfile="tmp/content.pdf";
exec("https_proxy='http://cache.univ-nantes.fr:3128' curl -s ".$file." -o ".$tmpfile);
$filename = 'inline.pdf';
header('Content-type: application/pdf');
header('Content-Disposition: inline; filename="' . $filename . '"');
header('Content-Transfer-Encoding: binary');
header('Content-Length: ' . filesize($tmpfile));
header('Accept-Ranges: bytes');
@readfile($tmpfile);
unlink($tmpfile);
}
?>