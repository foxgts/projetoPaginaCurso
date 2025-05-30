<?php
// admin/adicionar_curso.php
// Não precisa de session_start() aqui, pois já está no header
require_once '../db_connect.php'; // Caminho correto para db_connect.php

// Variáveis para armazenar mensagens de erro ou sucesso
$titulo = $descricao = $idioma = $nivel = $imagem_url = "";
$titulo_err = $descricao_err = $idioma_err = $nivel_err = $imagem_url_err = "";
$sucesso_msg = "";
$error_msg = ""; // Para exibir erros gerais do DB

// Processa o formulário quando ele é enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Valida Titulo
    if (empty(trim($_POST["titulo"]))) {
        $titulo_err = "Por favor, digite o título do curso.";
    } else {
        $titulo = trim($_POST["titulo"]);
    }

    // Valida Descrição (opcional, pode ser vazia, mas se preenchida, remove espaços extras)
    $descricao = trim($_POST["descricao"]);

    // Valida Idioma
    if (empty(trim($_POST["idioma"]))) {
        $idioma_err = "Por favor, selecione o idioma.";
    } else {
        $idioma = trim($_POST["idioma"]);
    }

    // Valida Nível
    if (empty(trim($_POST["nivel"]))) {
        $nivel_err = "Por favor, selecione o nível do curso.";
    } else {
        $nivel = trim($_POST["nivel"]);
    }

    // Valida URL da Imagem (opcional)
    $imagem_url = trim($_POST["imagem_url"]);
    // Você poderia adicionar mais validação aqui, como filter_var para URL

    // Se não houver erros de validação, insere o curso no banco de dados
    if (empty($titulo_err) && empty($idioma_err) && empty($nivel_err)) {
        $sql = "INSERT INTO cursos (titulo, descricao, idioma, nivel, imagem_url, ativo) VALUES (?, ?, ?, ?, ?, ?)";

        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "sssssi", $param_titulo, $param_descricao, $param_idioma, $param_nivel, $param_imagem_url, $param_ativo);

            $param_titulo = $titulo;
            $param_descricao = $descricao;
            $param_idioma = $idioma;
            $param_nivel = $nivel;
            $param_imagem_url = $imagem_url;
            $param_ativo = 1; // Por padrão, cursos adicionados via admin são ativos

            if (mysqli_stmt_execute($stmt)) {
                $sucesso_msg = "Curso '" . htmlspecialchars($titulo) . "' adicionado com sucesso!";
                // Limpa os campos do formulário após o sucesso
                $titulo = $descricao = $idioma = $nivel = $imagem_url = "";
            } else {
                $error_msg = "Ops! Algo deu errado ao adicionar o curso. Por favor, tente novamente mais tarde. " . mysqli_error($link);
            }
            mysqli_stmt_close($stmt);
        } else {
            $error_msg = "ERRO: Não foi possível preparar a query. " . mysqli_error($link);
        }
    }

    // Fecha a conexão com o banco de dados
    mysqli_close($link);
}

$page_title = "Adicionar Novo Curso"; // Define o título da página

require_once 'includes/admin_header.php'; // Inclui o cabeçalho
?>

        <?php if (!empty($sucesso_msg)): ?>
            <div class="success-message"><?php echo $sucesso_msg; ?></div>
        <?php endif; ?>
        <?php if (!empty($error_msg)): ?>
            <div class="error-message"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
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
                <input type="submit" class="btn-submit" value="Adicionar Curso">
            </div>
        </form>

        <p class="back-link"><a href="index.php">← Voltar para o Painel</a></p>

<?php
require_once 'includes/admin_footer.php'; // Inclui o rodapé
?>
