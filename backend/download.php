<?php
require_once './lib/utils.php';
require_once './lib/DB.php';

// TODO This page should not be necessary, because the download is requested from the homepage
// Check if the user is logged
$user = getLoggedUser();

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
