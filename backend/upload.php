<?php
require_once './lib/utils.php';
require_once './lib/DB.php';

// Check user authentication
$user = getLoggedUser();

#if ($user == null) {
#    header("Location: login.php");
#    exit();
#}

function handleUpload()
{
    if (!isset($_POST['novel_type'])) {
        return "Please select a novel type.";
    }
    
    $db = DB::getInstance();
    
    if ($_POST['novel_type'] === 'short') {
        if (!isset($_POST['title']) || !isset($_POST['content'])) {
            return "Title and content are required for a short novel.";
        }
        
        $title = $_POST['title'];
        $content = $_POST['content'];

        // Check title, content
        if (!is_string($title) || !is_string($content)) {
            return "Invalid title or content";
        }

        // TODO Check if premium
        // In "novels" we need a boolean field 'premium'
        $premium = isset($_POST['premium']) ? 1 : 0;
        
        $db->exec('INSERT INTO `novels` (`user_id`, `title`, `content`, `type`) VALUES (:user_id, :title, :content, "short")', [
            'user_id' => $user['id'],
            'title' => $title,
            'content' => $content
        ]);
        
        return "Short novel uploaded successfully!";
    } elseif ($_POST['novel_type'] === 'long') {
        if (!isset($_FILES['pdf']) || $_FILES['pdf']['error'] != UPLOAD_ERR_OK) {
            return "Error uploading PDF file.";
        }
        
        $pdfPath = 'uploads/' . basename($_FILES['pdf']['name']);
        if (!move_uploaded_file($_FILES['pdf']['tmp_name'], $pdfPath)) {
            return "Failed to save the uploaded file.";
        }
        
        $title = $_POST['title'] ?? "Untitled Novel";

        if (!is_string($title)) {
            return "Invalid title";
        }        

        // TODO check if premium
        $premium = isset($_POST['premium']) ? 1 : 0;
        
        $db->exec('INSERT INTO `novels` (`user_id`, `title`, `file_path`, `type`) VALUES (:user_id, :title, :file_path, "long")', [
            'user_id' => $user['id'],
            'title' => $title,
            'file_path' => $pdfPath
        ]);
        
        return "Long novel uploaded successfully!";
    }
    
    return "Invalid novel type.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $error_msg = handleUpload();
}

$title = "Upload Your Novel";
require_once "template/header.php";
?>

<div class="flex flex-col items-center justify-center px-6 py-8 mx-auto my-auto lg:py-0">
    <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl">Upload Your Novel</h1>
    <div class="w-full bg-white rounded-lg shadow sm:max-w-md p-6">
        <form class="space-y-4" action="" method="POST" enctype="multipart/form-data">
            <div>
                <label for="novel_type" class="block text-sm font-medium text-gray-900">Novel Type</label>
                <select name="novel_type" id="novel_type" class="w-full p-2 border rounded">
                    <option value="short">Short Novel</option>
                    <option value="long">Long Novel (PDF)</option>
                </select>
                <label>
                    <input type="checkbox" name="premium" value="0"> Premium
                </label>
            </div>
            <div>
                <label for="title" class="block text-sm font-medium text-gray-900">Title</label>
                <input type="text" name="title" id="title" class="w-full p-2 border rounded" required>
            </div>
            <div id="short_novel_section">
                <label for="content" class="block text-sm font-medium text-gray-900">Content</label>
                <textarea name="content" id="content" class="w-full p-2 border rounded"></textarea>
            </div>
            <div id="long_novel_section" class="hidden">
                <label for="pdf" class="block text-sm font-medium text-gray-900">Upload PDF</label>
                <input type="file" name="pdf" id="pdf" class="w-full p-2 border rounded" accept="application/pdf">
            </div>
            <?php if (isset($error_msg)) { ?>
                <p class="text-sm text-red-600"> <?php echo $error_msg; ?> </p>
            <?php } ?>
            <button type="submit" class="w-full text-white bg-blue-600 hover:bg-blue-700 p-2 rounded">Upload</button>
        </form>
    </div>
</div>

<script>
document.getElementById('novel_type').addEventListener('change', function() {
    if (this.value === 'short') {
        document.getElementById('short_novel_section').classList.remove('hidden');
        document.getElementById('long_novel_section').classList.add('hidden');
    } else {
        document.getElementById('short_novel_section').classList.add('hidden');
        document.getElementById('long_novel_section').classList.remove('hidden');
    }
});
</script>

