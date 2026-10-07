<?php
function e($string) {
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

function simpan_gambar(array $file, string $folder, string $label): string {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new Exception("$label wajib diupload.");
    }

    if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
        throw new Exception("$label maksimal 2MB.");
    }

    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
    } else {
        $mime = mime_content_type($file['tmp_name']);
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($allowed[$mime])) {
        throw new Exception("$label harus berupa gambar JPG, PNG, atau WEBP.");
    }

    if (!is_dir($folder)) {
        mkdir($folder, 0775, true);
    }

    $filename = date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.' . $allowed[$mime];
    $target = rtrim($folder, '/') . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        throw new Exception("$label gagal disimpan.");
    }

    return $target;
}
?>
