<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nome_categoria'])) {
    $nome = trim($_POST['nome_categoria']);
    if (!empty($nome)) {
        $stmt = $pdo->prepare("INSERT INTO categorias (nome_categoria) VALUES (?)");
        $stmt->execute([$nome]);
    }
    header("Location: categorias.php");
    exit;
}

$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nome_categoria ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Gerir Categorias</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        form { margin-bottom: 20px; }
    </style>
</head>
<body>
    <p><a href="index.php">← Voltar para o Catálogo</a></p>
    <h2>Gerir Categorias</h2>

    <form method="POST">
        <input type="text" name="nome_categoria" placeholder="Nome da Categoria" required>
        <button type="submit">Cadastrar Categoria</button>
    </form>

    <h3>Categorias Registadas</h3>
    <ul>
        <?php foreach ($categorias as $cat): ?>
            <li><?= htmlspecialchars($cat['nome_categoria']) ?></li>
        <?php endforeach; ?>
    </ul>
</body>
</html>