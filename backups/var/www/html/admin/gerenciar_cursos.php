<?php
// admin/gerenciar_cursos.php
// Não precisa de session_start() aqui, pois já está no header
require_once '../db_connect.php'; // Caminho correto para db_connect.php

$cursos = []; // Array para armazenar os cursos
$error_msg = "";

// Busca todos os cursos no banco de dados
$sql = "SELECT id_curso, titulo FROM cursos ORDER BY titulo ASC";
if ($result = mysqli_query($link, $sql)) {
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $cursos[] = $row;
        }
        mysqli_free_result($result); // Libera o conjunto de resultados
    }
} else {
    $error_msg = "ERRO: Não foi possível executar a query de cursos. " . mysqli_error($link);
}

mysqli_close($link);

$page_title = "Gerenciar Cursos"; // Define o título da página

require_once 'includes/admin_header.php'; // Inclui o cabeçalho
?>

        <?php if (!empty($error_msg)): ?>
            <div class="error-message"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <div class="item-list">
        <?php if (!empty($cursos)): ?>
            <?php foreach ($cursos as $curso): ?>
                <div class="list-item">
                    <h3><?php echo htmlspecialchars($curso['titulo']); ?></h3>
		<div class="actions">
    		<a href="gerenciar_modulos.php?id_curso=<?php echo htmlspecialchars($curso['id_curso']); ?>">Gerenciar Módulos</a>
    		<a href="adicionar_modulo.php?id_curso=<?php echo htmlspecialchars($curso['id_curso']); ?>">Adicionar Módulo</a>
    		<a href="editar_curso.php?id_curso=<?php echo htmlspecialchars($curso['id_curso']); ?>" class="action-edit">Editar</a>
    		<a href="excluir_curso.php?id_curso=<?php echo htmlspecialchars($curso['id_curso']); ?>" class="action-delete" onclick="return confirm('Tem certeza que deseja excluir este curso e todo o seu conteúdo (módulos e aulas)? Esta ação é irreversível!');">Excluir</a>
		</div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-items">Nenhum curso encontrado. <a href="adicionar_curso.php">Adicione um novo curso.</a></p>
        <?php endif; ?>
        </div>

<?php
require_once 'includes/admin_footer.php'; // Inclui o rodapé
?>
