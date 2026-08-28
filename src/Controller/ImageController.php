<?php

namespace App\Controller;

/**
 * ImageController - Handles image reading and saving operations
 */
class ImageController extends BaseController
{
    /**
     * Read image content
     *
     * Retrieves and returns image content from storage.
     * Requires either imageId or roomId parameter.
     *
     * @return void
     */
    public function read(): void
    {
        // Get image identifier from request
        $imageId = $this->getParam('id');
        $roomId = $this->getParam('ROOMID');

        if (empty($imageId) && empty($roomId)) {
            $this->jsonResponse(['error' => 'Image identifier missing'], 400);
        }

        // TODO: Implement image reading logic
        // This should read an image from storage and return it

        // For now, return a placeholder
        header('Content-Type: image/svg+xml');
        echo '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100">'
            . '<rect width="100" height="100" fill="#ddd"/>'
            . '<text x="50" y="55" text-anchor="middle" font-family="Arial">Image</text>'
            . '</svg>';
        exit;
    }

    /**
     * Save image content
     *
     * Validates CSRF token, processes uploaded image file.
     * Validates image type and saves to secure storage.
     * Returns JSON response with image information.
     *
     * @throws \RuntimeException When no image is uploaded or type is invalid
     * @return void
     */
    public function save(): void
    {
        // Validate CSRF token
        \App\Utils\Csrf::verifyRequest();

        // Check if image was uploaded
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $this->jsonResponse(['error' => 'No image uploaded'], 400);
        }

        // Get file information
        $file = $_FILES['image'];
        $allowedTypes = ['image/png', 'image/jpeg', 'image/gif', 'image/svg+xml'];

        // Validate file type
        if (!in_array($file['type'], $allowedTypes)) {
            $this->jsonResponse(['error' => 'Invalid image type'], 400);
        }

        // TODO: Implement actual image saving logic
        // This should:
        // 1. Validate image size
        // 2. Generate unique filename
        // 3. Move to secure storage
        // 4. Return success with image info

        $this->jsonResponse([
            'success' => true,
            'filename' => $file['name'],
            'size' => $file['size'],
            'type' => $file['type']
        ]);
    }
}
