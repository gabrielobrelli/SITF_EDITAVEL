<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $chatKey = $_GET['chat_key'] ?? null;
        if (!$chatKey) responder(['erro' => 'chat_key obrigatório'], 400);
        $stmt = $pdo->prepare('SELECT * FROM mensagens WHERE chat_key = ? ORDER BY criado_em ASC');
        $stmt->execute([$chatKey]);
        $msgs = $stmt->fetchAll();
        // Marca como lidas
        $pdo->prepare('UPDATE mensagens SET lido = 1 WHERE chat_key = ?')->execute([$chatKey]);
        responder($msgs);
        break;

    case 'POST':
        $d = json_decode(file_get_contents('php://input'), true);
        if (empty($d['chat_key']) || empty($d['sender_id']) || empty($d['texto'])) {
            responder(['erro' => 'Campos obrigatórios faltando'], 400);
        }
        $id = gerarId();
        $stmt = $pdo->prepare('INSERT INTO mensagens (id, chat_key, sender_id, texto, lido) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$id, $d['chat_key'], $d['sender_id'], $d['texto'], 0]);
        responder(['sucesso' => true, 'id' => $id], 201);
        break;

    default:
        responder(['erro' => 'Método não permitido'], 405);
}
?>