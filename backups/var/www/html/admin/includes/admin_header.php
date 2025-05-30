<?php
// admin/includes/admin_header.php
// Inicia a sessão se ainda não estiver iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado, caso contrário, redireciona
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login.php");
    exit;
}

// VERIFICAÇÃO DE NÍVEL DE ACESSO PARA O PAINEL ADMIN
// Apenas 'admin' e 'professor' podem acessar o painel administrativo.
$allowed_roles_admin_panel = ['admin', 'professor'];
if (!isset($_SESSION["tipo_usuario"]) || !in_array($_SESSION["tipo_usuario"], $allowed_roles_admin_panel)) {
    // Redireciona para a página inicial com mensagem de acesso negado
    header("location: ../index.php?error=acesso_negado");
    exit;
}

// Obtém o nome do arquivo atual para destacar o link ativo no menu
$current_page = basename($_SERVER['PHP_SELF']);

// Define um título padrão caso a página não defina um
if (!isset($page_title)) {
    $page_title = "Painel Administrativo";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Admin</title>
    <link rel="stylesheet" href="/css/admin.css">
    <style>
        /* Estilos básicos para garantir que o layout mínimo funcione, caso o admin.css externo falhe. */
        body { font-family: Arial, sans-serif; margin: 0; display: flex; }
        .wrapper { display: flex; width: 100%; }
        .sidebar { width: 220px; background-color: #2c3e50; color: white; height: 100vh; padding-top: 20px; }
        .sidebar-header { text-align: center; margin-bottom: 30px; }
        .sidebar-header h2 { color: white; font-size: 1.5em; }
        .sidebar-nav ul { list-style: none; padding: 0; }
        .sidebar-nav ul li a { display: block; padding: 10px 20px; color: white; text-decoration: none; transition: background-color 0.3s; }
        .sidebar-nav ul li a:hover, .sidebar-nav ul li a.active { background-color: #34495e; }
        .content { flex-grow: 1; padding: 20px; background-color: #f4f7f6; }
        .main-header { background-color: #ffffff; padding: 15px 20px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .main-header h1 { margin: 0; font-size: 1.8em; color: #333; }
        .user-info { font-size: 1.1em; color: #555; }
        .user-info strong { color: #2c3e50; }
        .main-content-inner { background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }

        /* Estilos para mensagens */
        .success-message { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 10px; margin-bottom: 15px; border-radius: 5px; text-align: center; }
        .error-message { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 10px; margin-bottom: 15px; border-radius: 5px; text-align: center; }
        .help-block { color: #dc3545; font-size: 0.9em; margin-top: 5px; display: block; }

        /* Estilos de formulário */
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group input[type="url"],
        .form-group input[type="date"], /* Adicionado para tipo date */
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box; /* Garante que o padding não aumente a largura total */
            font-size: 1em;
        }
        .btn-submit, .btn-link {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            text-decoration: none; /* Para btn-link */
            display: inline-block; /* Para btn-link */
            text-align: center; /* Para btn-link */
        }
        .btn-submit:hover, .btn-link:hover {
            background-color: #218838;
        }

        /* Estilos para listas de itens (gerenciar_usuarios, gerenciar_cursos) */
        .item-list, .module-grid {
            margin-top: 20px;
        }
        .list-item, .module-card {
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .list-item h3, .module-card h3 {
            margin: 0;
            font-size: 1.2em;
            color: #333;
        }
        .list-item p { /* Adicionado para tipo de usuário */
            margin: 5px 0 0;
            font-size: 0.9em;
            color: #666;
        }
        .actions a, .module-actions a, .aula-actions a {
            margin-left: 10px;
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9em;
        }
        .action-edit { background-color: #007bff; color: white; }
        .action-edit:hover { background-color: #0056b3; }
        .action-delete { background-color: #dc3545; color: white; }
        .action-delete:hover { background-color: #c82333; }
        .action-gerenciar-aulas { background-color: #17a2b8; color: white; }
        .action-gerenciar-aulas:hover { background-color: #138496; }
        .action-add-aula { background-color: #6c757d; color: white; }
        .action-add-aula:hover { background-color: #5a6268; }

        .no-items { text-align: center; color: #666; padding: 20px; border: 1px dashed #eee; border-radius: 8px; margin-top: 20px; }
        .back-link { text-align: center; margin-top: 30px; }
        .back-link a { color: #007bff; text-decoration: none; }
        .back-link a:hover { text-decoration: underline; }

        /* Estilos específicos para module-grid */
        .module-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
        .module-card {
            flex-direction: column; /* Organiza título e ações em coluna */
            align-items: flex-start; /* Alinha o conteúdo à esquerda */
        }
        .module-card h3 {
            margin-bottom: 10px;
        }
        .aula-actions, .module-actions {
            margin-top: 10px;
            width: 100%; /* Ocupa a largura total do card */
            display: flex;
            justify-content: flex-end; /* Alinha os botões à direita */
            flex-wrap: wrap; /* Permite que os botões quebrem linha se necessário */
        }
        .aula-actions a, .module-actions a {
            margin-left: 5px;
            margin-top: 5px; /* Adiciona um pequeno espaçamento vertical entre os botões */
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>Admin Panel</h2>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">Dashboard</a></li>

                    <?php if (isset($_SESSION["tipo_usuario"]) && ($_SESSION["tipo_usuario"] == 'admin' || $_SESSION["tipo_usuario"] == 'professor')): ?>
                    <li><a href="adicionar_curso.php" class="<?php echo ($current_page == 'adicionar_curso.php') ? 'active' : ''; ?>">Adicionar Curso</a></li>
                    <li>
                        <a href="gerenciar_cursos.php" class="<?php
                            echo (in_array($current_page, [
                                'gerenciar_cursos.php', 'adicionar_modulo.php', 'gerenciar_modulos.php',
                                'adicionar_aula.php', 'editar_curso.php', 'excluir_curso.php',
                                'editar_modulo.php', 'excluir_modulo.php', 'gerenciar_aulas.php',
                                'editar_aula.php', 'excluir_aula.php'
                            ])) ? 'active' : '';
                        ?>">Gerenciar Cursos/Módulos/Aulas</a>
                    </li>
                    <?php endif; ?>

                    <?php if (isset($_SESSION["tipo_usuario"]) && $_SESSION["tipo_usuario"] == 'admin'): ?>
                    <li>
                        <a href="gerenciar_usuarios.php" class="<?php
                            echo (in_array($current_page, [
                                'gerenciar_usuarios.php', 'adicionar_usuario.php',
                                'editar_usuario.php', 'excluir_usuario.php'
                            ])) ? 'active' : '';
                        ?>">Gerenciar Usuários</a>
                    </li>
                    <?php endif; ?>

                    <li><a href="../index.php">Ver Site</a></li>
                    <li><a href="../logout.php">Sair</a></li>
                </ul>
            </nav>
        </aside>

        <main class="content">
            <header class="main-header">
                <h1><?php echo htmlspecialchars($page_title); ?></h1>
                <div class="user-info">
                    <span>Bem-vindo, <strong><?php echo htmlspecialchars($_SESSION["nome_completo"] ?? 'Usuário'); ?></strong>! (<?php echo htmlspecialchars($_SESSION["tipo_usuario"] ?? 'N/A'); ?>)</span>
                </div>
            </header>
            <div class="main-content-inner">