<?php

if ( isset($_POST['Upload']) && isset($_FILES['uploaded']) && is_uploaded_file($_FILES['uploaded']['tmp_name']) ) {
    // Use fileinfo to determine real MIME type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($_FILES['uploaded']['tmp_name']);

    // Allowed MIME types and extensions
    $allowed = [
        'image/jpeg'           => 'jpg',
        'image/png'            => 'png',
        'image/gif'            => 'gif',
        'application/pdf'      => 'pdf'
    ];

    // Check MIME whitelist
    if (!isset($allowed[$mime])) {
        $html .= '<pre>Upload denied: invalid or disallowed file type.</pre>';
    } else {
        // Reject suspicious original filenames (double extensions, php, phtml, etc.)
        $original = basename($_FILES['uploaded']['name']);
        if (preg_match('/\.(php|phtml|phar|pl|py|sh|exe|cgi|asp|aspx|jsp)$/i', $original)) {
            $html .= '<pre>Upload denied: forbidden file extension in filename.</pre>';
        } else {
            // Generate safe random filename
            try {
                $ext = $allowed[$mime];
                $filename = bin2hex(random_bytes(16)) . '.' . $ext;
            } catch (Exception $e) {
                // Fallback if random_bytes fails
                $filename = uniqid('f_', true) . '.' . $ext;
            }

            // Store uploads outside the webroot for safety
            $upload_dir = '/var/uploads/dvwa/';
            if (!is_dir($upload_dir)) {
                if (!mkdir($upload_dir, 0750, true)) {
                    $html .= '<pre>Server error: could not create upload directory.</pre>';
                    // stop here
                }
                @chown($upload_dir, 'www-data');
                @chmod($upload_dir, 0750);
            }

            if (empty($html)) {
                $target = $upload_dir . $filename;

                // Move file to safe location
                if (!move_uploaded_file($_FILES['uploaded']['tmp_name'], $target)) {
                    $html .= '<pre>Upload failed: could not move uploaded file.</pre>';
                } else {
                    // Optional: set safe permissions on saved file
                    @chown($target, 'www-data');
                    @chmod($target, 0640);

                    // Do NOT reveal full filesystem path in production; for lab evidence it's okay to show the filename only
                    $html .= '<pre>Upload successful. Stored as: ' . htmlspecialchars($filename, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</pre>';
                }
            }
        }
    }
}


?>
