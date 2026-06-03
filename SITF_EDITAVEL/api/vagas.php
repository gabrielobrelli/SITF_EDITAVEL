<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $stmt = $pdo->query('SELECT * FROM vagas ORDER BY criado_em DESC');
        $vagas = $stmt->fetchAll();
        foreach ($vagas as &$v) {
            $v['tags']     = $v['tags'] ? explode(',', $v['tags']) : [];
            $v['destaque'] = (bool)$v['destaque'];
            // Busca candidatos
            $c = $pdo->prepare('SELECT freelancer_id FROM candidaturas WHERE vaga_id = ?');
            $c->execute([$v['id']]);
            $v['candidatos'] = $c->fetchAll(PDO::FETCH_COLUMN);
        }
        responder($vagas);
        break;

    case 'POST':
        $d = json_decode(file_get_contents('php://input'), true);
        if (empty($d['titulo']) || empty($d['empregador_id']) || empty($d['preco'])) {
            responder(['erro' => 'Campos obrigatórios faltando'], 400);
        }
        $id = gerarId();
        $stmt = $pdo->prepare('
            INSERT INTO vagas 
            (id, titulo, empresa, empregador_id, categoria, preco, tipo_preco, modalidade, cidade, estado, descricao, tags, status, destaque)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            $id,
            $d['titulo'],
            $d['empresa']      ?? '',
            $d['empregador_id'],
            $d['cat']          ?? 'Outros',
            $d['preco'],
            $d['tipo']         ?? 'hora',
            $d['modalidade']   ?? 'Remoto',
            $d['cidade']       ?? '',
            $d['estado']       ?? '',
            $d['desc']         ?? '',
            is_array($d['tags'] ?? []) ? implode(',', $d['tags']) : ($d['tags'] ?? ''),
            'aberta',
            $d['destaque']     ?? 0,
        ]);
        responder(['sucesso' => true, 'id' => $id], 201);
        break;

    case 'PUT':
        $d  = json_decode(file_get_contents('php://input'), true);
        $id = $d['id'] ?? null;
        if (!$id) responder(['erro' => 'ID obrigatório'], 400);
        $stmt = $pdo->prepare('UPDATE vagas SET status = ? WHERE id = ?');
        $stmt->execute([$d['status'] ?? 'encerrada', $id]);
        responder(['sucesso' => true]);
        break;

    case 'DELETE':
        $id = $_GET['id'] ?? null;
        if (!$id) responder(['erro' => 'ID obrigatório'], 400);
        $pdo->prepare('DELETE FROM vagas WHERE id = ?')->execute([$id]);
        responder(['sucesso' => true]);
        break;

    default:
        responder(['erro' => 'Método não permitido'], 405);
}
?>