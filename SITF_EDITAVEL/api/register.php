<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(['erro' => 'Método não permitido'], 405);
}

$dados = json_decode(file_get_contents('php://input'), true);

if (empty($dados['nome']) || empty($dados['email']) || empty($dados['senha']) || empty($dados['tipo'])) {
    responder(['erro' => 'Preencha todos os campos obrigatórios'], 400);
}

$nome  = trim($dados['nome']);
$email = trim($dados['email']);
$senha = trim($dados['senha']);
$tipo  = trim($dados['tipo']);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    responder(['erro' => 'E-mail inválido'], 400);
}

if (strlen($senha) < 6) {
    responder(['erro' => 'Senha deve ter pelo menos 6 caracteres'], 400);
}

if (!in_array($tipo, ['freelancer', 'empregador'])) {
    responder(['erro' => 'Tipo inválido'], 400);
}

// Verifica se email já existe
$stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    responder(['erro' => 'E-mail já cadastrado'], 409);
}

$id    = gerarId();
$hash  = password_hash($senha, PASSWORD_DEFAULT);

$stmt = $pdo->prepare('
    INSERT INTO usuarios 
    (id, nome, email, senha, tipo, cidade, estado, bio, skills, rate, rate_type, portfolio, rating, reviews, projetos, online)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
');

$stmt->execute([
    $id, $nome, $email, $hash, $tipo,
    $dados['cidade']    ?? '',
    $dados['estado']    ?? '',
    $dados['bio']       ?? '',
    $dados['skills']    ?? '',
    $dados['rate']      ?? 0,
    $dados['rateType']  ?? 'hora',
    $dados['portfolio'] ?? '',
    0, 0, 0, 1
]);

$novoUsuario = [
    'id'       => $id,
    'nome'     => $nome,
    'email'    => $email,
    'tipo'     => $tipo,
    'cidade'   => $dados['cidade']   ?? '',
    'estado'   => $dados['estado']   ?? '',
    'bio'      => $dados['bio']      ?? '',
    'skills'   => [],
    'rate'     => $dados['rate']     ?? 0,
    'rateType' => $dados['rateType'] ?? 'hora',
    'portfolio'=> $dados['portfolio']?? '',
    'rating'   => 0,
    'reviews'  => 0,
    'projetos' => 0,
    'online'   => 1,
];

responder(['sucesso' => true, 'usuario' => $novoUsuario]);
?>