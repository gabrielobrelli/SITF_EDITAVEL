<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $id   = $_GET['id']   ?? null;
        $tipo = $_GET['tipo'] ?? null;

        if ($id) {
            $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = ?');
            $stmt->execute([$id]);
            $u = $stmt->fetch();
            if (!$u) responder(['erro' => 'Usuário não encontrado'], 404);
            unset($u['senha']);
            $u['skills'] = $u['skills'] ? explode(',', $u['skills']) : [];
            responder($u);
        } else {
            if ($tipo) {
                $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE tipo = ?');
                $stmt->execute([$tipo]);
            } else {
                $stmt = $pdo->query('SELECT * FROM usuarios');
            }
            $users = $stmt->fetchAll();
            foreach ($users as &$u) {
                unset($u['senha']);
                $u['skills'] = $u['skills'] ? explode(',', $u['skills']) : [];
            }
            responder($users);
        }
        break;

    case 'PUT':
        $d  = json_decode(file_get_contents('php://input'), true);
        $id = $d['id'] ?? null;
        if (!$id) responder(['erro' => 'ID obrigatório'], 400);

        $skills = is_array($d['skills'] ?? [])
            ? implode(',', $d['skills'])
            : ($d['skills'] ?? '');

        $stmt = $pdo->prepare('
            UPDATE usuarios SET
                nome          = ?,
                cidade        = ?,
                estado        = ?,
                bio           = ?,
                skills        = ?,
                rate          = ?,
                rate_type     = ?,
                portfolio     = ?,
                site          = ?,
                online        = ?,
                telefone      = ?,
                email_contato = ?,
                foto          = ?
            WHERE id = ?
        ');

        $stmt->execute([
            $d['nome']          ?? '',
            $d['cidade']        ?? '',
            $d['estado']        ?? '',
            $d['bio']           ?? '',
            $skills,
            $d['rate']          ?? 0,
            $d['rateType']      ?? 'hora',
            $d['portfolio']     ?? '',
            $d['site']          ?? '',
            $d['online']        ?? 1,
            $d['telefone']      ?? null,
            $d['email_contato'] ?? null,
            $d['foto']          ?? null,
            $id
        ]);

        responder(['sucesso' => true]);
        break;

    default:
        responder(['erro' => 'Método não permitido'], 405);
}
?>