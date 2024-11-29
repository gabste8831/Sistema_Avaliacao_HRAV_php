<?php
require 'conexao.php';

try {
    $dados = json_decode(file_get_contents('php://input'), true);

    if (is_array($dados) && !empty($dados)) {
        $stmt = $conn->prepare("INSERT INTO respostas (id_pergunta, resposta, feedback, data_resposta) VALUES (:id_pergunta, :resposta, :feedback, NOW())");

        foreach ($dados as $resposta) {
            $stmt->execute([
                ':id_pergunta' => $resposta['id_pergunta'],
                ':resposta' => $resposta['resposta'],
                ':feedback' => $resposta['feedback']
            ]);
        }

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Nenhuma resposta enviada']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
