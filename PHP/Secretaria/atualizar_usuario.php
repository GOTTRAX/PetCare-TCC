<?php
session_start();

if (!isset($_SESSION["id"]) || $_SESSION["tipo_usuario"] !== "Secretaria") {
    header("Location: ../index.php");
    exit();
}

include '../conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $tipo = $_POST['tipo_usuario'];
    $ativo = $_POST['ativo'];

    $sql = "UPDATE Usuarios 
            SET tipo_usuario = :tipo, ativo = :ativo, atualizado_em = NOW() 
            WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':tipo' => $tipo,
        ':ativo' => $ativo,
        ':id' => $id
    ]);
}

header("Location: sec_home.php");
exit();
