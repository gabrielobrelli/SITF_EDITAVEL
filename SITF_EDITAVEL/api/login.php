<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(['erro' => 'Método não permitido'], 405);
}

$dados = json_decode(file_get_contents('php://input'), true);

if (empty($dados['email']) || empty($dados['senha'])) {
    responder(['erro' => 'E-mail e senha são obrigatórios'], 400);
}

$email = trim($dados['email']);
$senha = trim($dados['senha']);

$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = ?');
$stmt->execute([$email]);
$usuario = $stmt->fetch();

if (!$usuario || !password_verify($senha, $usuario['senha'])) {
    responder(['erro' => 'E-mail ou senha incorretos'], 401);
}

// Remove a senha antes de retornar
unset($usuario['senha']);

// Converte skills de string para array
if ($usuario['skills']) {
    $usuario['skills'] = explode(',', $usuario['skills']);
} else {
    $usuario['skills'] = [];
}

responder(['sucesso' => true, 'usuario' => $usuario]);
?>