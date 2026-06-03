<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $avaliadoId = $_GET['avaliado_id'] ?? null;
        $tipo       = $_GET['tipo']        ?? null;

        if ($avaliadoId && $tipo) {
            $stmt = $pdo->prepare('SELECT * FROM avaliacoes WHERE avaliado_id = ? AND tipo = ? ORDER BY criado_em DESC');
            $stmt->execute([$avaliadoId, $tipo]);
        } elseif ($avaliadoId) {
            $stmt = $pdo->prepare('SELECT * FROM avaliacoes WHERE avaliado_id = ? ORDER BY criado_em DESC');
            $stmt->execute([$avaliadoId]);
        } else {
            $stmt = $pdo->query('SELECT * FROM avaliacoes ORDER BY criado_em DESC');
        }
        responder($stmt->fetchAll());
        break;

    case 'POST':
        $d = json_decode(file_get_contents('php://input'), true);
        if (empty($d['vaga_id']) || empty($d['avaliador_id']) || empty($d['avaliado_id']) || empty($d['nota']) || empty($d['comentario'])) {
            responder(['erro' => 'Campos obrigatórios faltando'], 400);
        }
        // Verifica se já avaliou
        $stmt = $pdo->prepare('SELECT id FROM avaliacoes WHERE vaga_id = ? AND avaliador_id = ? AND avaliado_id = ?');
        $stmt->execute([$d['vaga_id'], $d['avaliador_id'], $d['avaliado_id']]);
        if ($stmt->fetch()) {
            responder(['erro' => 'Você já avaliou esta pessoa nesta vaga'], 409);
        }
        $id = gerarId();
        $stmt = $pdo->prepare('
            INSERT INTO avaliacoes (id, vaga_id, avaliador_id, avaliado_id, tipo, nota, comentario)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            $id,
            $d['vaga_id'],
            $d['avaliador_id'],
            $d['avaliado_id'],
            $d['tipo'],
            $d['nota'],
            $d['comentario']
        ]);
        // Atualiza média do avaliado
        $stmt = $pdo->prepare('SELECT AVG(nota) as media, COUNT(*) as total FROM avaliacoes WHERE avaliado_id = ?');
        $stmt->execute([$d['avaliado_id']]);
        $avg = $stmt->fetch();
        $pdo->prepare('UPDATE usuarios SET rating = ?, reviews = ? WHERE id = ?')
            ->execute([round($avg['media'], 1), $avg['total'], $d['avaliado_id']]);

        responder(['sucesso' => true, 'id' => $id], 201);
        break;

    default:
        responder(['erro' => 'Método não permitido'], 405);
}
?>