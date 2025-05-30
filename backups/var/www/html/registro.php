<?php
// registro.php
require_once 'db_connect.php';

// Inicia a sessão se ainda não estiver iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redireciona se o usuário já estiver logado
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    if ($_SESSION["tipo_usuario"] === 'admin' || $_SESSION["tipo_usuario"] === 'professor') {
        header("location: admin/index.php");
    } else {
        header("location: area_aluno.php");
    }
    exit;
}

// Define variáveis e inicializa com valores vazios
$nome_completo = $email = $senha = $confirm_senha = "";
$nome_completo_err = $email_err = $senha_err = $confirm_senha_err = "";
$registration_success = "";

// Processa o formulário quando ele é submetido
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Valida nome_completo
    if (empty(trim($_POST["nome_completo"]))) {
        $nome_completo_err = "Por favor, insira seu nome completo.";
    } else {
        $nome_completo = trim($_POST["nome_completo"]);
    }

    // Valida email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Por favor, insira um email.";
    } else {
        // Verifica se o email já existe
        $sql = "SELECT id_usuario FROM usuarios WHERE email = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            $param_email = trim($_POST["email"]);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    $email_err = "Este email já está registrado.";
                } else {
                    $email = trim($_POST["email"]);
                }
            } else {
                echo "Ops! Algo deu errado. Por favor, tente novamente mais tarde.";
            }
            mysqli_stmt_close($stmt);
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

    // Se não houver erros de entrada, insere no banco de dados
    if (empty($nome_completo_err) && empty($email_err) && empty($senha_err) && empty($confirm_senha_err)) {

        // O tipo de usuário padrão para registro público é 'estudante'
        $param_tipo_usuario = 'estudante';

        $sql_insert = "INSERT INTO usuarios (nome_completo, email, senha_hash, tipo_usuario) VALUES (?, ?, ?, ?)";

        if ($stmt_insert = mysqli_prepare($link, $sql_insert)) {
            mysqli_stmt_bind_param($stmt_insert, "ssss", $param_nome_completo, $param_email, $param_senha_hash, $param_tipo_usuario);

            // Define parâmetros
            $param_nome_completo = $nome_completo;
            $param_email = $email;
            $param_senha_hash = password_hash($senha, PASSWORD_DEFAULT); // Cria um hash da senha

            if (mysqli_stmt_execute($stmt_insert)) {
                $registration_success = "Cadastro realizado com sucesso! Você pode fazer login agora.";
                // Limpa os campos do formulário
                $nome_completo = $email = $senha = $confirm_senha = "";
            } else {
                echo "Algo deu errado. Por favor, tente novamente mais tarde. " . mysqli_error($link);
            }
            mysqli_stmt_close($stmt_insert);
        }
    }

    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro</title>
    <link rel="stylesheet" href="/css/main.css">
    <style>
        .wrapper {
            width: 400px;
            padding: 20px;
            margin: 50px auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .wrapper h2 {
            text-align: center;
            color: #007bff;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 1em;
        }
        .form-group input[type="submit"] {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            width: 100%;
            transition: background-color 0.3s;
        }
        .form-group input[type="submit"]:hover {
            background-color: #218838;
        }
        .help-block {
            color: #dc3545;
            font-size: 0.9em;
            margin-top: 5px;
            display: block;
        }
        .text-center {
            text-align: center;
            margin-top: 20px;
        }
        .text-center a {
            color: #007bff;
            text-decoration: none;
        }
        .text-center a:hover {
            text-decoration: underline;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: center;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Cadastro</h2>
        <?php if (!empty($registration_success)): ?>
            <div class="success-message"><?php echo $registration_success; ?></div>
        <?php endif; ?>
        <?php if (!empty($email_err) && strpos($email_err, "Este email já está registrado") !== false): ?>
             <div class="error-message"><?php echo $email_err; ?></div>
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
                <input type="submit" class="btn-submit" value="Cadastrar">
            </div>
            <p class="text-center">Já tem uma conta? <a href="login.php">Faça login aqui</a>.</p>
            <p class="text-center"><a href="index.php">? Voltar para a página inicial</a></p>
        </form>
    </div>
</body>
</html>
