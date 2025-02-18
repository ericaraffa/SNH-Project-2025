<?php
require_once './lib/utils.php';
require_once './lib/DB.php';

// Check user authentication

$user = getLoggedUser();

if ($user == null) {
    header("Location: login.php");
    exit();
}

function handleUpload()
{
    global $user;

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

        // Check if premium
        // In "novel" we need a boolean field 'premium'
        $premium = isset($_POST['premium']) ? 1 : 0;

                
        $ans = $db->exec('INSERT INTO `novel` (`title`, `text`, `premium`) VALUES (:title, :text, :premium)', [
            'title' => $title,
            'text' => $content,
            'premium' => $premium
        ]);

        if($ans === false){
            return "Error uploading the novel.";
        } else {
            return "Short novel uploaded successfully!";
        }
        
        
    } elseif ($_POST['novel_type'] === 'long') {

        // Error handling
        if (!isset($_POST['title'])) {
            return "Title is required for the novel.";
        }

        // Check if a file was uploaded
        if (!isset($_FILES['pdf']) && $_FILES['pdf']['error'] !== UPLOAD_ERR_OK) {
            return "Please select a valid PDF file";
        }

        // Check boundary size
        if ($_FILES['pdf']['size'] > MAX_SIZE_PDF){
            security_log("Attempt to upload file the exceeds max size permitted.");
            return "Error PDF file exceeds max size.";
        }

        $file = $_FILES['pdf'];
        $fileName = $file['name'];
        $fileTmpPath = $file['tmp_name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Allowed file type
        $allowedMimeTypes = ['application/pdf'];
        $allowedExtensions = ['pdf'];

        // Validate file extension
        if (!in_array($fileExtension, $allowedExtensions)) {
            return "Invalid file type.";
        }

        // Validate MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE); // PHP's built-in file info
        $detectedType = finfo_file($finfo, $fileTmpPath);
        finfo_close($finfo);

        if (!in_array($detectedType, $allowedMimeTypes)) {
            return "Invalid file type.";
        }

        $title = $_POST['title'];
        if (!is_string($title)) {
            return "Invalid title";
        }        

        // Check if premium
        $premium = isset($_POST['premium']) ? 1 : 0;
        
        // Insert in database
        $ans = $db->exec('INSERT INTO `novel` (`title`, `text`, `premium`) VALUES (:title, NULL, :premium)', [
            'title' => $title,
            'premium' => $premium
        ]);

        if($ans === false){
            return "Error uploading PDF file.";
        } else {        
            $novelId = $db->lastInsertId();
            $fileName = basename($novelId);
            $pdfPath = STORAGE . $fileName . ".pdf";
        
            # Upload file .pdf
            if (!move_uploaded_file($_FILES['pdf']['tmp_name'], $pdfPath)) {
                return "Failed to save the uploaded file.";
            }
            return "Long novel uploaded successfully!";
        }
    }
    
    return "Invalid novel type.";
}

if (isPost()) {
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
                <input type="text" name="title" id="title" class="w-full p-2 border rounded" maxlength="<?php echo MAX_CHAR_TITLE; ?>" required>
            </div>
            <div id="short_novel_section">
                <label for="content" class="block text-sm font-medium text-gray-900">Content</label>
                <textarea name="content" id="content" class="w-full p-2 border rounded" maxlength= <?php echo MAX_CHAR_NOVEL;?>></textarea>
                <p id="charCount">0 / <?php echo MAX_CHAR_NOVEL;?> characters</p>
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
    // To change the content of the form (short vs full-length novels)
    document.getElementById('novel_type').addEventListener('change', function() {
        if (this.value === 'short') {
            document.getElementById('short_novel_section').classList.remove('hidden');
            document.getElementById('long_novel_section').classList.add('hidden');
        } else {
            document.getElementById('short_novel_section').classList.add('hidden');
            document.getElementById('long_novel_section').classList.remove('hidden');
        }
    });

    // To update the textarea counter
    document.getElementById("content").addEventListener("input", function() {
        document.getElementById("charCount").textContent = this.value.length + " / " + <?php echo MAX_CHAR_NOVEL;?>;
    });

    // To check the selected file
    document.addEventListener("DOMContentLoaded", function () {
        const novelTypeSelect = document.getElementById("novel_type");
        const pdfInput = document.getElementById("pdf");
        const maxFileSize = <?php echo MAX_SIZE_PDF; ?>; // 1 MB

        // Validate the PDF file on form submission
        pdfInput.addEventListener("change", function (event) {
            
            // Only validate if the selected novel type is "long"
            if (novelTypeSelect.value === "long") {
                const file = pdfInput.files[0];

                // Check if a file is selected
                if (!file) {
                    alert("Please select a PDF file.");
                    event.preventDefault();
                    return;
                }

                // Check if the file size exceeds the limit
                if (file.size > maxFileSize) {
                    alert("The file is too large. Maximum size is 1 MB.");
                    pdfInput.value = ""; // Clear the input field
                    event.preventDefault();
                }
            }
        });
    });

</script>

