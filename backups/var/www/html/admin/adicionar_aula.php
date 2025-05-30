<?php
// admin/adicionar_aula.php
// Não precisa de session_start() aqui, pois já está no header
require_once '../db_connect.php';

$id_modulo = null;
$modulo_titulo = "Módulo Desconhecido";
$curso_titulo = "Curso Desconhecido"; // Para exibir no breadcrumb
$id_curso_associado = null; // Para o link de volta ao curso

$titulo_aula = $conteudo = $tipo_conteudo = $url_video = "";
$ordem = "";

$titulo_aula_err = $conteudo_err = $tipo_conteudo_err = $url_video_err = $ordem_err = "";
$sucesso_msg = "";
$error_msg = "";

// Pega o ID do módulo da URL ou do POST
if (isset($_GET['id_modulo']) && !empty(trim($_GET['id_modulo']))) {
    $id_modulo = mysqli_real_escape_string($link, $_GET['id_modulo']);
} elseif (isset($_POST['id_modulo']) && !empty(trim($_POST['id_modulo']))) {
    $id_modulo = mysqli_real_escape_string($link, $_POST['id_modulo']);
} else {
    // Se não houver ID de módulo, redireciona para gerenciar_cursos ou index
    header("location: gerenciar_cursos.php"); // Melhor redirecionar para uma visão geral
    exit;
}

// Busca o título do módulo e do curso associado
$sql_modulo_info = "SELECT m.titulo_modulo, c.titulo AS curso_titulo, c.id_curso
                    FROM modulos m
                    JOIN cursos c ON m.id_curso = c.id_curso
                    WHERE m.id_modulo = ?";
if ($stmt_modulo_info = mysqli_prepare($link, $sql_modulo_info)) {
    mysqli_stmt_bind_param($stmt_modulo_info, "i", $id_modulo);
    mysqli_stmt_execute($stmt_modulo_info);
    mysqli_stmt_bind_result($stmt_modulo_info, $titulo_mod_found, $titulo_curso_found, $id_curso_found);
    if (mysqli_stmt_fetch($stmt_modulo_info)) {
        $modulo_titulo = $titulo_mod_found;
        $curso_titulo = $titulo_curso_found;
        $id_curso_associado = $id_curso_found;
    } else {
        $error_msg = "Módulo não encontrado.";
        $id_modulo = null; // Invalida o ID para não tentar inserir
    }
    mysqli_stmt_close($stmt_modulo_info);
} else {
    $error_msg = "Erro ao buscar informações do módulo.";
}

// Processa o formulário quando ele é enviado
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($id_modulo)) {

    // Valida Título da Aula
    if (empty(trim($_POST["titulo_aula"]))) {
        $titulo_aula_err = "Por favor, digite o título da aula.";
    } else {
        $titulo_aula = trim($_POST["titulo_aula"]);
    }

    // Valida Conteúdo
    if (empty(trim($_POST["conteudo"]))) {
        $conteudo_err = "Por favor, digite o conteúdo da aula.";
    } else {
        $conteudo = trim($_POST["conteudo"]);
    }

    // Valida Tipo de Conteúdo
    if (empty(trim($_POST["tipo_conteudo"]))) {
        $tipo_conteudo_err = "Por favor, selecione o tipo de conteúdo.";
    } else {
        $tipo_conteudo = trim($_POST["tipo_conteudo"]);
    }

    // Valida URL do Vídeo se o tipo for 'video'
    $url_video = trim($_POST["url_video"]);
    if ($tipo_conteudo == 'video' && empty($url_video)) {
        $url_video_err = "Para conteúdo de vídeo, a URL do vídeo é obrigatória.";
    }

    // Valida Ordem
    if (empty(trim($_POST["ordem"]))) {
        $ordem_err = "Por favor, digite a ordem da aula.";
    } elseif (!is_numeric(trim($_POST["ordem"])) || (int)trim($_POST["ordem"]) <= 0) {
        $ordem_err = "A ordem deve ser um número inteiro positivo.";
    } else {
        $ordem = (int)trim($_POST["ordem"]);
    }

    // Se não houver erros de validação, insere a aula
    if (empty($titulo_aula_err) && empty($conteudo_err) && empty($tipo_conteudo_err) && empty($url_video_err) && empty($ordem_err)) {
        $sql_insert = "INSERT INTO aulas (id_modulo, titulo_aula, conteudo, tipo_conteudo, url_video, ordem) VALUES (?, ?, ?, ?, ?, ?)";
        if ($stmt_insert = mysqli_prepare($link, $sql_insert)) {
            mysqli_stmt_bind_param($stmt_insert, "issssi", $id_modulo, $titulo_aula, $conteudo, $tipo_conteudo, $url_video, $ordem);

            if (mysqli_stmt_execute($stmt_insert)) {
                $sucesso_msg = "Aula '" . htmlspecialchars($titulo_aula) . "' adicionada com sucesso ao módulo '" . htmlspecialchars($modulo_titulo) . "'!";
                // Limpa os campos do formulário
                $titulo_aula = $conteudo = $tipo_conteudo = $url_video = $ordem = "";
            } else {
                $error_msg = "Erro ao adicionar aula: " . mysqli_error($link);
            }
            mysqli_stmt_close($stmt_insert);
        } else {
            $error_msg = "Erro ao preparar query de inserção de aula: " . mysqli_error($link);
        }
    }
}

