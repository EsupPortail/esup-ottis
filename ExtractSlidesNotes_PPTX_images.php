<?php
ini_set("upload_max_filesize", "50M");
ini_set("post_max_size", "50M");
include("config.php");

// Debug: Log all input
error_log("POST: " . print_r($_POST, true));
error_log("FILES: " . print_r($_FILES, true));

header('Content-Type: application/json');

// Check for input
if (!isset($_FILES['ppt_presentation']) && !isset($_POST["uncloudlink"])) {
    echo json_encode(["error" => "No file or uncloudlink provided"]);
    exit;
}

// Handle file upload or uncloudlink
if (isset($_POST["uncloudlink"]) && trim($_POST["uncloudlink"]) != "") {
    $tmp_file = "/var/www/html/uploads/uncloudfile_" . md5(gmdate("U")) . ".pptx";
    $uncloudlink = $_POST["uncloudlink"];
    if (strpos($uncloudlink, "download") === false) $uncloudlink .= "/download";
    $command = $proxy . 'curl -q ' . escapeshellarg($uncloudlink) . ' -o ' . escapeshellarg($tmp_file);
    $out = exec($command);
    if (!file_exists($tmp_file)) {
        echo json_encode(["error" => "Failed to download file from uncloudlink"]);
        exit;
    }
} else {
    if (!isset($_FILES['ppt_presentation']) || $_FILES['ppt_presentation']['error'] !== UPLOAD_ERR_OK) {
        header('Content-Type: application/json');
        echo json_encode(["error" => "Uploaded file not found or upload error: " . ($_FILES['ppt_presentation']['error'] ?? 'No file')]);
        exit;
    }
    $tmp_file = $_FILES['ppt_presentation']['tmp_name'];
    error_log("Temporary file path: " . $tmp_file);
    if (!file_exists($tmp_file)) {
        header('Content-Type: application/json');
        echo json_encode(["error" => "Temporary file does not exist: $tmp_file"]);
        exit;
    }
}

// Generate HTML
$randompath = md5(gmdate("U"));
$omistToolsDir = __DIR__ . '/OMIST_Tools';
$command = 'cd ' . escapeshellarg($omistToolsDir) . '; bash ./CreateApp/CreateAppForInstantTranslator.sh ' . escapeshellarg($tmp_file) . ' ' . escapeshellarg($randompath) . ' 2>&1';
exec($command, $commandOutput, $returnVar);
if ($returnVar !== 0) {
    error_log("ExtractSlidesNotes_PPTX_images failed (return=$returnVar): " . implode("\n", $commandOutput));
}
$tmp_file = $omistToolsDir . '/tmp/output_' . $randompath . '.html';

if (!file_exists($tmp_file)) {
    error_log("Expected generated HTML file not found: $tmp_file");
    if (!empty($commandOutput)) {
        echo json_encode(["error" => "Failed to generate HTML file", "details" => trim(implode(" | ", $commandOutput))]);
    } else {
        echo json_encode(["error" => "Failed to generate HTML file"]);
    }
    exit;
}

$contenttxt = file_get_contents($tmp_file);
if ($contenttxt === false) {
    echo json_encode(["error" => "Failed to read generated HTML file"]);
    exit;
}

$content = explode("\n", $contenttxt);
$txtslidenotes = "";
$T_images = array();

foreach ($content as $line) {
    if (strpos("_$line", "var slidesnotes = ") > 0) {
        $txtslidenotes = str_replace("var slidesnotes = ", "", $line);
        $txtslidenotes = substr($txtslidenotes, 0, strlen($txtslidenotes) - 1);
    }
    if (strpos("_$line", "Timages.push(") > 0) {
        $txtimage = str_replace("Timages.push('", "", $line);
        $txtimage = str_replace("');", "", $txtimage);
        $T_images[] = $txtimage;
    }
}

if (empty($txtslidenotes)) {
    echo json_encode(["error" => "No slidesnotes found in the generated file"]);
    exit;
}

$Tnotes = json_decode($txtslidenotes);
if ($Tnotes === null) {
    echo json_encode(["error" => "Invalid JSON in slidesnotes: " . json_last_error_msg()]);
    exit;
}

$notes = [];
foreach ($Tnotes as $lang => $noteslang) {
    if (is_array($noteslang)) {
        $notes = $noteslang;
        break;
    }
}

if (empty($notes)) {
    $notes = [["Slide" => 0, "Notes" => [], "Images" => $T_images]];
} else {
    $n = count($notes);
    $T = array();
    $T["Images"] = $T_images;
    $T["Slide"] = $n;
    $T["Notes"] = array();
    $notes[$n] = $T;
}

echo json_encode($notes);
unlink($tmp_file);
?>