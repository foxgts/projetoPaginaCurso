<?php
// login.php
// Inicia a sessão se ainda não estiver iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redireciona se o usuário já estiver logado
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    if (isset($_SESSION["tipo_usuario"]) && ($_SESSION["tipo_usuario"] === 'admin' || $_SESSION["tipo_usuario"] === 'professor')) {
        header("location: admin/index.php");
    } else { // Presume-se que seja 'estudante' ou outro tipo que vai para a área do aluno
        header("location: area_aluno.php");
    }
    exit;
}

require_once 'db_connect.php';

$email = $senha = "";
$email_err = $senha_err = $login_err = "";

// Processa o formulário quando ele é submetido
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Valida email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Por favor, insira o email.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Valida senha
    if (empty(trim($_POST["senha"]))) {
        $senha_err = "Por favor, insira sua senha.";
    } else {
        $senha = trim($_POST["senha"]);
    }

    // Valida credenciais
    if (empty($email_err) && empty($senha_err)) {
        $sql = "SELECT id_usuario, nome_completo, email, senha_hash, tipo_usuario FROM usuarios WHERE email = ?";

        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            $param_email = $email;

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);

                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $id_usuario_db, $nome_completo_db, $email_db, $senha_hash_db, $tipo_usuario_db);
                    if (mysqli_stmt_fetch($stmt)) {
                        if (password_verify($senha, $senha_hash_db)) {
                            // Senha correta, inicia a sessão
                            session_start();

                            $_SESSION["loggedin"] = true;
                            $_SESSION["id_usuario"] = $id_usuario_db;
                            $_SESSION["nome_completo"] = $nome_completo_db;
                            $_SESSION["email"] = $email_db;
                            $_SESSION["tipo_usuario"] = $tipo_usuario_db; // Armazena o tipo de usuário na sessão

                            // Redireciona para a página apropriada com base no tipo de usuário
                            if ($tipo_usuario_db === 'admin' || $tipo_usuario_db === 'professor') {
                                header("location: admin/index.php");
                            } else {
                                header("location: area_aluno.php");
                            }
                            exit;
                        } else {
                            $login_err = "Email ou senha inválidos.";
                        }
                    }
                } else {
                    $login_err = "Email ou senha inválidos.";
                }
            } else {
                echo "Ops! Algo deu errado. Por favor, tente novamente mais tarde.";
            }
            mysqli_stmt_close($stmt);
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
    <title>Login</title>
    <link rel="stylesheet" href="/css/main.css">
    <style>
        .wrapper {
            width: 360px;
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
            background-color: #007bff;
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
            background-color: #0056b3;
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
        <h2>Login</h2>
        <?php if (!empty($login_err)): ?>
            <div class="error-message"><?php echo $login_err; ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['error']) && $_GET['error'] == 'acesso_nao_autorizado'): ?>
            <div class="error-message">Acesso não autorizado para a página solicitada. Faça login com um tipo de usuário permitido.</div>
        <?php endif; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
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
            <div class="form-group">
                <input type="submit" class="btn-submit" value="Login">
            </div>
            <p class="text-center">Não tem uma conta? <a href="registro.php">Cadastre-se agora</a>.</p>
            <p class="text-center"><a href="index.php">? Voltar para a página inicial</a></p>
        </form>
    </div>
</body>
</html>