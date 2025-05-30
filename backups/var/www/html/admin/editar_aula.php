<?php
// admin/editar_aula.php
require_once '../db_connect.php';

$id_aula = null;
$id_modulo_associado = null; // Para o link de volta
$id_curso_associado = null; // Para o link de volta ao curso
$modulo_titulo = "Módulo Desconhecido";
$curso_titulo = "Curso Desconhecido"; // Para exibição no breadcrumb

$titulo_aula = $conteudo = $tipo_conteudo = $url_video = "";
$ordem = "";

$titulo_aula_err = $conteudo_err = $tipo_conteudo_err = $url_video_err = $ordem_err = "";
$sucesso_msg = "";
$error_msg = "";

// Verifica se um ID de aula foi passado na URL
if (isset($_GET["id_aula"]) && !empty(trim($_GET["id_aula"]))) {
    $id_aula = mysqli_real_escape_string($link, $_GET["id_aula"]);

    // Busca os detalhes da aula e informações do módulo/curso associado
    $sql_select = "SELECT a.titulo_aula, a.conteudo, a.tipo_conteudo, a.url_video, a.ordem,
                          m.id_modulo, m.titulo_modulo, c.id_curso, c.titulo AS curso_titulo
                   FROM aulas a
                   JOIN modulos m ON a.id_modulo = m.id_modulo
                   JOIN cursos c ON m.id_curso = c.id_curso
                   WHERE a.id_aula = ?";

    if ($stmt = mysqli_prepare($link, $sql_select)) {
        mysqli_stmt_bind_param($stmt, "i", $param_id_aula);
        $param_id_aula = $id_aula;

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_bind_result($stmt, $titulo_aula, $conteudo, $tipo_conteudo, $url_video, $ordem,
                                   $id_modulo_associado, $modulo_titulo, $id_curso_associado, $curso_titulo);
            if (!mysqli_stmt_fetch($stmt)) {
                $error_msg = "Aula não encontrada.";
                $id_aula = null; // Invalida o ID se não encontrar
            }
        } else {
            $error_msg = "Erro ao executar a query de busca: " . mysqli_error($link);
        }
        mysqli_stmt_close($stmt);
    } else {
        $error_msg = "Erro ao preparar a query de busca: " . mysqli_error($link);
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id_aula"])) {
    // Se o formulário foi submetido, pega o ID do POST
    $id_aula = mysqli_real_escape_string($link, $_POST["id_aula"]);
    $id_modulo_associado = mysqli_real_escape_string($link, $_POST["id_modulo_associado"]);
    $id_curso_associado = mysqli_real_escape_string($link, $_POST["id_curso_associado"]);
    $modulo_titulo = mysqli_real_escape_string($link, $_POST["modulo_titulo"]); // Pega do hidden para exibir no título
    $curso_titulo = mysqli_real_escape_string($link, $_POST["curso_titulo"]); // Pega do hidden para exibir no título

    // Valida Titulo da Aula
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

    // Se não houver erros de validação, atualiza a aula
    if (empty($titulo_aula_err) && empty($conteudo_err) && empty($tipo_conteudo_err) && empty($url_video_err) && empty($ordem_err) && !empty($id_aula)) {
        $sql_update = "UPDATE aulas SET titulo_aula = ?, conteudo = ?, tipo_conteudo = ?, url_video = ?, ordem = ? WHERE id_aula = ?";
        if ($stmt_update = mysqli_prepare($link, $sql_update)) {
            mysqli_stmt_bind_param($stmt_update, "ssssii", $param_titulo, $param_conteudo, $param_tipo, $param_url_video, $param_ordem, $param_id_aula);

            $param_titulo = $titulo_aula;
            $param_conteudo = $conteudo;
            $param_tipo = $tipo_conteudo;
            $param_url_video = $url_video;
            $param_ordem = $ordem;
            $param_id_aula = $id_aula;

            if (mysqli_stmt_execute($stmt_update)) {
                $sucesso_msg = "Aula '" . htmlspecialchars($titulo_aula) . "' atualizada com sucesso!";
                // Se o título da aula foi alterado, precisamos atualizar a variável para o header
                // Não é estritamente necessário aqui, pois o page_title já foi definido pelo GET
            } else {
                $error_msg = "Erro ao atualizar aula: " . mysqli_error($link);
            }
            mysqli_stmt_close($stmt_update);
        } else {
            $error_msg = "Erro ao preparar query de atualização de aula: " . mysqli_error($link);
        }
    }
} else {
    // Se não há ID na URL e não é POST, redireciona
    header("location: gerenciar_cursos.php"); // Ou para uma página mais geral de erro
    exit;
}

mysqli_close($link);

$page_title = "Editar Aula: " . htmlspecialchars($titulo_aula) . " (Módulo: " . htmlspecialchars($modulo_titulo) . ")";

require_once 'includes/admin_header.php'; // Inclui o cabeçalho
?>

        <?php if (!empty($sucesso_msg)): ?>
            <div class="success-message"><?php echo $sucesso_msg; ?></div>
        <?php endif; ?>
        <?php if (!empty($error_msg)): ?>
            <div class="error-message"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <?php if (!empty($id_aula)): // Mostra o formulário apenas se o ID da aula for válido ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <input type="hidden" name="id_aula" value="<?php echo htmlspecialchars($id_aula); ?>">
            <input type="hidden" name="id_modulo_associado" value="<?php echo htmlspecialchars($id_modulo_associado); ?>">
            <input type="hidden" name="id_curso_associado" value="<?php echo htmlspecialchars($id_curso_associado); ?>">
            <input type="hidden" name="modulo_titulo" value="<?php echo htmlspecialchars($modulo_titulo); ?>">
            <input type="hidden" name="curso_titulo" value="<?php echo htmlspecialchars($curso_titulo); ?>">

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
                <select name="tipo_conteudo" id="tipo_conteudo_select">
                    <option value="">Selecione</option>
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
                <input type="submit" class="btn-submit" value="Atualizar Aula">
            </div>
        </form>
        <?php endif; ?>

        <p class="back-link">
            <?php if (!empty($id_modulo_associado)): ?>
                <a href="gerenciar_aulas.php?id_modulo=<?php echo htmlspecialchars($id_modulo_associado); ?>">← Voltar para Aulas de "<?php echo htmlspecialchars($modulo_titulo); ?>"</a>
            <?php else: ?>
                <a href="gerenciar_modulos.php?id_curso=<?php echo htmlspecialchars($id_curso_associado); ?>">← Voltar para Gerenciar Módulos</a>
            <?php endif; ?>
        </p>

<script>
    // Script para mostrar/esconder o campo de URL de vídeo
    document.addEventListener('DOMContentLoaded', function() {
        var tipoConteudo = document.getElementById('tipo_conteudo_select');
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
