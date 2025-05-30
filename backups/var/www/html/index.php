<?php
// index.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    // Se estiver logado, redireciona para a área de estudante ou admin
    if (isset($_SESSION["tipo_usuario"]) && ($_SESSION["tipo_usuario"] === 'admin' || $_SESSION["tipo_usuario"] === 'professor')) {
        header("location: admin/index.php");
    } else { // Presume-se que seja 'estudante' ou outro tipo que vai para a área do aluno
        header("location: area_aluno.php");
    }
    exit;
}

// Mensagem de erro para acesso negado ao painel admin
$error_message = "";
if (isset($_GET['error']) && $_GET['error'] == 'acesso_negado') {
    $error_message = "Você não tem permissão para acessar o painel administrativo. Por favor, faça login com uma conta de administrador ou professor.";
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bem-vindo ao Nosso Portal de Cursos!</title>
    <link rel="stylesheet" href="/css/main.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background-color: #f0f2f5; text-align: center; padding-top: 50px;}
        .main-content { background-color: #ffffff; max-width: 800px; margin: 0 auto; padding: 40px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .main-content h1 { color: #007bff; margin-bottom: 20px; }
        .main-content p { color: #555; line-height: 1.6; margin-bottom: 30px; }
        .action-buttons a { display: inline-block; background-color: #28a745; color: white; padding: 12px 25px; border-radius: 5px; text-decoration: none; font-size: 1.1em; margin: 0 10px; transition: background-color 0.3s; }
        .action-buttons a:hover { background-color: #218838; }
        .action-buttons .login-btn { background-color: #007bff; }
        .action-buttons .login-btn:hover { background-color: #0056b3; }
    </style>
</head>
<body>
    <div class="main-content">
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <h1>Bem-vindo ao Nosso Portal de Cursos!</h1>
        <p>Descubra uma vasta gama de cursos de alta qualidade para impulsionar sua carreira e seus conhecimentos. Aprenda no seu próprio ritmo, com instrutores experientes e conteúdo atualizado.</p>
        <div class="action-buttons">
            <a href="registro.php">Cadastre-se Grátis</a>
            <a href="login.php" class="login-btn">Fazer Login</a>
        </div>
        <p style="margin-top: 30px; font-size: 0.9em; color: #888;">Explore, aprenda e cresça conosco.</p>
    </div>
</body>
</html>