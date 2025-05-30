<?php
// admin/adicionar_usuario.php
require_once '../db_connect.php';

// Inclui o cabeçalho do administrador
require_once 'includes/admin_header.php';

// Somente administradores podem adicionar usuários
if ($_SESSION["tipo_usuario"] !== 'admin') {
    header("location: index.php?error=acesso_nao_autorizado");
    exit;
}

// Define variáveis e inicializa com valores vazios
$nome_completo = $email = $senha = $confirm_senha = $telefone = $data_nascimento = $foto_perfil = $descricao_perfil = $url_lattes = $area_atuacao = $tipo_usuario = "";
$nome_completo_err = $email_err = $senha_err = $confirm_senha_err = $telefone_err = $data_nascimento_err = $foto_perfil_err = $descricao_perfil_err = $url_lattes_err = $area_atuacao_err = $tipo_usuario_err = "";

// Processa o formulário quando ele é submetido
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Valida nome_completo
    if (empty(trim($_POST["nome_completo"]))) {
        $nome_completo_err = "Por favor, insira o nome completo.";
    } else {
        $nome_completo = trim($_POST["nome_completo"]);
    }

    // Valida email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Por favor, insira um email.";
    } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
        $email_err = "Formato de email inválido.";
    } else {
        // Verifica se o email já existe
        $sql_check_email = "SELECT id_usuario FROM usuarios WHERE email = ?";
        if ($stmt_check_email = mysqli_prepare($link, $sql_check_email)) {
            mysqli_stmt_bind_param($stmt_check_email, "s", $param_email);
            $param_email = trim($_POST["email"]);
            if (mysqli_stmt_execute($stmt_check_email)) {
                mysqli_stmt_store_result($stmt_check_email);
                if (mysqli_stmt_num_rows($stmt_check_email) == 1) {
                    $email_err = "Este email já está registrado.";
                } else {
                    $email = trim($_POST["email"]);
                }
            } else {
                echo "Ops! Algo deu errado. Por favor, tente novamente mais tarde.";
            }
            mysqli_stmt_close($stmt_check_email);
        }
    }

    // Valida senha
    if (empty(trim($_POST["senha"]))) {
        $senha_err = "Por favor, insira uma senha.";
    } elseif (strlen(trim($_POST["senha"])) < 6) {
        $senha_err = "A senha deve ter pelo menos 6 caracteres.";
    } else {
        $senha = trim($_POST["senha"]);
    }

    // Valida confirmar senha
    if (empty(trim($_POST["confirm_senha"]))) {
        $confirm_senha_err = "Por favor, confirme a senha.";
    } else {
        $confirm_senha = trim($_POST["confirm_senha"]);
        if (empty($senha_err) && ($senha != $confirm_senha)) {
            $confirm_senha_err = "As senhas não coincidem.";
        }
    }

    // Novos campos
    $telefone = trim($_POST["telefone"]);
    $data_nascimento = trim($_POST["data_nascimento"]);
    $foto_perfil = trim($_POST["foto_perfil"]);
    $descricao_perfil = trim($_POST["descricao_perfil"]);
    $url_lattes = trim($_POST["url_lattes"]);
    $area_atuacao = trim($_POST["area_atuacao"]);
    $tipo_usuario = trim($_POST["tipo_usuario"]);

    // Validação para tipo_usuario
    $allowed_types = ['estudante', 'professor', 'admin'];
    if (empty($tipo_usuario) || !in_array($tipo_usuario, $allowed_types)) {
        $tipo_usuario_err = "Por favor, selecione um tipo de usuário válido.";
    }

    // Validação básica para outros campos (ajuste conforme necessário)
    if (!empty($data_nascimento) && !strtotime($data_nascimento)) {
        $data_nascimento_err = "Data de nascimento inválida.";
    }
    if (!empty($foto_perfil) && !filter_var($foto_perfil, FILTER_VALIDATE_URL)) {
        $foto_perfil_err = "URL da foto de perfil inválida.";
    }
    if (!empty($url_lattes) && !filter_var($url_lattes, FILTER_VALIDATE_URL)) {
        $url_lattes_err = "URL Lattes inválida.";
    }


    // Se não houver erros de entrada, insere no banco de dados
    if (empty($nome_completo_err) && empty($email_err) && empty($senha_err) && empty($confirm_senha_err) && empty($tipo_usuario_err) && empty($data_nascimento_err) && empty($foto_perfil_err) && empty($url_lattes_err)) {

        $sql_insert = "INSERT INTO usuarios (nome_completo, email, senha_hash, tipo_usuario, telefone, data_nascimento, foto_perfil, descricao_perfil, url_lattes, area_atuacao) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        if ($stmt_insert = mysqli_prepare($link, $sql_insert)) {
            mysqli_stmt_bind_param($stmt_insert, "ssssssssss", $param_nome_completo, $param_email, $param_senha_hash, $param_tipo_usuario, $param_telefone, $param_data_nascimento, $param_foto_perfil, $param_descricao_perfil, $param_url_lattes, $param_area_atuacao);

            // Define parâmetros
            $param_nome_completo = $nome_completo;
            $param_email = $email;
            $param_senha_hash = password_hash($senha, PASSWORD_DEFAULT); // Cria um hash da senha
            $param_tipo_usuario = $tipo_usuario;
            $param_telefone = !empty($telefone) ? $telefone : NULL;
            $param_data_nascimento = !empty($data_nascimento) ? $data_nascimento : NULL;
            $param_foto_perfil = !empty($foto_perfil) ? $foto_perfil : NULL;
            $param_descricao_perfil = !empty($descricao_perfil) ? $descricao_perfil : NULL;
            $param_url_lattes = !empty($url_lattes) ? $url_lattes : NULL;
            $param_area_atuacao = !empty($area_atuacao) ? $area_atuacao : NULL;

            if (mysqli_stmt_execute($stmt_insert)) {
                header("location: gerenciar_usuarios.php?sucesso_msg=Usuário adicionado com sucesso.");
                exit;
            } else {
                echo "Algo deu errado. Por favor, tente novamente mais tarde. " . mysqli_error($link);
            }
            mysqli_stmt_close($stmt_insert);
        }
    }

    mysqli_close($link);
}

