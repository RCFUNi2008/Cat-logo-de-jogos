<?php
require 'config.php';

$codigo = $_GET['codigo'] ?? null;
if ($codigo) {
    $stmt = $pdo->prepare("DELETE FROM jogos WHERE codigo = ?");
    $stmt->execute([$codigo]);
}

header("Location: index.php");
exit;
?>