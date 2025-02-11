<?php
require_once './lib/utils.php';
require_once './lib/DB.php';

// Check if the user is logged
$user = getLoggedUser();

// User not authenticated, page not found
if ($user == null) {
    raiseNotFound();
}

// Check if a file is provided
if (!isset($_GET['novel_id'])) {
    die("Error: No file specified.");
}

// Sanitize the filename (prevent directory traversal attacks)
$fileName = basename($_GET['novel_id']);
$filePath = STORAGE . $fileName;

// Check if the file exists and is readable
if (!file_exists($filePath) || !is_readable($filePath)) {
    die("Error: File not found.");
}

// Set headers to force download
header("Content-Type: application/pdf");
header("Content-Disposition: attachment; filename=\"$fileName\"");
header("Content-Length: " . filesize($filePath));

// Read and output the file
readfile($filePath);
exit();
?>
