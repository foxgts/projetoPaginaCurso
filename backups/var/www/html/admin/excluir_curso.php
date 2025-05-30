<?php
// admin/excluir_curso.php
// Não precisa de session_start() aqui, pois já está no header
require_once '../db_connect.php';

$id_curso = null;
$error_msg = "";
$sucesso_msg = "";

// Verifica se um ID de curso foi passado na URL
if (isset($_GET["id_curso"]) && !empty(trim($_GET["id_curso"]))) {
    $id_curso = mysqli_real_escape_string($link, $_GET["id_curso"]);

    // Inicia uma transação para garantir que tudo seja excluído ou nada seja
    mysqli_begin_transaction($link);

    try {
        // 1. Excluir AULAS relacionadas aos módulos do curso
        // Primeiro, precisamos dos IDs dos módulos associados a este curso
        $sql_select_modulos = "SELECT id_modulo FROM modulos WHERE id_curso = ?";
        if ($stmt_select_modulos = mysqli_prepare($link, $sql_select_modulos)) {
            mysqli_stmt_bind_param($stmt_select_modulos, "i", $id_curso);
            mysqli_stmt_execute($stmt_select_modulos);
            $result_modulos = mysqli_stmt_get_result($stmt_select_modulos);
            $modulos_ids = [];
            while ($row = mysqli_fetch_assoc($result_modulos)) {
                $modulos_ids[] = $row['id_modulo'];
            }
            mysqli_free_result($result_modulos);
            mysqli_stmt_close($stmt_select_modulos);

            // Se houver módulos, exclui as aulas desses módulos
            if (!empty($modulos_ids)) {
                $placeholders = implode(',', array_fill(0, count($modulos_ids), '?'));
                $sql_delete_aulas = "DELETE FROM aulas WHERE id_modulo IN ($placeholders)";
                if ($stmt_delete_aulas = mysqli_prepare($link, $sql_delete_aulas)) {
                    // 's' para string, mas como são int, 'i' seria melhor, mas para implode serve 's'
                    // Precisamos de um array de tipos dinâmico para mysqli_stmt_bind_param
                    $types = str_repeat('i', count($modulos_ids));
                    mysqli_stmt_bind_param($stmt_delete_aulas, $types, ...$modulos_ids);
                    mysqli_stmt_execute($stmt_delete_aulas);
                    mysqli_stmt_close($stmt_delete_aulas);
                } else {
                    throw new Exception("Erro ao preparar query de exclusão de aulas: " . mysqli_error($link));
                }
            }
        } else {
            throw new Exception("Erro ao preparar query de seleção de módulos: " . mysqli_error($link));
        }


        // 2. Excluir MÓDULOS relacionados ao curso
        $sql_delete_modulos = "DELETE FROM modulos WHERE id_curso = ?";
        if ($stmt_delete_modulos = mysqli_prepare($link, $sql_delete_modulos)) {
            mysqli_stmt_bind_param($stmt_delete_modulos, "i", $id_curso);
            mysqli_stmt_execute($stmt_delete_modulos);
            mysqli_stmt_close($stmt_delete_modulos);
        } else {
            throw new Exception("Erro ao preparar query de exclusão de módulos: " . mysqli_error($link));
        }

        // 3. Excluir o CURSO
        $sql_delete_curso = "DELETE FROM cursos WHERE id_curso = ?";
        if ($stmt_delete_curso = mysqli_prepare($link, $sql_delete_curso)) {
            mysqli_stmt_bind_param($stmt_delete_curso, "i", $id_curso);
            mysqli_stmt_execute($stmt_delete_curso);

            if (mysqli_stmt_affected_rows($stmt_delete_curso) > 0) {
                mysqli_commit($link); // Confirma todas as operações se tudo deu certo
                $sucesso_msg = "Curso e todo o seu conteúdo associado foram excluídos com sucesso!";
            } else {
                throw new Exception("Curso não encontrado para exclusão.");
            }
            mysqli_stmt_close($stmt_delete_curso);
        } else {
            throw new Exception("Erro ao preparar query de exclusão do curso: " . mysqli_error($link));
        }

    } catch (Exception $e) {
        mysqli_rollback($link); // Desfaz todas as operações se algo deu errado
        $error_msg = "Falha ao excluir o curso: " . $e->getMessage();
    }

} else {
    $error_msg = "ID do curso inválido ou não fornecido.";
}

mysqli_close($link);

$page_title = "Excluir Curso"; // Define o título da página

require_once 'includes/admin_header.php'; // Inclui o cabeçalho
?>
        <?php if (!empty($sucesso_msg)): ?>
            <div class="success-message"><?php echo $sucesso_msg; ?></div>
        <?php endif; ?>
        <?php if (!empty($error_msg)): ?>
            <div class="error-message"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <p style="text-align: center; margin-top: 30px;">
            <a href="gerenciar_cursos.php" class="btn-link">← Voltar para Gerenciar Cursos</a>
        </p>

<style>
    .btn-link {
        display: inline-block;
        padding: 10px 20px;
        background-color: #007bff;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        transition: background-color 0.3s ease;
    }
    .btn-link:hover {
        background-color: #0056b3;
    }
</style>

<?php
require_once 'includes/admin_footer.php'; // Inclui o rodapé
?>
