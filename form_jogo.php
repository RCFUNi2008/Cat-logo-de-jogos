<?php
require 'config.php';

$codigo = $_GET['codigo'] ?? null;
$jogo = ['codigo' => '', 'nome' => '', 'link' => '', 'descricao' => ''];
$categorias_selecionadas = [];

if ($codigo) {
    $stmt = $pdo->prepare("SELECT * FROM jogos WHERE codigo = ?");
    $stmt->execute([$codigo]);
    $jogo = $stmt->fetch();

    if (!$jogo) {
        header("Location: index.php");
        exit;
    }

    $stmtCat = $pdo->prepare("SELECT id_categoria FROM jogo_categoria WHERE codigo_jogo = ?");
    $stmtCat->execute([$codigo]);
    $categorias_selecionadas = $stmtCat->fetchAll(PDO::FETCH_COLUMN);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $link = trim($_POST['link']);
    $descricao = trim($_POST['descricao']);
    $cats = $_POST['categorias'] ?? [];

    if ($codigo) {
        $stmt = $pdo->prepare("UPDATE jogos SET nome = ?, link = ?, descricao = ? WHERE codigo = ?");
        $stmt->execute([$nome, $link, $descricao, $codigo]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO jogos (nome, link, descricao) VALUES (?, ?, ?)");
        $stmt->execute([$nome, $link, $descricao]);
        $codigo = $pdo->lastInsertId();
    }

    
    $pdo->prepare("DELETE FROM jogo_categoria WHERE codigo_jogo = ?")->execute([$codigo]);
    $stmtAssoc = $pdo->prepare("INSERT INTO jogo_categoria (codigo_jogo, id_categoria) VALUES (?, ?)");
    foreach ($cats as $id_cat) {
        $stmtAssoc->execute([$codigo, $id_cat]);
    }

    header("Location: index.php");
    exit;
}

$todas_categorias = $pdo->query("SELECT * FROM categorias ORDER BY nome_categoria ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title><?= $codigo ? 'Editar' : 'Novo' ?> Jogo</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .campo { margin-bottom: 15px; }
        label { display: block; font-weight: bold; }
        input[type="text"], input[type="url"], textarea { width: 300px; padding: 5px; }
        textarea { height: 80px; }
    </style>
</head>
<body>
    <h2><?= $codigo ? 'Editar' : 'Novo' ?> Jogo</h2>

    <form method="POST">
        <div class="campo">
            <label>Nome:</label>
            <input type="text" name="nome" value="<?= htmlspecialchars($jogo['nome']) ?>" required>
        </div>

        <div class="campo">
            <label>Link:</label>
            <input type="url" name="link" value="<?= htmlspecialchars($jogo['link']) ?>" required>
        </div>

        <div class="campo">
            <label>Descrição:</label>
            <textarea name="descricao"><?= htmlspecialchars($jogo['descricao']) ?></textarea>
        </div>

        <div class="campo">
            <label>Categorias:</label>
            <?php foreach ($todas_categorias as $cat): ?>
                <label style="font-weight: normal;">
                    <input type="checkbox" name="categorias[]" value="<?= $cat['id_categoria'] ?>"
                        <?= in_array($cat['id_categoria'], $categorias_selecionadas) ? 'checked' : '' ?>>
                    <?= htmlspecialchars($cat['nome_categoria']) ?>
                </label>
            <?php endforeach; ?>
        </div>

        <button type="submit">Guardar</button>
        <a href="index.php">Cancelar</a>
    </form>
</body>
</html>