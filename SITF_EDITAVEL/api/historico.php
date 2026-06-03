<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $freelancer_id = $_GET['freelancer_id'] ?? null;
        $empregador_id = $_GET['empregador_id'] ?? null;

        if ($freelancer_id) {
            $stmt = $pdo->prepare('
                SELECT h.*, 
                       u_emp.nome as empregador_nome,
                       u_emp.foto as empregador_foto
                FROM historico_servicos h
                LEFT JOIN usuarios u_emp ON u_emp.id = h.empregador_id
                WHERE h.freelancer_id = ?
                ORDER BY h.criado_em DESC
            ');
            $stmt->execute([$freelancer_id]);
        } elseif ($empregador_id) {
            $stmt = $pdo->prepare('
                SELECT h.*, 
                       u_free.nome as freelancer_nome,
                       u_free.foto as freelancer_foto
                FROM historico_servicos h
                LEFT JOIN usuarios u_free ON u_free.id = h.freelancer_id
                WHERE h.empregador_id = ?
                ORDER BY h.criado_em DESC
            ');
            $stmt->execute([$empregador_id]);
        } else {
            responder(['erro' => 'Informe freelancer_id ou empregador_id'], 400);
        }

        responder($stmt->fetchAll());
        break;

    case 'POST':
        $d = json_decode(file_get_contents('php://input'), true);

        if (empty($d['freelancer_id']) || empty($d['empregador_id']) || empty($d['titulo'])) {
            responder(['erro' => 'Campos obrigatórios faltando'], 400);
        }

        $id = gerarId();
        $stmt = $pdo->prepare('
            INSERT INTO historico_servicos 
            (id, freelancer_id, empregador_id, titulo, descricao, valor, data_inicio, data_fim, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');

        $stmt->execute([
            $id,
            $d['freelancer_id'],
            $d['empregador_id'],
            $d['titulo'],
            $d['descricao']   ?? '',
            $d['valor']       ?? 0,
            $d['data_inicio'] ?? null,
            $d['data_fim']    ?? null,
            $d['status']      ?? 'concluido',
        ]);

        responder(['sucesso' => true, 'id' => $id], 201);
        break;

    case 'PUT':
        $d  = json_decode(file_get_contents('php://input'), true);
        $id = $d['id'] ?? null;
        if (!$id) responder(['erro' => 'ID obrigatório'], 400);

        $stmt = $pdo->prepare('
            UPDATE historico_servicos SET
                titulo      = ?,
                descricao   = ?,
                valor       = ?,
                data_inicio = ?,
                data_fim    = ?,
                status      = ?
            WHERE id = ?
        ');

        $stmt->execute([
            $d['titulo']      ?? '',
            $d['descricao']   ?? '',
            $d['valor']       ?? 0,
            $d['data_inicio'] ?? null,
            $d['data_fim']    ?? null,
            $d['status']      ?? 'concluido',
            $id
        ]);

        responder(['sucesso' => true]);
        break;

    case 'DELETE':
        $id = $_GET['id'] ?? null;
        if (!$id) responder(['erro' => 'ID obrigatório'], 400);
        $pdo->prepare('DELETE FROM historico_servicos WHERE id = ?')->execute([$id]);
        responder(['sucesso' => true]);
        break;

    default:
        responder(['erro' => 'Método não permitido'], 405);
}
?>