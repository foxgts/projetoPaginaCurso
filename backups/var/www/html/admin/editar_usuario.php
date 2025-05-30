<?php
// admin/editar_usuario.php
require_once '../db_connect.php';

// Inclui o cabeçalho do administrador
require_once 'includes/admin_header.php';

// Somente administradores podem editar usuários
if ($_SESSION["tipo_usuario"] !== 'admin') {
    header("location: index.php?error=acesso_nao_autorizado");
    exit;
}

// Define variáveis e inicializa com valores vazios
$id_usuario = $nome_completo = $email = $senha = $confirm_senha = $telefone = $data_nascimento = $foto_perfil = $descricao_perfil = $url_lattes = $area_atuacao = $tipo_usuario = "";
$nome_completo_err = $email_err = $senha_err = $confirm_senha_err = $telefone_err = $data_nascimento_err = $foto_perfil_err = $descricao_perfil_err = $url_lattes_err = $area_atuacao_err = $tipo_usuario_err = "";

// Processa o GET parameter 'id' (ID do usuário a ser editado)
if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $id_usuario = trim($_GET["id"]);

    // Prepara uma declaração SELECT para buscar os dados do usuário
    $sql_select = "SELECT nome_completo, email, telefone, data_nascimento, foto_perfil, descricao_perfil, url_lattes, area_atuacao, tipo_usuario FROM usuarios WHERE id_usuario = ?";
    if ($stmt_select = mysqli_prepare($link, $sql_select)) {
        mysqli_stmt_bind_param($stmt_select, "i", $param_id_usuario);
        $param_id_usuario = $id_usuario;

        if (mysqli_stmt_execute($stmt_select)) {
            mysqli_stmt_bind_result($stmt_select, $nome_completo, $email, $telefone, $data_nascimento, $foto_perfil, $descricao_perfil, $url_lattes, $area_atuacao, $tipo_usuario);
            if (!mysqli_stmt_fetch($stmt_select)) {
                // Usuário não encontrado, redireciona de volta com erro
                header("location: gerenciar_usuarios.php?error_msg=Usuário não encontrado.");
                exit();
            }
        } else {
            echo "Ops! Algo deu errado ao buscar os dados do usuário. " . mysqli_error($link);
        }
        mysqli_stmt_close($stmt_select);
    } else {
        echo "Erro ao preparar a consulta SELECT.";
    }
} else {
    // ID não fornecido, redireciona de volta
    header("location: gerenciar_usuarios.php?error_msg=ID do usuário não fornecido.");
    exit();
}


