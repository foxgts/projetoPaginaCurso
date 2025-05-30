<?php
// admin/adicionar_modulo.php
// Não precisa de session_start() aqui, pois já está no header
require_once '../db_connect.php';

$id_curso = null;
$curso_titulo = "Curso Desconhecido";
$titulo_modulo = $descricao_modulo = "";
$ordem = "";

$titulo_modulo_err = $descricao_modulo_err = $ordem_err = "";
$sucesso_msg = "";
$error_msg = ""; // Para mensagens de erro gerais

// Pega o ID do curso da URL ou do POST
if (isset($_GET['id_curso']) && !empty(trim($_GET['id_curso']))) {
    $id_curso = mysqli_real_escape_string($link, $_GET['id_curso']);
} elseif (isset($_POST['id_curso']) && !empty(trim($_POST['id_curso']))) {
    $id_curso = mysqli_real_escape_string($link, $_POST['id_curso']);
} else {
    // Se não houver ID de curso, redireciona de volta
    header("location: gerenciar_cursos.php");
    exit;
}

// Busca o título do curso para exibição
$sql_curso_titulo = "SELECT titulo FROM cursos WHERE id_curso = ?";
if ($stmt_curso_titulo = mysqli_prepare($link, $sql_curso_titulo)) {
    mysqli_stmt_bind_param($stmt_curso_titulo, "i", $id_curso);
    mysqli_stmt_execute($stmt_curso_titulo);
    mysqli_stmt_bind_result($stmt_curso_titulo, $titulo_found);
    if (!mysqli_stmt_fetch($stmt_curso_titulo)) {
        $error_msg = "Curso não encontrado.";
        $id_curso = null; // Invalida o ID para não tentar inserir
    }
    mysqli_stmt_close($stmt_curso_titulo);
} else {
    $error_msg = "Erro ao buscar título do curso.";
}

// Processa o formulário quando ele é enviado
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($id_curso)) {

    // Valida Título do Módulo
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

    // Se não houver erros de validação, insere o módulo
    if (empty($titulo_modulo_err) && empty($ordem_err)) {
        $sql_insert = "INSERT INTO modulos (id_curso, titulo_modulo, descricao_modulo, ordem) VALUES (?, ?, ?, ?)";
        if ($stmt_insert = mysqli_prepare($link, $sql_insert)) {
            mysqli_stmt_bind_param($stmt_insert, "issi", $id_curso, $titulo_modulo, $descricao_modulo, $ordem);

            if (mysqli_stmt_execute($stmt_insert)) {
                $sucesso_msg = "Módulo '" . htmlspecialchars($titulo_modulo) . "' adicionado com sucesso ao curso '" . htmlspecialchars($curso_titulo) . "'!";
                // Limpa os campos do formulário
                $titulo_modulo = $descricao_modulo = $ordem = "";
            } else {
                $error_msg = "Erro ao adicionar módulo: " . mysqli_error($link);
            }
            mysqli_stmt_close($stmt_insert);
        } else {
            $error_msg = "Erro ao preparar query de inserção de módulo: " . mysqli_error($link);
        }
    }
}

$page_title = "Adicionar Módulo para " . htmlspecialchars($curso_titulo); // Define o título da página

require_once 'includes/admin_header.php'; // Inclui o cabeçalho
?>

        <?php if (!empty($sucesso_msg)): ?>
            <div class="success-message"><?php echo $sucesso_msg; ?></div>
        <?php endif; ?>
        <?php if (!empty($error_msg)): ?>
            <div class="error-message"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <?php if (!empty($id_curso)): // Mostra o formulário apenas se o ID do curso for válido ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id_curso=" . htmlspecialchars($id_curso); ?>" method="post">
            <input type="hidden" name="id_curso" value="<?php echo htmlspecialchars($id_curso); ?>">
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
                <input type="submit" class="btn-submit" value="Adicionar Módulo">
            </div>
        </form>
        <?php endif; ?>

        <p class="back-link"><a href="gerenciar_cursos.php">← Voltar para Gerenciar Cursos</a></p>

<?php
require_once 'includes/admin_footer.php'; // Inclui o rodapé
?>
