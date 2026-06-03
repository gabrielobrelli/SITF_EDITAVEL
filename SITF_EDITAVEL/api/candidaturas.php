<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $vagaId       = $_GET['vaga_id']       ?? null;
        $freelancerId = $_GET['freelancer_id'] ?? null;

        if ($vagaId) {
            $stmt = $pdo->prepare('SELECT * FROM candidaturas WHERE vaga_id = ? ORDER BY criado_em DESC');
            $stmt->execute([$vagaId]);
        } elseif ($freelancerId) {
            $stmt = $pdo->prepare('SELECT * FROM candidaturas WHERE freelancer_id = ? ORDER BY criado_em DESC');
            $stmt->execute([$freelancerId]);
        } else {
            $stmt = $pdo->query('SELECT * FROM candidaturas ORDER BY criado_em DESC');
        }
        responder($stmt->fetchAll());
        break;

    case 'POST':
        $d = json_decode(file_get_contents('php://input'), true);
        if (empty($d['vaga_id']) || empty($d['freelancer_id'])) {
            responder(['erro' => 'Campos obrigatórios faltando'], 400);
        }
        // Verifica se já candidatou
        $stmt = $pdo->prepare('SELECT id FROM candidaturas WHERE vaga_id = ? AND freelancer_id = ?');
        $stmt->execute([$d['vaga_id'], $d['freelancer_id']]);
        if ($stmt->fetch()) {
            responder(['erro' => 'Você já se candidatou a esta vaga'], 409);
        }
        $id = gerarId();
        $stmt = $pdo->prepare('INSERT INTO candidaturas (id, vaga_id, freelancer_id, status, visto) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$id, $d['vaga_id'], $d['freelancer_id'], 'pendente', 0]);
        responder(['sucesso' => true, 'id' => $id], 201);
        break;

    case 'PUT':
        $d  = json_decode(file_get_contents('php://input'), true);
        $id = $d['id'] ?? null;
        if (!$id) responder(['erro' => 'ID obrigatório'], 400);
        $stmt = $pdo->prepare('UPDATE candidaturas SET status = ?, visto = ? WHERE id = ?');
        $stmt->execute([$d['status'] ?? 'pendente', $d['visto'] ?? 0, $id]);
        responder(['sucesso' => true]);
        break;

    default:
        responder(['erro' => 'Método não permitido'], 405);
}
?>