// Processa o formulário quando ele é submetido
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Obtém e sanitiza novos campos do POST
    $id_usuario = trim($_POST["id_usuario"]); // Oculto no formulário, mas necessário para UPDATE
    $nome_completo = trim($_POST["nome_completo"]);
    $email = trim($_POST["email"]);
    $telefone = trim($_POST["telefone"]);
    $data_nascimento = trim($_POST["data_nascimento"]);
    $foto_perfil = trim($_POST["foto_perfil"]);
    $descricao_perfil = trim($_POST["descricao_perfil"]);
    $url_lattes = trim($_POST["url_lattes"]);
    $area_atuacao = trim($_POST["area_atuacao"]);
    $tipo_usuario = trim($_POST["tipo_usuario"]);
    $senha = trim($_POST["senha"]);
    $confirm_senha = trim($_POST["confirm_senha"]);

    // Validação básica para nome_completo
    if (empty($nome_completo)) {
        $nome_completo_err = "Por favor, insira o nome completo.";
    }

    // Validação email
    if (empty($email)) {
        $email_err = "Por favor, insira um email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_err = "Formato de email inválido.";
    } else {
        // Verifica se o email já existe (excluindo o usuário atual)
        $sql_check_email = "SELECT id_usuario FROM usuarios WHERE email = ? AND id_usuario != ?";
        if ($stmt_check_email = mysqli_prepare($link, $sql_check_email)) {
            mysqli_stmt_bind_param($stmt_check_email, "si", $param_email_check, $param_id_usuario_check);
            $param_email_check = $email;
            $param_id_usuario_check = $id_usuario;
            if (mysqli_stmt_execute($stmt_check_email)) {
                mysqli_stmt_store_result($stmt_check_email);
                if (mysqli_stmt_num_rows($stmt_check_email) == 1) {
                    $email_err = "Este email já está registrado por outro usuário.";
                }
            } else {
                echo "Ops! Algo deu errado. Por favor, tente novamente mais tarde.";
            }
            mysqli_stmt_close($stmt_check_email);
        }
    }

    // Validação de senha (apenas se a senha for alterada)
    if (!empty($senha)) { // Se o campo senha não estiver vazio, o usuário deseja mudar a senha
        if (strlen($senha) < 6) {
            $senha_err = "A senha deve ter pelo menos 6 caracteres.";
        }
        if (empty($confirm_senha)) {
            $confirm_senha_err = "Por favor, confirme a nova senha.";
        } elseif ($senha != $confirm_senha) {
            $confirm_senha_err = "As senhas não coincidem.";
        }
    }

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

    // Se não houver erros, atualiza no banco de dados
    if (empty($nome_completo_err) && empty($email_err) && empty($senha_err) && empty($confirm_senha_err) && empty($tipo_usuario_err) && empty($data_nascimento_err) && empty($foto_perfil_err) && empty($url_lattes_err)) {

        // Se a senha foi fornecida, atualiza-a também
        $update_password_sql_part = "";
        if (!empty($senha)) {
            $update_password_sql_part = ", senha_hash = ?";
        }

        $sql_update = "UPDATE usuarios SET nome_completo = ?, email = ?, telefone = ?, data_nascimento = ?, foto_perfil = ?, descricao_perfil = ?, url_lattes = ?, area_atuacao = ?, tipo_usuario = ?" . $update_password_sql_part . " WHERE id_usuario = ?";

        if ($stmt_update = mysqli_prepare($link, $sql_update)) {

            // Prepara os parâmetros para bind
            $params_array = [
                $nome_completo,
                $email,
                !empty($telefone) ? $telefone : NULL,
                !empty($data_nascimento) ? $data_nascimento : NULL,
                !empty($foto_perfil) ? $foto_perfil : NULL,
                !empty($descricao_perfil) ? $descricao_perfil : NULL,
                !empty($url_lattes) ? $url_lattes : NULL,
                !empty($area_atuacao) ? $area_atuacao : NULL,
                $tipo_usuario
            ];
            $types_string = "sssssssss";

            if (!empty($senha)) {
                $params_array[] = password_hash($senha, PASSWORD_DEFAULT);
                $types_string .= "s";
            }
            $params_array[] = $id_usuario;
            $types_string .= "i";

            // Cria um array de referências para mysqli_stmt_bind_param
            $bind_params = [$types_string];
            foreach ($params_array as $key => $value) {
                $bind_params[] = &$params_array[$key];
            }

            // Chama mysqli_stmt_bind_param usando call_user_func_array para lidar com número variável de parâmetros
            call_user_func_array('mysqli_stmt_bind_param', array_merge([$stmt_update], $bind_params));

            if (mysqli_stmt_execute($stmt_update)) {
                header("location: gerenciar_usuarios.php?sucesso_msg=Usuário atualizado com sucesso.");
                exit;
            } else {
                echo "Ops! Algo deu errado ao atualizar o usuário. " . mysqli_error($link);
            }
            mysqli_stmt_close($stmt_update);
        } else {
            echo "Erro ao preparar a consulta UPDATE.";
        }
    }
    mysqli_close($link);
}

$page_title = "Editar Usuário"; // Define o título da página
?>

        <h2><?php echo htmlspecialchars($page_title); ?></h2>
        <p>Edite os detalhes do usuário.</p>

        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . htmlspecialchars($id_usuario); ?>" method="post">
            <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($id_usuario); ?>">
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
                <label>URL da Foto de Perfil (opcional)</label>
                <input type="url" name="foto_perfil" class="form-control" value="<?php echo htmlspecialchars($foto_perfil); ?>">
                <span class="help-block"><?php echo $foto_perfil_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($descricao_perfil_err)) ? 'has-error' : ''; ?>">
                <label>Descrição/Biografia (opcional)</label>
                <textarea name="descricao_perfil" class="form-control"><?php echo htmlspecialchars($descricao_perfil); ?></textarea>
                <span class="help-block"><?php echo $descricao_perfil_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($url_lattes_err)) ? 'has-error' : ''; ?>">
                <label>URL Lattes (opcional)</label>
                <input type="url" name="url_lattes" class="form-control" value="<?php echo htmlspecialchars($url_lattes); ?>">
                <span class="help-block"><?php echo $url_lattes_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($area_atuacao_err)) ? 'has-error' : ''; ?>">
                <label>Área de Atuação (opcional)</label>
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
                <label>Nova Senha (deixe em branco para não alterar)</label>
                <input type="password" name="senha" class="form-control">
                <span class="help-block"><?php echo $senha_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($confirm_senha_err)) ? 'has-error' : ''; ?>">
                <label>Confirmar Nova Senha</label>
                <input type="password" name="confirm_senha" class="form-control">
                <span class="help-block"><?php echo $confirm_senha_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn-submit" value="Salvar Alterações">
                <a href="gerenciar_usuarios.php" class="btn-link">Cancelar</a>
            </div>
        </form>

<?php
require_once 'includes/admin_footer.php'; // Inclui o rodapé
?>