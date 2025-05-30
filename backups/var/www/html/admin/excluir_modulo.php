<?php
// admin/excluir_modulo.php
require_once '../db_connect.php';

$id_modulo = null;
$id_curso_associado = null; // Para redirecionar de volta ao curso correto
$error_msg = "";
$sucesso_msg = "";

// Verifica se um ID de módulo foi passado na URL
if (isset($_GET["id_modulo"]) && !empty(trim($_GET["id_modulo"]))) {
    $id_modulo = mysqli_real_escape_string($link, $_GET["id_modulo"]);

    // Antes de excluir, obtenha o id_curso associado para o redirecionamento
    $sql_get_curso_id = "SELECT id_curso FROM modulos WHERE id_modulo = ?";
    if ($stmt_get_curso_id = mysqli_prepare($link, $sql_get_curso_id)) {
        mysqli_stmt_bind_param($stmt_get_curso_id, "i", $id_modulo);
        mysqli_stmt_execute($stmt_get_curso_id);
        mysqli_stmt_bind_result($stmt_get_curso_id, $fetched_id_curso);
        if (mysqli_stmt_fetch($stmt_get_curso_id)) {
            $id_curso_associado = $fetched_id_curso;
        } else {
            $error_msg = "Módulo não encontrado ou já excluído.";
        }
        mysqli_stmt_close($stmt_get_curso_id);
    } else {
        $error_msg = "Erro ao preparar a busca por ID do curso associado.";
    }

    // Se encontrou o módulo e seu curso, procede com a exclusão
    if (!empty($id_modulo) && !empty($id_curso_associado) && empty($error_msg)) {
        // Inicia uma transação para garantir a integridade
        mysqli_begin_transaction($link);

        try {
            // 1. Excluir AULAS relacionadas a este módulo
            $sql_delete_aulas = "DELETE FROM aulas WHERE id_modulo = ?";
            if ($stmt_delete_aulas = mysqli_prepare($link, $sql_delete_aulas)) {
                mysqli_stmt_bind_param($stmt_delete_aulas, "i", $id_modulo);
                mysqli_stmt_execute($stmt_delete_aulas);
                mysqli_stmt_close($stmt_delete_aulas);
            } else {
                throw new Exception("Erro ao preparar query de exclusão de aulas: " . mysqli_error($link));
            }

            // 2. Excluir o MÓDULO
            $sql_delete_modulo = "DELETE FROM modulos WHERE id_modulo = ?";
            if ($stmt_delete_modulo = mysqli_prepare($link, $sql_delete_modulo)) {
                mysqli_stmt_bind_param($stmt_delete_modulo, "i", $id_modulo);
                mysqli_stmt_execute($stmt_delete_modulo);

                if (mysqli_stmt_affected_rows($stmt_delete_modulo) > 0) {
                    mysqli_commit($link); // Confirma todas as operações se tudo deu certo
                    $sucesso_msg = "Módulo e todas as suas aulas foram excluídos com sucesso!";
                } else {
                    throw new Exception("Módulo não encontrado para exclusão.");
                }
                mysqli_stmt_close($stmt_delete_modulo);
            } else {
                throw new Exception("Erro ao preparar query de exclusão do módulo: " . mysqli_error($link));
            }

        } catch (Exception $e) {
            mysqli_rollback($link); // Desfaz todas as operações se algo deu errado
            $error_msg = "Falha ao excluir o módulo: " . $e->getMessage();
        }
    }

} else {
    $error_msg = "ID do módulo inválido ou não fornecido.";
}

mysqli_close($link);

$page_title = "Excluir Módulo"; // Define o título da página

require_once 'includes/admin_header.php'; // Inclui o cabeçalho
?>
        <?php if (!empty($sucesso_msg)): ?>
            <div class="success-message"><?php echo $sucesso_msg; ?></div>
        <?php endif; ?>
        <?php if (!empty($error_msg)): ?>
            <div class="error-message"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <p style="text-align: center; margin-top: 30px;">
            <?php if (!empty($id_curso_associado)): ?>
                <a href="gerenciar_modulos.php?id_curso=<?php echo htmlspecialchars($id_curso_associado); ?>" class="btn-link">← Voltar para Gerenciar Módulos</a>
            <?php else: ?>
                <a href="gerenciar_cursos.php" class="btn-link">← Voltar para Gerenciar Cursos</a>
            <?php endif; ?>
        </p>

<?php
require_once 'includes/admin_footer.php'; // Inclui o rodapé
?>