$page_title = "Adicionar Novo Usuário"; // Define o título da página
?>

        <h2><?php echo htmlspecialchars($page_title); ?></h2>
        <p>Preencha este formulário para adicionar um novo usuário.</p>

        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group <?php echo (!empty($nome_completo_err)) ? 'has-error' : ''; ?>">
                <label>Nome Completo</label>
                <input type="text" name="nome_completo" class="form-control" value="<?php echo htmlspecialchars($nome_completo); ?>">
                <span class="help-block"><?php echo $nome_completo_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($email_err)) ? 'has-error' : ''; ?>">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>">
                <span class="help-block"><?php echo $email_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($telefone_err)) ? 'has-error' : ''; ?>">
                <label>Telefone (opcional)</label>
                <input type="text" name="telefone" class="form-control" value="<?php echo htmlspecialchars($telefone); ?>">
                <span class="help-block"><?php echo $telefone_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($data_nascimento_err)) ? 'has-error' : ''; ?>">
                <label>Data de Nascimento (opcional)</label>
                <input type="date" name="data_nascimento" class="form-control" value="<?php echo htmlspecialchars($data_nascimento); ?>">
                <span class="help-block"><?php echo $data_nascimento_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($foto_perfil_err)) ? 'has-error' : ''; ?>">
                <label>URL da Foto de Perfil (opcional, p/ ex: https://exemplo.com/foto.jpg)</label>
                <input type="url" name="foto_perfil" class="form-control" value="<?php echo htmlspecialchars($foto_perfil); ?>">
                <span class="help-block"><?php echo $foto_perfil_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($descricao_perfil_err)) ? 'has-error' : ''; ?>">
                <label>Descrição/Biografia (opcional)</label>
                <textarea name="descricao_perfil" class="form-control"><?php echo htmlspecialchars($descricao_perfil); ?></textarea>
                <span class="help-block"><?php echo $descricao_perfil_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($url_lattes_err)) ? 'has-error' : ''; ?>">
                <label>URL Lattes (opcional, apenas para professores)</label>
                <input type="url" name="url_lattes" class="form-control" value="<?php echo htmlspecialchars($url_lattes); ?>">
                <span class="help-block"><?php echo $url_lattes_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($area_atuacao_err)) ? 'has-error' : ''; ?>">
                <label>Área de Atuação (opcional, apenas para professores)</label>
                <input type="text" name="area_atuacao" class="form-control" value="<?php echo htmlspecialchars($area_atuacao); ?>">
                <span class="help-block"><?php echo $area_atuacao_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($tipo_usuario_err)) ? 'has-error' : ''; ?>">
                <label>Tipo de Usuário</label>
                <select name="tipo_usuario" class="form-control">
                    <option value="estudante" <?php echo ($tipo_usuario == 'estudante') ? 'selected' : ''; ?>>Estudante</option>
                    <option value="professor" <?php echo ($tipo_usuario == 'professor') ? 'selected' : ''; ?>>Professor</option>
                    <option value="admin" <?php echo ($tipo_usuario == 'admin') ? 'selected' : ''; ?>>Administrador</option>
                </select>
                <span class="help-block"><?php echo $tipo_usuario_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($senha_err)) ? 'has-error' : ''; ?>">
                <label>Senha</label>
                <input type="password" name="senha" class="form-control">
                <span class="help-block"><?php echo $senha_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($confirm_senha_err)) ? 'has-error' : ''; ?>">
                <label>Confirmar Senha</label>
                <input type="password" name="confirm_senha" class="form-control">
                <span class="help-block"><?php echo $confirm_senha_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn-submit" value="Adicionar Usuário">
                <a href="gerenciar_usuarios.php" class="btn-link">Cancelar</a>
            </div>
        </form>

<?php
require_once 'includes/admin_footer.php'; // Inclui o rodapé
?>