$page_title = "Adicionar Aula para " . htmlspecialchars($modulo_titulo); // Define o título da página

require_once 'includes/admin_header.php'; // Inclui o cabeçalho
?>

        <?php if (!empty($sucesso_msg)): ?>
            <div class="success-message"><?php echo $sucesso_msg; ?></div>
        <?php endif; ?>
        <?php if (!empty($error_msg)): ?>
            <div class="error-message"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <?php if (!empty($id_modulo)): // Mostra o formulário apenas se o ID do módulo for válido ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id_modulo=" . htmlspecialchars($id_modulo); ?>" method="post">
            <input type="hidden" name="id_modulo" value="<?php echo htmlspecialchars($id_modulo); ?>">
            <div class="form-group">
                <label>Título da Aula</label>
                <input type="text" name="titulo_aula" value="<?php echo htmlspecialchars($titulo_aula); ?>">
                <span class="help-block"><?php echo $titulo_aula_err; ?></span>
            </div>
            <div class="form-group">
                <label>Conteúdo da Aula</label>
                <textarea name="conteudo"><?php echo htmlspecialchars($conteudo); ?></textarea>
                <span class="help-block"><?php echo $conteudo_err; ?></span>
            </div>
            <div class="form-group">
                <label>Tipo de Conteúdo</label>
                <select name="tipo_conteudo" id="tipo_conteudo_select"> <option value="">Selecione</option>
                    <option value="texto" <?php echo ($tipo_conteudo == 'texto') ? 'selected' : ''; ?>>Texto</option>
                    <option value="video" <?php echo ($tipo_conteudo == 'video') ? 'selected' : ''; ?>>Vídeo</option>
                    </select>
                <span class="help-block"><?php echo $tipo_conteudo_err; ?></span>
            </div>
            <div class="form-group" id="url_video_group" style="display: none;">
                <label>URL do Vídeo (para tipo "Vídeo")</label>
                <input type="url" name="url_video" value="<?php echo htmlspecialchars($url_video); ?>">
                <span class="help-block"><?php echo $url_video_err; ?></span>
            </div>
            <div class="form-group">
                <label>Ordem (Número)</label>
                <input type="number" name="ordem" value="<?php echo htmlspecialchars($ordem); ?>" min="1">
                <span class="help-block"><?php echo $ordem_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn-submit" value="Adicionar Aula">
            </div>
        </form>
        <?php endif; ?>

        <p class="back-link">
            <?php if (!empty($id_curso_associado)): ?>
                <a href="gerenciar_modulos.php?id_curso=<?php echo htmlspecialchars($id_curso_associado); ?>">← Voltar para Gerenciar Módulos</a>
            <?php else: ?>
                <a href="gerenciar_cursos.php">← Voltar para Gerenciar Cursos</a>
            <?php endif; ?>
        </p>

<script>
    // Script para mostrar/esconder o campo de URL de vídeo
    document.addEventListener('DOMContentLoaded', function() {
        var tipoConteudo = document.getElementById('tipo_conteudo_select'); // Usando o ID
        var urlVideoGroup = document.getElementById('url_video_group');

        function toggleUrlVideoField() {
            if (tipoConteudo.value === 'video') {
                urlVideoGroup.style.display = 'block';
            } else {
                urlVideoGroup.style.display = 'none';
            }
        }

        tipoConteudo.addEventListener('change', toggleUrlVideoField);
        toggleUrlVideoField(); // Executa ao carregar para o estado inicial
    });
</script>
<?php
require_once 'includes/admin_footer.php'; // Inclui o rodapé
?>
