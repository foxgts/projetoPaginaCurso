<?php
// admin/excluir_aula.php
require_once '../db_connect.php';

$id_aula = null;
$id_modulo_associado = null; // Para redirecionar de volta ao módulo correto
$error_msg = "";
$sucesso_msg = "";

// Verifica se um ID de aula foi passado na URL
if (isset($_GET["id_aula"]) && !empty(trim($_GET["id_aula"]))) {
    $id_aula = mysqli_real_escape_string($link, $_GET["id_aula"]);

    // Antes de excluir, obtenha o id_modulo associado para o redirecionamento
    $sql_get_modulo_id = "SELECT id_modulo FROM aulas WHERE id_aula = ?";
    if ($stmt_get_modulo_id = mysqli_prepare($link, $sql_get_modulo_id)) {
        mysqli_stmt_bind_param($stmt_get_modulo_id, "i", $id_aula);
        mysqli_stmt_execute($stmt_get_modulo_id);
        mysqli_stmt_bind_result($stmt_get_modulo_id, $fetched_id_modulo);
        if (mysqli_stmt_fetch($stmt_get_modulo_id)) {
            $id_modulo_associado = $fetched_id_modulo;
        } else {
            $error_msg = "Aula não encontrada ou já excluída.";
        }
        mysqli_stmt_close($stmt_get_modulo_id);
    } else {
        $error_msg = "Erro ao preparar a busca por ID do módulo associado.";
    }

    // Se encontrou a aula e seu módulo, procede com a exclusão
    if (!empty($id_aula) && !empty($id_modulo_associado) && empty($error_msg)) {
        $sql_delete_aula = "DELETE FROM aulas WHERE id_aula = ?";
        if ($stmt_delete_aula = mysqli_prepare($link, $sql_delete_aula)) {
            mysqli_stmt_bind_param($stmt_delete_aula, "i", $id_aula);
            mysqli_stmt_execute($stmt_delete_aula);

            if (mysqli_stmt_affected_rows($stmt_delete_aula) > 0) {
                $sucesso_msg = "Aula excluída com sucesso!";
            } else {
                $error_msg = "Aula não encontrada para exclusão.";
            }
            mysqli_stmt_close($stmt_delete_aula);
        } else {
            $error_msg = "Erro ao preparar query de exclusão da aula: " . mysqli_error($link);
        }
    }

} else {
    $error_msg = "ID da aula inválido ou não fornecido.";
}

mysqli_close($link);

$page_title = "Excluir Aula"; // Define o título da página

require_once 'includes/admin_header.php'; // Inclui o cabeçalho
?>
        <?php if (!empty($sucesso_msg)): ?>
            <div class="success-message"><?php echo $sucesso_msg; ?></div>
        <?php endif; ?>
        <?php if (!empty($error_msg)): ?>
            <div class="error-message"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <p style="text-align: center; margin-top: 30px;">
            <?php if (!empty($id_modulo_associado)): ?>
                <a href="gerenciar_aulas.php?id_modulo=<?php echo htmlspecialchars($id_modulo_associado); ?>" class="btn-link">← Voltar para Gerenciar Aulas</a>
            <?php else: ?>
                <a href="gerenciar_cursos.php" class="btn-link">← Voltar para Gerenciar Cursos</a>
            <?php endif; ?>
        </p>

<?php
require_once 'includes/admin_footer.php'; // Inclui o rodapé
?>
