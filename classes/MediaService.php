<?php

class MediaService {

    public function handlePostImageUpload(array $fileInput): string {
        if (empty($fileInput["name"])) {
            return "";
        }

        $error = $fileInput['error'] ?? UPLOAD_ERR_OK;
        if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
            throw new Exception("Image too large - max size 3 MB");
        }
        if ($error !== UPLOAD_ERR_OK) {
            throw new Exception("Upload failed");
        }

        $maxSize = 3 * 1024 * 1024;
        if (($fileInput['size'] ?? 0) > $maxSize) {
            throw new Exception("Image too large - max size 3 MB");
        }

        $fileTmp = $fileInput["tmp_name"];
        $fileName = bin2hex(random_bytes(10)) . "_" . basename($fileInput["name"]);
        $targetDir = __DIR__ . "/../public/uploads/posts";
        $targetPath = $targetDir . "/" . $fileName;

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        if (move_uploaded_file($fileTmp, $targetPath)) {
            return "/public/uploads/posts/" . $fileName;
        }

        return "";
    }

    public function handleProfileImage(?array $fileInput, ?string $type): string {
        if (!in_array($type, ['avatar', 'cover'], true)) {
            throw new Exception("Invalid upload type");
        }

        if (empty($fileInput)) {
            throw new Exception("No file received");
        }

        $error = $fileInput['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
            throw new Exception("Image too large - max size 3 MB");
        }
        if ($error !== UPLOAD_ERR_OK) {
            throw new Exception("Upload failed");
        }

        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($fileInput['type'], $allowed, true)) {
            throw new Exception("Invalid file type - allowed: JPG, PNG, WEBP");
        }

        $maxSize = 3 * 1024 * 1024;
        if (($fileInput['size'] ?? 0) > $maxSize) {
            throw new Exception("Image too large - max size 3 MB");
        }

        $ext = pathinfo($fileInput['name'], PATHINFO_EXTENSION);
        $newName = bin2hex(random_bytes(12)) . "." . $ext;
        $folder = $type === 'avatar' ? 'avatars' : 'covers';

        $uploadDir = __DIR__ . "/../public/uploads/{$folder}/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $targetPath = $uploadDir . $newName;
        if (!move_uploaded_file($fileInput['tmp_name'], $targetPath)) {
            throw new Exception("Upload failed");
        }

        return "/public/uploads/{$folder}/{$newName}";
    }
}
