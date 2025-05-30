<?php
// admin/editar_modulo.php
require_once '../db_connect.php';

$id_modulo = null;
$id_curso_associado = null; // Para o link de volta
$modulo_titulo_atual = "Módulo Desconhecido"; // Título original para o header
$curso_titulo = "Curso Desconhecido"; // Para exibição no header

$titulo_modulo = $descricao_modulo = "";
$ordem = "";

$titulo_modulo_err = $descricao_modulo_err = $ordem_err = "";
$sucesso_msg = "";
$error_msg = "";

// Verifica se um ID de módulo foi passado na URL ou via POST
if (isset($_GET["id_modulo"]) && !empty(trim($_GET["id_modulo"]))) {
    $id_modulo = mysqli_real_escape_string($link, $_GET["id_modulo"]);

    // Busca os detalhes do módulo e informações do curso associado
    $sql_select = "SELECT m.titulo_modulo, m.descricao_modulo, m.ordem, m.id_curso, c.titulo AS curso_titulo
                   FROM modulos m
                   JOIN cursos c ON m.id_curso = c.id_curso
                   WHERE m.id_modulo = ?";

    if ($stmt = mysqli_prepare($link, $sql_select)) {
        mysqli_stmt_bind_param($stmt, "i", $param_id_modulo);
        $param_id_modulo = $id_modulo;

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_bind_result($stmt, $titulo_modulo, $descricao_modulo, $ordem, $id_curso_associado, $curso_titulo);
            if (!mysqli_stmt_fetch($stmt)) {
                $error_msg = "Módulo não encontrado.";
                $id_modulo = null; // Invalida o ID se não encontrar
            }
        } else {
            $error_msg = "Erro ao executar a query de busca: " . mysqli_error($link);
        }
        mysqli_stmt_close($stmt);
    } else {
        $error_msg = "Erro ao preparar a query de busca: " . mysqli_error($link);
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id_modulo"])) {
    // Se o formulário foi submetido, pega o ID do POST
    $id_modulo = mysqli_real_escape_string($link, $_POST["id_modulo"]);
    $id_curso_associado = mysqli_real_escape_string($link, $_POST["id_curso_associado"]); // Pega o ID do curso para o link de volta

    // Valida Titulo do Módulo
    if (empty(trim($_POST["titulo_modulo"]))) {
        $titulo_modulo_err = "Por favor, digite o título do módulo.";
    } else {
        $titulo_modulo = trim($_POST["titulo_modulo"]);
    }

    // Valida Descrição do Módulo (opcional)
    $descricao_modulo = trim($_POST["descricao_modulo"]);

    // Valida Ordem
    if (empty(trim($_POST["ordem"]))) {
        $ordem_err = "Por favor, digite a ordem do módulo.";
    } elseif (!is_numeric(trim($_POST["ordem"])) || (int)trim($_POST["ordem"]) <= 0) {
        $ordem_err = "A ordem deve ser um número inteiro positivo.";
    } else {
        $ordem = (int)trim($_POST["ordem"]);
    }

    // Se não houver erros de validação, atualiza o módulo
    if (empty($titulo_modulo_err) && empty($ordem_err) && !empty($id_modulo)) {
        $sql_update = "UPDATE modulos SET titulo_modulo = ?, descricao_modulo = ?, ordem = ? WHERE id_modulo = ?";
        if ($stmt_update = mysqli_prepare($link, $sql_update)) {
            mysqli_stmt_bind_param($stmt_update, "ssii", $param_titulo, $param_descricao, $param_ordem, $param_id_modulo);

            $param_titulo = $titulo_modulo;
            $param_descricao = $descricao_modulo;
            $param_ordem = $ordem;
            $param_id_modulo = $id_modulo;

            if (mysqli_stmt_execute($stmt_update)) {
                $sucesso_msg = "Módulo '" . htmlspecialchars($titulo_modulo) . "' atualizado com sucesso!";
                // Se o título do módulo foi alterado, precisamos atualizar a variável para o header
                $modulo_titulo_atual = $titulo_modulo;
            } else {
                $error_msg = "Erro ao atualizar módulo: " . mysqli_error($link);
            }
            mysqli_stmt_close($stmt_update);
        } else {
            $error_msg = "Erro ao preparar query de atualização de módulo: " . mysqli_error($link);
        }
    }
    // Se houve erro e o ID do curso está no POST, busca o título do curso novamente para exibir
    if (!empty($id_curso_associado) && empty($curso_titulo)) {
        $sql_curso_titulo_reget = "SELECT titulo FROM cursos WHERE id_curso = ?";
        if ($stmt_reget = mysqli_prepare($link, $sql_curso_titulo_reget)) {
            mysqli_stmt_bind_param($stmt_reget, "i", $id_curso_associado);
            mysqli_stmt_execute($stmt_reget);
            mysqli_stmt_bind_result($stmt_reget, $titulo_reget_found);
            if (mysqli_stmt_fetch($stmt_reget)) {
                $curso_titulo = $titulo_reget_found;
            }
            mysqli_stmt_close($stmt_reget);
        }
    }

} else {
    // Se não há ID na URL e não é POST, ou se o ID é inválido
    header("location: gerenciar_cursos.php"); // Redireciona para onde se gerencia os cursos
    exit;
}

mysqli_close($link);

$page_title = "Editar Módulo: " . htmlspecialchars($modulo_titulo_atual) . " (Curso: " . htmlspecialchars($curso_titulo) . ")";

require_once 'includes/admin_header.php'; // Inclui o cabeçalho
?>

        <?php if (!empty($sucesso_msg)): ?>
            <div class="success-message"><?php echo $sucesso_msg; ?></div>
        <?php endif; ?>
        <?php if (!empty($error_msg)): ?>
            <div class="error-message"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <?php if (!empty($id_modulo)): // Mostra o formulário apenas se o ID do módulo for válido ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <input type="hidden" name="id_modulo" value="<?php echo htmlspecialchars($id_modulo); ?>">
            <input type="hidden" name="id_curso_associado" value="<?php echo htmlspecialchars($id_curso_associado); ?>">
            <div class="form-group">
                <label>Título do Módulo</label>
                <input type="text" name="titulo_modulo" value="<?php echo htmlspecialchars($titulo_modulo); ?>">
                <span class="help-block"><?php echo $titulo_modulo_err; ?></span>
            </div>
            <div class="form-group">
                <label>Descrição do Módulo (Opcional)</label>
                <textarea name="descricao_modulo"><?php echo htmlspecialchars($descricao_modulo); ?></textarea>
                <span class="help-block"><?php echo $descricao_modulo_err; ?></span>
            </div>
            <div class="form-group">
                <label>Ordem (Número)</label>
                <input type="number" name="ordem" value="<?php echo htmlspecialchars($ordem); ?>" min="1">
                <span class="help-block"><?php echo $ordem_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn-submit" value="Atualizar Módulo">
            </div>
        </form>
        <?php endif; ?>

        <p class="back-link">
            <?php if (!empty($id_curso_associado)): ?>
                <a href="gerenciar_modulos.php?id_curso=<?php echo htmlspecialchars($id_curso_associado); ?>">← Voltar para Módulos do Curso "<?php echo htmlspecialchars($curso_titulo); ?>"</a>
            <?php else: ?>
                <a href="gerenciar_cursos.php">← Voltar para Gerenciar Cursos</a>
            <?php endif; ?>
        </p>

<?php
require_once 'includes/admin_footer.php'; // Inclui o rodapé
?>
