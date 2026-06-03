<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(['erro' => 'Método não permitido'], 405);
}

// Verifica se veio arquivo
if (empty($_FILES['foto'])) {
    responder(['erro' => 'Nenhum arquivo enviado'], 400);
}

$file    = $_FILES['foto'];
$userId  = $_POST['user_id'] ?? null;

if (!$userId) {
    responder(['erro' => 'user_id obrigatório'], 400);
}

// Verifica erro de upload
if ($file['error'] !== UPLOAD_ERR_OK) {
    responder(['erro' => 'Erro no upload: ' . $file['error']], 400);
}

// Valida tipo
$tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime, $tiposPermitidos)) {
    responder(['erro' => 'Tipo de arquivo não permitido. Use JPG, PNG, GIF ou WEBP'], 400);
}

// Valida tamanho (máx 5MB)
if ($file['size'] > 5 * 1024 * 1024) {
    responder(['erro' => 'Arquivo muito grande. Máximo 5MB'], 400);
}

// Cria pasta de uploads se não existir
$uploadDir = dirname(__DIR__) . '/assets/uploads/fotos/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Remove foto antiga se existir
$stmt = $pdo->prepare('SELECT foto FROM usuarios WHERE id = ?');
$stmt->execute([$userId]);
$oldUser = $stmt->fetch();
if ($oldUser && $oldUser['foto']) {
    $oldPath = dirname(__DIR__) . '/' . ltrim($oldUser['foto'], '/');
    if (file_exists($oldPath)) {
        unlink($oldPath);
    }
}

// Gera nome único
$ext      = match($mime) {
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/gif'  => 'gif',
    'image/webp' => 'webp',
    default      => 'jpg'
};
$filename = 'foto_' . $userId . '_' . time() . '.' . $ext;
$destPath = $uploadDir . $filename;
$publicUrl = 'assets/uploads/fotos/' . $filename;

// Move arquivo
if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    responder(['erro' => 'Erro ao salvar arquivo no servidor'], 500);
}

// Salva caminho no banco
$stmt = $pdo->prepare('UPDATE usuarios SET foto = ? WHERE id = ?');
$stmt->execute([$publicUrl, $userId]);

responder([
    'sucesso' => true,
    'foto'    => $publicUrl,
    'url'     => $publicUrl
]);
?>