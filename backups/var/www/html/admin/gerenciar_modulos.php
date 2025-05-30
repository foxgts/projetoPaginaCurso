<?php
// admin/gerenciar_modulos.php
require_once '../db_connect.php';

$id_curso = null;
$curso_titulo = "Curso Desconhecido";
$modulos = [];
$error_msg = "";

if (isset($_GET['id_curso']) && !empty(trim($_GET['id_curso']))) {
    $id_curso = mysqli_real_escape_string($link, $_GET['id_curso']);

    $sql_curso_titulo = "SELECT titulo FROM cursos WHERE id_curso = ?";
    if ($stmt_curso_titulo = mysqli_prepare($link, $sql_curso_titulo)) {
        mysqli_stmt_bind_param($stmt_curso_titulo, "i", $id_curso);
        mysqli_stmt_execute($stmt_curso_titulo);
        mysqli_stmt_bind_result($stmt_curso_titulo, $titulo_found);
        if (mysqli_stmt_fetch($stmt_curso_titulo)) {
            $curso_titulo = $titulo_found;
        } else {
            $error_msg = "Curso não encontrado.";
        }
        mysqli_stmt_close($stmt_curso_titulo);
    } else {
        $error_msg = "Erro ao preparar query do título do curso.";
    }

    if (empty($error_msg)) {
        $sql_modulos = "SELECT id_modulo, titulo_modulo FROM modulos WHERE id_curso = ? ORDER BY ordem ASC";
        if ($stmt_modulos = mysqli_prepare($link, $sql_modulos)) {
            mysqli_stmt_bind_param($stmt_modulos, "i", $id_curso);
            mysqli_stmt_execute($stmt_modulos);
            $result_modulos = mysqli_stmt_get_result($stmt_modulos);

            if (mysqli_num_rows($result_modulos) > 0) {
                while ($row = mysqli_fetch_assoc($result_modulos)) {
                    $modulos[] = $row;
                }
                mysqli_free_result($result_modulos);
            }
            mysqli_stmt_close($stmt_modulos);
        } else {
            $error_msg = "Erro ao preparar query dos módulos.";
        }
    }
} else {
    header("location: gerenciar_cursos.php?error_msg=" . urlencode("ID do curso não fornecido para gerenciar módulos."));
    exit;
}

mysqli_close($link);

$page_title = "Gerenciar Módulos de " . htmlspecialchars($curso_titulo);

require_once 'includes/admin_header.php';
?>

        <?php if (!empty($error_msg)): ?>
            <div class="error-message"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <p style="margin-bottom: 20px; text-align: center;">
            <a href="adicionar_modulo.php?id_curso=<?php echo htmlspecialchars($id_curso); ?>" class="btn-link">Adicionar Novo Módulo a este Curso</a>
        </p>

        <div class="module-grid">
        <?php if (!empty($modulos)): ?>
            <?php foreach ($modulos as $modulo): ?>
                <div class="module-card">
                    <h3><?php echo htmlspecialchars($modulo['titulo_modulo']); ?></h3>
                    <div class="aula-actions">
                        <a href="gerenciar_aulas.php?id_modulo=<?php echo htmlspecialchars($modulo['id_modulo']); ?>" class="action-gerenciar-aulas">Gerenciar Aulas</a>
                        <a href="adicionar_aula.php?id_modulo=<?php echo htmlspecialchars($modulo['id_modulo']); ?>" class="action-add-aula">Adicionar Aula</a>
                    </div>
                    <div class="module-actions">
                        <a href="editar_modulo.php?id_modulo=<?php echo htmlspecialchars($modulo['id_modulo']); ?>" class="action-edit">Editar Módulo</a>
                        <a href="excluir_modulo.php?id_modulo=<?php echo htmlspecialchars($modulo['id_modulo']); ?>" class="action-delete" onclick="return confirm('Tem certeza que deseja excluir este módulo e todas as suas aulas? Esta ação é irreversível!');">Excluir Módulo</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-items">Nenhum módulo encontrado para este curso. <a href="adicionar_modulo.php?id_curso=<?php echo htmlspecialchars($id_curso); ?>">Adicione um novo módulo.</a></p>
        <?php endif; ?>
        </div>

        <p class="back-link"><a href="gerenciar_cursos.php">← Voltar para Gerenciar Cursos</a></p>

<?php
require_once 'includes/admin_footer.php';
?>
