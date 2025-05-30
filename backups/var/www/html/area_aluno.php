<?php
// area_aluno.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Verifica se o usuário é um estudante, professor ou admin (qualquer um pode acessar a área do aluno se quiser)
if (!isset($_SESSION["tipo_usuario"]) || !in_array($_SESSION["tipo_usuario"], ['estudante', 'professor', 'admin'])) {
    // Se não for nenhum dos tipos esperados, redireciona para o login ou uma página de erro
    header("location: login.php?error=acesso_nao_autorizado");
    exit;
}

$page_title = "Minha Área"; // Título da página
$nome_usuario = htmlspecialchars($_SESSION["nome_completo"] ?? 'Usuário');

// Inclua a conexão com o banco de dados se for buscar informações dinâmicas
// require_once 'db_connect.php';

// Aqui você buscaria os cursos que o aluno está matriculado
// Por enquanto, apenas um placeholder:
$cursos_matriculados = [
    ['titulo' => 'Introdução ao PHP', 'progresso' => '50%'],
    ['titulo' => 'Banco de Dados Essencial', 'progresso' => '80%'],
    ['titulo' => 'Design Web Responsivo', 'progresso' => '20%']
];

// Feche a conexão se ela foi aberta aqui
// mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Portal</title>
    <link rel="stylesheet" href="/css/main.css">
    <style>
        /* Estilos básicos para a área do aluno (podem ser movidos para main.css) */
        body { font-family: Arial, sans-serif; margin: 0; background-color: #f0f2f5; }
        .container { max-width: 960px; margin: 20px auto; padding: 20px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .header-aluno { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
        .header-aluno h1 { margin: 0; color: #333; font-size: 2em; }
        .welcome-message { font-size: 1.1em; color: #555; }
        .welcome-message strong { color: #007bff; }
        .course-list { margin-top: 20px; }
        .course-item { background-color: #e9ecef; border: 1px solid #dee2e6; border-radius: 5px; padding: 15px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; }
        .course-item h3 { margin: 0; color: #333; }
        .course-progress { font-weight: bold; color: #28a745; }
        .nav-aluno { text-align: center; margin-top: 30px; }
        .nav-aluno a { display: inline-block; background-color: #007bff; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; margin: 0 10px; transition: background-color 0.3s; }
        .nav-aluno a:hover { background-color: #0056b3; }
        .logout-btn { background-color: #dc3545; }
        .logout-btn:hover { background-color: #c82333; }
    </style>
</head>
<body>
    <div class="container">
        <header class="header-aluno">
            <h1><?php echo htmlspecialchars($page_title); ?></h1>
            <div class="welcome-message">
                Olá, <strong><?php echo $nome_usuario; ?></strong>! (Tipo: <?php echo htmlspecialchars($_SESSION["tipo_usuario"]); ?>)
            </div>
        </header>

        <h2>Meus Cursos</h2>
        <div class="course-list">
            <?php if (!empty($cursos_matriculados)): ?>
                <?php foreach ($cursos_matriculados as $curso): ?>
                    <div class="course-item">
                        <h3><?php echo htmlspecialchars($curso['titulo']); ?></h3>
                        <span class="course-progress">Progresso: <?php echo htmlspecialchars($curso['progresso']); ?></span>
                        <a href="#" class="btn-continue">Continuar</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Você ainda não está matriculado em nenhum curso. <a href="cursos.php">Explore nossos cursos!</a></p>
            <?php endif; ?>
        </div>

        <nav class="nav-aluno">
            <a href="index.php">Página Inicial</a>
            <a href="logout.php" class="logout-btn">Sair</a>
            <?php if (isset($_SESSION["tipo_usuario"]) && ($_SESSION["tipo_usuario"] === 'admin' || $_SESSION["tipo_usuario"] === 'professor')): ?>
                <a href="admin/index.php">Ir para o Painel Administrativo</a>
            <?php endif; ?>
        </nav>
    </div>
</body>
</html>
