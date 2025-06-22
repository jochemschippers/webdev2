<?php
// app/utils/ImageUploader.php

namespace App\Utils;

class ImageUploader {
    // Absolute path where uploaded images will be stored on the server.
    // This points to the 'public/uploads/graphic_cards' directory relative to the backend root.
    private static $uploadDir = __DIR__ . '/../../public/uploads/graphic_cards';
    
    // Relative web path for serving the images from the frontend.
    // This is the path the frontend will use in <img> tags (e.g., http://localhost/uploads/graphic_cards/...).
    private static $webPath = '/uploads/graphic_cards/';
    
    // Allowed MIME types for image uploads.
    private static $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    
    // Maximum allowed file size for uploads (5 MB).
    private static $maxFileSize = 5 * 1024 * 1024; // 5 MB in bytes

    /**
     * Constructor for ImageUploader.
     * Ensures that the target upload directory exists. If not, it attempts to create it.
     */
    public function __construct() {
        // Check if the upload directory exists. If not, create it with appropriate permissions.
        // The 0777 permission is broad; consider a more restrictive permission like 0755 in production.
        if (!is_dir(self::$uploadDir)) {
            mkdir(self::$uploadDir, 0777, true); // Create recursively with full permissions
            error_log("ImageUploader: Created upload directory: " . self::$uploadDir);
        }
    }

    /**
     * Handles the upload of a single image file from a temporary location to the permanent upload directory.
     * Performs basic validation (upload errors, file type, file size).
     *
     * @param array $fileData The associative array representing the uploaded file, typically from $_FILES.
     * Expected keys: 'error', 'type', 'size', 'tmp_name', 'name'.
     * @return string|false The relative web path to the uploaded image on success (e.g., '/uploads/graphic_cards/unique_name.png'),
     * or false on any failure (validation, move operation).
     */
    public function uploadImage(array $fileData): string|false {
        // Step 1: Check for PHP upload errors
        if ($fileData['error'] !== UPLOAD_ERR_OK) {
            error_log("ImageUploader: File upload error detected: " . $fileData['error']);
            return false;
        }

        // Step 2: Validate file type (MIME type)
        if (!in_array($fileData['type'], self::$allowedTypes)) {
            error_log("ImageUploader: Invalid file type detected: " . $fileData['type'] . ". Allowed types: " . implode(', ', self::$allowedTypes));
            return false;
        }

        // Step 3: Validate file size
        if ($fileData['size'] > self::$maxFileSize) {
            error_log("ImageUploader: File size exceeds maximum allowed: " . $fileData['size'] . " bytes. Max allowed: " . self::$maxFileSize . " bytes.");
            return false;
        }

        // Step 4: Generate a unique filename to prevent conflicts and ensure security.
        // Uses uniqid() for uniqueness and preserves the original file extension.
        $extension = pathinfo($fileData['name'], PATHINFO_EXTENSION);
        $filename = uniqid('gc_') . '.' . $extension; // e.g., 'gc_654321abcdef.png'
        
        // Construct the full absolute path where the file will be saved.
        $destinationPath = self::$uploadDir . '/' . $filename;
        
        // Construct the relative web path that will be stored in the database and used by the frontend.
        $webPath = self::$webPath . $filename;

        // Step 5: Move the uploaded file from its temporary location to the permanent destination.
        // move_uploaded_file() is crucial for security as it ensures the file was uploaded via HTTP POST.
        if (move_uploaded_file($fileData['tmp_name'], $destinationPath)) {
            error_log("ImageUploader: File uploaded successfully from '{$fileData['tmp_name']}' to '{$destinationPath}'. Web path: '{$webPath}'");
            return $webPath; // Return the web-accessible path
        } else {
            error_log("ImageUploader: Failed to move uploaded file. Possible permissions issue or destination error. Source: '{$fileData['tmp_name']}', Destination: '{$destinationPath}'");
            return false;
        }
    }

    /**
     * Deletes an image file from the server given its relative web path.
     *
     * @param string $webPath The relative web path to the image (e.g., '/uploads/graphic_cards/gc_123.png').
     * @return bool True on successful deletion, false if the file does not exist or deletion fails.
     */
    public function deleteImage(string $webPath): bool {
        // Construct the absolute file system path from the web path.
        // It's important to build this path correctly based on your server's document root.
        $filePath = __DIR__ . '/../../public' . $webPath; // Backend root -> public folder -> web path

        // Check if the file exists and is indeed a file (not a directory) before attempting deletion.
        if (file_exists($filePath) && is_file($filePath)) {
            if (unlink($filePath)) { // Attempt to delete the file.
                error_log("ImageUploader: Successfully deleted image file: " . $filePath);
                return true;
            } else {
                error_log("ImageUploader: Failed to delete image file: " . $filePath . ". Check file permissions.");
                return false;
            }
        }
        error_log("ImageUploader: Image file not found for deletion at path: " . $filePath);
        return false; // File doesn't exist or is not a regular file
    }
}
