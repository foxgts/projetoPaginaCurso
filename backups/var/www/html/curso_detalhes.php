<?php
require_once 'db_connect.php';

// Garante que o ID do curso foi passado via URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_curso = mysqli_real_escape_string($link, $_GET['id']); // Protege contra SQL Injection

    // --- 1. Obter detalhes do curso ---
    $sql_curso = "SELECT id_curso, titulo, descricao, nivel, idioma FROM cursos WHERE id_curso = ?";
    if ($stmt_curso = mysqli_prepare($link, $sql_curso)) {
        mysqli_stmt_bind_param($stmt_curso, "i", $id_curso); // "i" para inteiro
        mysqli_stmt_execute($stmt_curso);
        $result_curso = mysqli_stmt_get_result($stmt_curso);

        if (mysqli_num_rows($result_curso) == 1) {
            $curso = mysqli_fetch_assoc($result_curso);
        } else {
            // Curso não encontrado
            header("location: index.php"); // Redireciona de volta para a página inicial
            exit();
        }
        mysqli_stmt_close($stmt_curso);
    } else {
        die("ERRO: Não foi possível preparar a query para o curso. " . mysqli_error($link));
    }

} else {
    // ID do curso não fornecido na URL, redireciona para a página inicial
    header("location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($curso['titulo']); ?> - Detalhes do Curso</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; margin: 0; padding: 20px; background-color: #f4f4f4; color: #333; }
        .container { max-width: 960px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1, h2, h3 { color: #0056b3; }
        h1 { text-align: center; margin-bottom: 20px; }
        .curso-info { background: #e9e9e9; padding: 15px; border-radius: 5px; margin-bottom: 30px; }
        .curso-info p { font-size: 1.1em; }
        .modulo {
            background: #f8f8f8;
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .modulo h3 { margin-top: 0; color: #333; }
        .aula { margin-left: 20px; padding: 5px 0; border-bottom: 1px dashed #eee; }
        .aula:last-child { border-bottom: none; }
        .aula a { text-decoration: none; color: #007bff; }
        .aula a:hover { text-decoration: underline; }
        .back-link { display: block; text-align: center; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($curso['titulo']); ?></h1>

        <div class="curso-info">
            <p><strong>Nível:</strong> <?php echo htmlspecialchars($curso['nivel']); ?></p>
            <p><strong>Idioma:</strong> <?php echo htmlspecialchars($curso['idioma']); ?></p>
            <p><?php echo nl2br(htmlspecialchars($curso['descricao'])); ?></p>
        </div>

        <h2>Módulos e Aulas</h2>

        <?php
        // --- 2. Obter Módulos e Aulas do Curso ---
        $sql_modulos = "SELECT id_modulo, titulo_modulo, descricao_modulo, ordem FROM modulos WHERE id_curso = ? ORDER BY ordem ASC";
        if ($stmt_modulos = mysqli_prepare($link, $sql_modulos)) {
            mysqli_stmt_bind_param($stmt_modulos, "i", $id_curso);
            mysqli_stmt_execute($stmt_modulos);
            $result_modulos = mysqli_stmt_get_result($stmt_modulos);

            if (mysqli_num_rows($result_modulos) > 0) {
                while ($modulo = mysqli_fetch_assoc($result_modulos)) {
                    echo "<div class='modulo'>";
                    echo "<h3>" . htmlspecialchars($modulo['ordem']) . ". " . htmlspecialchars($modulo['titulo_modulo']) . "</h3>";
                    if (!empty($modulo['descricao_modulo'])) {
                        echo "<p>" . nl2br(htmlspecialchars($modulo['descricao_modulo'])) . "</p>";
                    }

                    // Agora, buscar as aulas para este módulo
                    $sql_aulas = "SELECT id_aula, titulo_aula, ordem FROM aulas WHERE id_modulo = ? ORDER BY ordem ASC";
                    if ($stmt_aulas = mysqli_prepare($link, $sql_aulas)) {
                        mysqli_stmt_bind_param($stmt_aulas, "i", $modulo['id_modulo']);
                        mysqli_stmt_execute($stmt_aulas);
                        $result_aulas = mysqli_stmt_get_result($stmt_aulas);

                        if (mysqli_num_rows($result_aulas) > 0) {
                            echo "<ul>";
                            while ($aula = mysqli_fetch_assoc($result_aulas)) {
                                // Futuramente, este link levará para a página da aula específica
                                echo "<li class='aula'><a href='aula_detalhes.php?id=" . htmlspecialchars($aula['id_aula']) . "'>" . htmlspecialchars($aula['ordem']) . ". " . htmlspecialchars($aula['titulo_aula']) . "</a></li>";
                            }
                            echo "</ul>";
                        } else {
                            echo "<p>Nenhuma aula encontrada para este módulo.</p>";
                        }
                        mysqli_stmt_close($stmt_aulas);
                    } else {
                        echo "<p>ERRO: Não foi possível preparar a query para as aulas. " . mysqli_error($link) . "</p>";
                    }
                    echo "</div>"; // Fecha .modulo
                }
            } else {
                echo "<p>Nenhum módulo encontrado para este curso.</p>";
            }
            mysqli_stmt_close($stmt_modulos);
        } else {
            echo "<p>ERRO: Não foi possível preparar a query para os módulos. " . mysqli_error($link) . "</p>";
        }

        // Fecha a conexão com o banco de dados
        mysqli_close($link);
        ?>

        <p class="back-link"><a href="index.php">← Voltar para a Lista de Cursos</a></p>
    </div>
</body>
</html>
