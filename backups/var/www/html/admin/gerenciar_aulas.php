<?php
// admin/gerenciar_aulas.php
require_once '../db_connect.php';

$id_modulo = null;
$modulo_titulo = "Módulo Desconhecido";
$curso_titulo = "Curso Desconhecido";
$id_curso_associado = null; // Para o link de volta ao curso

$aulas = []; // Array para armazenar as aulas
$error_msg = "";

// Pega o ID do módulo da URL
if (isset($_GET['id_modulo']) && !empty(trim($_GET['id_modulo']))) {
    $id_modulo = mysqli_real_escape_string($link, $_GET['id_modulo']);

    // Busca o título do módulo e do curso associado
    $sql_modulo_info = "SELECT m.titulo_modulo, c.titulo AS curso_titulo, c.id_curso
                        FROM modulos m
                        JOIN cursos c ON m.id_curso = c.id_curso
                        WHERE m.id_modulo = ?";
    if ($stmt_modulo_info = mysqli_prepare($link, $sql_modulo_info)) {
        mysqli_stmt_bind_param($stmt_modulo_info, "i", $id_modulo);
        mysqli_stmt_execute($stmt_modulo_info);
        mysqli_stmt_bind_result($stmt_modulo_info, $titulo_mod_found, $titulo_curso_found, $id_curso_found);
        if (mysqli_stmt_fetch($stmt_modulo_info)) {
            $modulo_titulo = $titulo_mod_found;
            $curso_titulo = $titulo_curso_found;
            $id_curso_associado = $id_curso_found;
        } else {
            $error_msg = "Módulo não encontrado.";
        }
        mysqli_stmt_close($stmt_modulo_info);
    } else {
        $error_msg = "Erro ao buscar informações do módulo.";
    }

    // Se o módulo foi encontrado, busca as aulas
    if (empty($error_msg)) {
        $sql_aulas = "SELECT id_aula, titulo_aula FROM aulas WHERE id_modulo = ? ORDER BY ordem ASC";
        if ($stmt_aulas = mysqli_prepare($link, $sql_aulas)) {
            mysqli_stmt_bind_param($stmt_aulas, "i", $id_modulo);
            mysqli_stmt_execute($stmt_aulas);
            $result_aulas = mysqli_stmt_get_result($stmt_aulas);

            if (mysqli_num_rows($result_aulas) > 0) {
                while ($row = mysqli_fetch_assoc($result_aulas)) {
                    $aulas[] = $row;
                }
                mysqli_free_result($result_aulas);
            }
            mysqli_stmt_close($stmt_aulas);
        } else {
            $error_msg = "Erro ao preparar query das aulas.";
        }
    }
} else {
    header("location: gerenciar_cursos.php"); // Redireciona se não houver ID de módulo
    exit;
}

mysqli_close($link);

$page_title = "Gerenciar Aulas de " . htmlspecialchars($modulo_titulo); // Define o título da página

require_once 'includes/admin_header.php'; // Inclui o cabeçalho
?>

        <?php if (!empty($error_msg)): ?>
            <div class="error-message"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <p style="margin-bottom: 20px;">
            <a href="adicionar_aula.php?id_modulo=<?php echo htmlspecialchars($id_modulo); ?>" class="btn-link">Adicionar Nova Aula a este Módulo</a>
        </p>

        <div class="item-list">
        <?php if (!empty($aulas)): ?>
            <?php foreach ($aulas as $aula): ?>
                <div class="list-item">
                    <h3><?php echo htmlspecialchars($aula['titulo_aula']); ?></h3>
                    <div class="actions">
                        <a href="editar_aula.php?id_aula=<?php echo htmlspecialchars($aula['id_aula']); ?>" class="action-edit">Editar</a>
                        <a href="excluir_aula.php?id_aula=<?php echo htmlspecialchars($aula['id_aula']); ?>" class="action-delete" onclick="return confirm('Tem certeza que deseja excluir esta aula? Esta ação é irreversível!');">Excluir</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-items">Nenhuma aula encontrada para este módulo. <a href="adicionar_aula.php?id_modulo=<?php echo htmlspecialchars($id_modulo); ?>">Adicione uma nova aula.</a></p>
        <?php endif; ?>
        </div>

        <p class="back-link">
            <?php if (!empty($id_curso_associado)): ?>
                <a href="gerenciar_modulos.php?id_curso=<?php echo htmlspecialchars($id_curso_associado); ?>">← Voltar para Módulos de "<?php echo htmlspecialchars($curso_titulo); ?>"</a>
            <?php else: ?>
                <a href="gerenciar_cursos.php">← Voltar para Gerenciar Cursos</a>
            <?php endif; ?>
        </p>

<?php
require_once 'includes/admin_footer.php'; // Inclui o rodapé
?>
