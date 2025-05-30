<?php
// admin/editar_curso.php
// Não precisa de session_start() aqui, pois já está no header
require_once '../db_connect.php'; // Caminho correto para db_connect.php

$id_curso = null;
$titulo = $descricao = $idioma = $nivel = $imagem_url = "";
$titulo_err = $descricao_err = $idioma_err = $nivel_err = $imagem_url_err = "";
$sucesso_msg = "";
$error_msg = "";

// Verifica se um ID de curso foi passado na URL
if (isset($_GET["id_curso"]) && !empty(trim($_GET["id_curso"]))) {
    $id_curso = mysqli_real_escape_string($link, $_GET["id_curso"]);

    // Prepara uma declaração SELECT para buscar os detalhes do curso
    $sql = "SELECT titulo, descricao, idioma, nivel, imagem_url FROM cursos WHERE id_curso = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $param_id_curso);
        $param_id_curso = $id_curso;

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_bind_result($stmt, $titulo, $descricao, $idioma, $nivel, $imagem_url);
            if (!mysqli_stmt_fetch($stmt)) {
                $error_msg = "Curso não encontrado.";
                $id_curso = null; // Invalida o ID se não encontrar
            }
        } else {
            $error_msg = "Erro ao executar a query de busca: " . mysqli_error($link);
        }
        mysqli_stmt_close($stmt);
    } else {
        $error_msg = "Erro ao preparar a query de busca: " . mysqli_error($link);
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id_curso"])) {
    // Se o formulário foi submetido, pega o ID do POST
    $id_curso = mysqli_real_escape_string($link, $_POST["id_curso"]);

    // Valida e sanitiza os inputs (mesma lógica do adicionar_curso.php)
    if (empty(trim($_POST["titulo"]))) {
        $titulo_err = "Por favor, digite o título do curso.";
    } else {
        $titulo = trim($_POST["titulo"]);
    }

    $descricao = trim($_POST["descricao"]);

    if (empty(trim($_POST["idioma"]))) {
        $idioma_err = "Por favor, selecione o idioma.";
    } else {
        $idioma = trim($_POST["idioma"]);
    }

    if (empty(trim($_POST["nivel"]))) {
        $nivel_err = "Por favor, selecione o nível do curso.";
    } else {
        $nivel = trim($_POST["nivel"]);
    }

    $imagem_url = trim($_POST["imagem_url"]);

    // Se não houver erros de validação, atualiza o curso no banco de dados
    if (empty($titulo_err) && empty($idioma_err) && empty($nivel_err)) {
        $sql_update = "UPDATE cursos SET titulo = ?, descricao = ?, idioma = ?, nivel = ?, imagem_url = ? WHERE id_curso = ?";

        if ($stmt_update = mysqli_prepare($link, $sql_update)) {
            mysqli_stmt_bind_param($stmt_update, "sssssi", $param_titulo, $param_descricao, $param_idioma, $param_nivel, $param_imagem_url, $param_id_curso);

            $param_titulo = $titulo;
            $param_descricao = $descricao;
            $param_idioma = $idioma;
            $param_nivel = $nivel;
            $param_imagem_url = $imagem_url;
            $param_id_curso = $id_curso;

            if (mysqli_stmt_execute($stmt_update)) {
                $sucesso_msg = "Curso '" . htmlspecialchars($titulo) . "' atualizado com sucesso!";
            } else {
                $error_msg = "Ops! Algo deu errado ao atualizar o curso. Por favor, tente novamente mais tarde. " . mysqli_error($link);
            }
            mysqli_stmt_close($stmt_update);
        } else {
            $error_msg = "ERRO: Não foi possível preparar a query de atualização. " . mysqli_error($link);
        }
    }
} else {
    // Se não há ID na URL e não é POST, redireciona
    header("location: gerenciar_cursos.php");
    exit;
}

mysqli_close($link);

$page_title = "Editar Curso: " . htmlspecialchars($titulo); // Define o título da página

require_once 'includes/admin_header.php'; // Inclui o cabeçalho
?>

        <?php if (!empty($sucesso_msg)): ?>
            <div class="success-message"><?php echo $sucesso_msg; ?></div>
        <?php endif; ?>
        <?php if (!empty($error_msg)): ?>
            <div class="error-message"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <?php if (!empty($id_curso)): // Mostra o formulário apenas se o ID do curso for válido ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <input type="hidden" name="id_curso" value="<?php echo htmlspecialchars($id_curso); ?>">
            <div class="form-group">
                <label>Título do Curso</label>
                <input type="text" name="titulo" value="<?php echo htmlspecialchars($titulo); ?>">
                <span class="help-block"><?php echo $titulo_err; ?></span>
            </div>
            <div class="form-group">
                <label>Descrição</label>
                <textarea name="descricao"><?php echo htmlspecialchars($descricao); ?></textarea>
                <span class="help-block"><?php echo $descricao_err; ?></span>
            </div>
            <div class="form-group">
                <label>Idioma</label>
                <select name="idioma">
                    <option value="">Selecione</option>
                    <option value="Português" <?php echo ($idioma == 'Português') ? 'selected' : ''; ?>>Português</option>
                    <option value="Inglês" <?php echo ($idioma == 'Inglês') ? 'selected' : ''; ?>>Inglês</option>
                    <option value="Espanhol" <?php echo ($idioma == 'Espanhol') ? 'selected' : ''; ?>>Espanhol</option>
                </select>
                <span class="help-block"><?php echo $idioma_err; ?></span>
            </div>
            <div class="form-group">
                <label>Nível</label>
                <select name="nivel">
                    <option value="">Selecione</option>
                    <option value="Iniciante" <?php echo ($nivel == 'Iniciante') ? 'selected' : ''; ?>>Iniciante</option>
                    <option value="Intermediário" <?php echo ($nivel == 'Intermediário') ? 'selected' : ''; ?>>Intermediário</option>
                    <option value="Avançado" <?php echo ($nivel == 'Avançado') ? 'selected' : ''; ?>>Avançado</option>
                </select>
                <span class="help-block"><?php echo $nivel_err; ?></span>
            </div>
            <div class="form-group">
                <label>URL da Imagem de Capa (Opcional)</label>
                <input type="url" name="imagem_url" value="<?php echo htmlspecialchars($imagem_url); ?>">
                <span class="help-block"><?php echo $imagem_url_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn-submit" value="Atualizar Curso">
            </div>
        </form>
        <?php endif; ?>

        <p class="back-link"><a href="gerenciar_cursos.php">← Voltar para Gerenciar Cursos</a></p>

<?php
require_once 'includes/admin_footer.php'; // Inclui o rodapé
?>
