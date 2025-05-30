<?php
// Inicia a sessão PHP
session_start();

// Verifica se o usuário está logado. Se NÃO estiver, redireciona para a página de login.
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Inclui o arquivo de conexão com o banco de dados (este já deve estar abaixo do session_start)
require_once 'db_connect.php';

$aula = null; // Variável para armazenar os dados da aula
$curso_id = null; // Para link de volta ao curso
$curso_titulo = null; // Para link de volta ao curso
$modulo_id = null; // Para link de volta ao módulo (se for o caso)
$modulo_titulo = null; // Para link de volta ao módulo (se for o caso)

// ... (o restante do código do aula_detalhes.php permanece o mesmo) ...
// Garante que o ID da aula foi passado via URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_aula = mysqli_real_escape_string($link, $_GET['id']);

    // --- 1. Obter detalhes da aula ---
    // Juntamos tabelas para pegar informações do módulo e do curso para navegação
    $sql_aula = "SELECT
                    a.id_aula,
                    a.titulo_aula,
                    a.conteudo,
                    a.tipo_conteudo,
                    a.url_video,
                    m.id_modulo,
                    m.titulo_modulo,
                    c.id_curso,
                    c.titulo AS titulo_curso
                 FROM
                    aulas a
                 JOIN
                    modulos m ON a.id_modulo = m.id_modulo
                 JOIN
                    cursos c ON m.id_curso = c.id_curso
                 WHERE
                    a.id_aula = ?";

    if ($stmt_aula = mysqli_prepare($link, $sql_aula)) {
        mysqli_stmt_bind_param($stmt_aula, "i", $id_aula);
        mysqli_stmt_execute($stmt_aula);
        $result_aula = mysqli_stmt_get_result($stmt_aula);

        if (mysqli_num_rows($result_aula) == 1) {
            $aula = mysqli_fetch_assoc($result_aula);
            $curso_id = $aula['id_curso'];
            $curso_titulo = $aula['titulo_curso'];
            $modulo_id = $aula['id_modulo'];
            $modulo_titulo = $aula['titulo_modulo'];
        } else {
            // Aula não encontrada
            header("location: index.php"); // Redireciona para a página inicial
            exit();
        }
        mysqli_stmt_close($stmt_aula);
    } else {
        die("ERRO: Não foi possível preparar a query para a aula. " . mysqli_error($link));
    }

} else {
    // ID da aula não fornecido na URL, redireciona para a página inicial
    header("location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($aula['titulo_aula']); ?> - Aula</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; margin: 0; padding: 20px; background-color: #f4f4f4; color: #333; }
        .container { max-width: 960px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #0056b3; text-align: center; margin-bottom: 20px; }
        .conteudo-aula {
            background: #e9e9e9;
            padding: 20px;
            border-radius: 5px;
            min-height: 200px; /* Para melhor visualização */
            margin-bottom: 20px;
            border: 1px solid #ddd;
        }
        .navegacao-aula {
            text-align: center;
            margin-top: 30px;
            padding: 10px;
            background-color: #f0f0f0;
            border-radius: 5px;
        }
        .navegacao-aula a {
            text-decoration: none;
            color: #007bff;
            margin: 0 10px;
        }
        .navegacao-aula a:hover { text-decoration: underline; }
        .breadcrumbs {
            font-size: 0.9em;
            margin-bottom: 20px;
            color: #666;
        }
        .breadcrumbs a {
            color: #0056b3;
            text-decoration: none;
        }
        .breadcrumbs a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="breadcrumbs">
            <a href="index.php">Cursos</a> &gt;
            <?php if ($curso_id && $curso_titulo): ?>
                <a href="curso_detalhes.php?id=<?php echo htmlspecialchars($curso_id); ?>"><?php echo htmlspecialchars($curso_titulo); ?></a> &gt;
            <?php endif; ?>
            <?php if ($modulo_id && $modulo_titulo): ?>
                <?php echo htmlspecialchars($modulo_titulo); ?> &gt;
            <?php endif; ?>
            <?php echo htmlspecialchars($aula['titulo_aula']); ?>
        </div>

        <h1><?php echo htmlspecialchars($aula['titulo_aula']); ?></h1>

        <div class="conteudo-aula">
            <?php
            // Verifica o tipo de conteúdo e exibe apropriadamente
            if ($aula['tipo_conteudo'] == 'video' && !empty($aula['url_video'])) {
                // Exemplo: se for um vídeo do YouTube
                // Você precisaria de um iframe:
                // echo '<iframe width="560" height="315" src="' . htmlspecialchars($aula['url_video']) . '" frameborder="0" allowfullscreen></iframe>';
                echo "<p>Conteúdo de vídeo disponível em: <a href='" . htmlspecialchars($aula['url_video']) . "' target='_blank'>" . htmlspecialchars($aula['url_video']) . "</a></p>";
            } else {
                // Por padrão, exibe como texto com quebras de linha
                echo nl2br(htmlspecialchars($aula['conteudo']));
            }
            ?>
        </div>

        <div class="navegacao-aula">
            <?php if ($curso_id): ?>
                <a href="curso_detalhes.php?id=<?php echo htmlspecialchars($curso_id); ?>">← Voltar para o Curso</a>
            <?php endif; ?>
            </div>

        <?php mysqli_close($link); ?>
    </div>
</body>
</html>
