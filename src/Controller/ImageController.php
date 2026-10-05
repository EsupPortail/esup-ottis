<?php

namespace App\Controller;

use App\Security\RoomTokenManager;
use App\Utils\Validator;

/**
 * ImageController - Handles image reading and saving operations
 */
class ImageController extends BaseController
{
    /**
     * Base tmp directory path
     */
    private string $tmpDir;

    protected function initialize(): void
    {
        parent::initialize();
        $this->tmpDir = __DIR__ . '/../../tmp/';
    }

    /**
     * Read image content
     *
     * Retrieves and returns image content from tmp storage.
     * Requires ROOMID and random parameters.
     *
     * @return void
     */
    public function read(): void
    {
        // Validate room token (will die if invalid)
        RoomTokenManager::requireRoomToken();

        // Verify CSRF token
        if (!\App\Utils\Csrf::verifyRequest()) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'CSRF token validation failed'
            ], 403);
            return;
        }

        $random = $this->postParam('random', '');
        $roomid = $this->postParam('ROOMID', '');

        // Ensure tmp directory exists
        if (!file_exists($this->tmpDir)) {
            mkdir($this->tmpDir, 0755, true);
        }

        $imageIdPath = $this->tmpDir . 'ImageID_' . $roomid;
        if (file_exists($imageIdPath)) {
            $randomid = file_get_contents($imageIdPath);
            if ($random != $randomid) {
                $imageContent = file_get_contents($this->tmpDir . 'Image_' . $roomid);
                echo $randomid . '|||' . $imageContent;
                exit;
            }
        }

        // If no match, return empty response
        $this->jsonResponse([
            'status' => 'error',
            'message' => 'No image data'
        ], 404);
    }

    /**
     * Save image content
     *
     * Validates room token and CSRF, processes uploaded image file.
     * Validates image type and saves to tmp storage.
     * Returns JSON response with image information.
     *
     * @throws \RuntimeException When no image is uploaded or type is invalid
     * @return void
     */
    public function save(): void
    {
        // Validate room token (will die if invalid)
        RoomTokenManager::requireRoomToken();

        // Verify CSRF token
        if (!\App\Utils\Csrf::verifyRequest()) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'CSRF token validation failed'
            ], 403);
            return;
        }

        // Get parameters
        $random = $this->postParam('random', '');
        $roomid = $this->postParam('ROOMID', '');

        // Validate ROOMID
        if (!Validator::validateRoomId($roomid)) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Invalid ROOMID'
            ], 400);
            return;
        }

        // Check if image was uploaded
        if (!isset($_FILES['image'])) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'No image uploaded'
            ], 400);
            return;
        }

        $file = $_FILES['image'];

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = $file['type'] ?? '';

        if (!in_array($fileType, $allowedTypes)) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Unauthorized file type: ' . $fileType
            ], 400);
            return;
        }

        // Check file size (5MB max)
        $maxSize = 5 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'File too large'
            ], 413);
            return;
        }

        // Check upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Upload error: ' . $file['error']
            ], 400);
            return;
        }

        // Validate actual MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowedTypes)) {
            @unlink($file['tmp_name']);
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Unauthorized MIME type: ' . $mime
            ], 400);
            return;
        }

        // Read file content
        $fileContent = file_get_contents($file['tmp_name']);
        if ($fileContent === false) {
            @unlink($file['tmp_name']);
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Unable to read file'
            ], 400);
            return;
        }

        // Encode to base64
        $url = 'data:' . $mime . ';base64,' . base64_encode($fileContent);

        // Save image - ensure tmp directory exists
        if (!file_exists($this->tmpDir)) {
            mkdir($this->tmpDir, 0755, true);
        }

        $imagePath = $this->tmpDir . 'Image_' . $roomid;
        $fd = fopen($imagePath, 'w');
        if ($fd === false) {
            @unlink($file['tmp_name']);
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Unable to create file'
            ], 500);
            return;
        }
        fputs($fd, $url);
        fclose($fd);

        // Save image ID
        $imageIdPath = $this->tmpDir . 'ImageID_' . $roomid;
        $fd = fopen($imageIdPath, 'w');
        if ($fd !== false) {
            fputs($fd, $random);
            fclose($fd);
        }

        // Clean up temp file
        @unlink($file['tmp_name']);

        // Return success
        $this->jsonResponse([
            'status' => 'success',
            'message' => 'Image saved'
        ]);
    }
}
