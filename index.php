<?php
require 'config.php';

$limite = 10;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina < 1) $pagina = 1;
$inicio = ($pagina - 1) * $limite;

$categoria_id = $_GET['categoria_id'] ?? '';

if (!empty($categoria_id)) {
    
    $countStmt = $pdo->prepare("SELECT COUNT(DISTINCT j.codigo) FROM jogos j JOIN jogo_categoria jc ON j.codigo = jc.codigo_jogo WHERE jc.id_categoria = ?");
    $countStmt->execute([$categoria_id]);
    $total_jogos = $countStmt->fetchColumn();

    
    $sql = "SELECT DISTINCT j.* FROM jogos j 
            JOIN jogo_categoria jc ON j.codigo = jc.codigo_jogo 
            WHERE jc.id_categoria = ? 
            ORDER BY j.codigo ASC LIMIT :inicio, :limite";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(1, (int)$categoria_id, PDO::PARAM_INT);
    $stmt->bindValue(':inicio', $inicio, PDO::PARAM_INT);
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->execute();
    $jogos = $stmt->fetchAll();
} else {
    
    $total_jogos = $pdo->query("SELECT COUNT(*) FROM jogos")->fetchColumn();

    // Consulta geral
    $sql = "SELECT * FROM jogos ORDER BY codigo ASC LIMIT :inicio, :limite";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':inicio', $inicio, PDO::PARAM_INT);
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->execute();
    $jogos = $stmt->fetchAll();
}

$total_paginas = ceil($total_jogos / $limite);
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nome_categoria ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Catálogo Jogos</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .btn { display: inline-block; padding: 6px 12px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px; }
        .btn-sec { background-color: #6c757d; }
        .jogo-card { margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #ddd; }
        .categorias-tag { font-size: 0.9em; color: #666; font-style: italic; }
        .paginacao { margin-top: 20px; }
        .paginacao a { margin-right: 5px; text-decoration: none; }
    </style>
</head>
<body>

    <h2>Catálogo Jogos</h2>

    <p>
        <a href="form_jogo.php" class="btn">+ Novo Jogo</a>
        <a href="categorias.php" class="btn btn-sec">Gerir Categorias</a>
    </p>

    
    <form method="GET" style="margin-bottom: 20px;">
        <label for="categoria_id">Filtrar por Categoria:</label>
        <select name="categoria_id" id="categoria_id" onchange="this.form.submit()">
            <option value="">Todas as Categorias</option>
            <?php foreach ($categorias as $cat): ?>
                <option value="<?= $cat['id_categoria'] ?>" <?= $categoria_id == $cat['id_categoria'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['nome_categoria']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    
    <?php foreach ($jogos as $jogo): ?>
        <?php
        
        $stmtCats = $pdo->prepare("SELECT c.nome_categoria FROM categorias c 
                                   JOIN jogo_categoria jc ON c.id_categoria = jc.id_categoria 
                                   WHERE jc.codigo_jogo = ?");
        $stmtCats->execute([$jogo['codigo']]);
        $cats_jogo = $stmtCats->fetchAll(PDO::FETCH_COLUMN);
        ?>
        <div class="jogo-card">
            <div>
                <strong><?= $jogo['codigo'] ?> - <?= htmlspecialchars($jogo['nome']) ?></strong> - 
                <a href="<?= htmlspecialchars($jogo['link']) ?>" target="_blank"><?= htmlspecialchars($jogo['link']) ?></a> 
                [<a href="form_jogo.php?codigo=<?= $jogo['codigo'] ?>">Editar</a> | 
                 <a href="excluir.php?codigo=<?= $jogo['codigo'] ?>" onclick="return confirm('Tem a certeza que deseja apagar?')">Excluir</a>]
            </div>
            
            <div class="categorias-tag">
                Categorias: <?= $cats_jogo ? htmlspecialchars(implode(', ', $cats_jogo)) : 'Nenhuma' ?>
            </div>

            <div>
                <?= nl2br(htmlspecialchars($jogo['descricao'])) ?>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="paginacao">
        Página: 
        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
            <a href="?pagina=<?= $i ?>&categoria_id=<?= urlencode($categoria_id) ?>" 
               style="<?= $i == $pagina ? 'font-weight:bold; text-decoration:underline;' : '' ?>">
                [<?= $i ?>]
            </a>
        <?php endfor; ?>
    </div>

</body>
</html>