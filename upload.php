<?php
include "includes/init.php";

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");


$targetDir = 'uploads/proiecte';
$cleanupTargetDir = true;
$maxFileAge = 5 * 3600;

if (!file_exists($targetDir)) {
    @mkdir($targetDir);
}

if (isset($_REQUEST["name"])) {
    $fileName = $_REQUEST["name"];
} elseif (!empty($_FILES)) {
    $fileName = $_FILES["file"]["name"];
} else {
    $fileName = uniqid("file_");
}

$filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

$chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;

if ($cleanupTargetDir) {
    if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
        die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
    }

    while (($file = readdir($dir)) !== false) {
        $tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

        if ($tmpfilePath == "{$filePath}.part") {
            continue;
        }
        if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge)) {
            @unlink($tmpfilePath);
        }
    }
    closedir($dir);
}


if (!$out = @fopen("{$filePath}.part", $chunks ? "ab" : "wb")) {
    die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
}

if (!empty($_FILES)) {
    if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
        die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
    }

    if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
        die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
    }
} else {
    if (!$in = @fopen("php://input", "rb")) {
        die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
    }
}

while ($buff = fread($in, 4096)) {
    fwrite($out, $buff);
}

@fclose($out);
@fclose($in);

if (!$chunks || $chunk == $chunks - 1) {
    rename("{$filePath}.part", $filePath);

    $user_id = $_SESSION['id'];

    $sqlLastIndex = "SELECT MAX(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(photo_name, '_', -2), '_', 1) AS UNSIGNED)) AS max_index FROM photos WHERE user_id = :user_id";
    $stmtLastIndex = $db->prepare($sqlLastIndex);
    $stmtLastIndex->execute(['user_id' => $user_id]);
    $lastIndexData = $stmtLastIndex->fetch(PDO::FETCH_ASSOC);
    $lastIndex = is_numeric($lastIndexData['max_index']) ? (int) $lastIndexData['max_index'] : 0;

    $newIndex = $lastIndex + 1;

    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $newPhotoName = 'photo_' . $newIndex . '_' . $user_id . '.' . $fileExtension;

    $finalFilePath = $targetDir . DIRECTORY_SEPARATOR . $newPhotoName;
    rename($filePath, $finalFilePath);

    $sql = "INSERT INTO photos (user_id, photo_name) VALUES (:user_id, :photo_name)";
    $stmt = $db->prepare($sql);
    $stmt->execute(['user_id' => $user_id, 'photo_name' => $newPhotoName]);
}

die('{"jsonrpc" : "2.0", "result" : {"status": 200, "message": "The file has been uploaded successfully!"}}');
