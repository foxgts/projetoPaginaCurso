<?php
// admin/gerenciar_usuarios.php
require_once '../db_connect.php';

// Inclui o cabeçalho do administrador
require_once 'includes/admin_header.php';

// Somente administradores podem gerenciar usuários
if ($_SESSION["tipo_usuario"] !== 'admin') {
    header("location: index.php?error=acesso_nao_autorizado");
    exit;
}

$usuarios = [];
$error_msg = "";
$sucesso_msg = "";

// Verifica se há mensagens de sucesso/erro vindas de outras páginas (ex: após adição/edição/exclusão)
if (isset($_GET['sucesso_msg'])) {
    $sucesso_msg = htmlspecialchars($_GET['sucesso_msg']);
}
if (isset($_GET['error_msg'])) {
    $error_msg = htmlspecialchars($_GET['error_msg']);
}

// Busca todos os usuários, incluindo o tipo_usuario
$sql_usuarios = "SELECT id_usuario, nome_completo, email, tipo_usuario, data_registro FROM usuarios ORDER BY data_registro DESC";
if ($stmt_usuarios = mysqli_prepare($link, $sql_usuarios)) {
    if (mysqli_stmt_execute($stmt_usuarios)) {
        $result_usuarios = mysqli_stmt_get_result($stmt_usuarios);

        if (mysqli_num_rows($result_usuarios) > 0) {
            while ($row = mysqli_fetch_assoc($result_usuarios)) {
                $usuarios[] = $row;
            }
            mysqli_free_result($result_usuarios);
        }
    } else {
        $error_msg = "Erro ao executar a query de busca de usuários: " . mysqli_error($link);
    }
    mysqli_stmt_close($stmt_usuarios);
} else {
    $error_msg = "Erro ao preparar a query de busca de usuários: " . mysqli_error($link);
}

mysqli_close($link);

$page_title = "Gerenciar Usuários"; // Define o título da página
?>

        <h2><?php echo htmlspecialchars($page_title); ?></h2>

        <?php if (!empty($sucesso_msg)): ?>
            <div class="success-message"><?php echo $sucesso_msg; ?></div>
        <?php endif; ?>
        <?php if (!empty($error_msg)): ?>
            <div class="error-message"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <p style="margin-bottom: 20px; text-align: center;">
            <a href="adicionar_usuario.php" class="btn-link">Adicionar Novo Usuário</a>
        </p>

        <div class="item-list">
        <?php if (!empty($usuarios)): ?>
            <?php foreach ($usuarios as $usuario): ?>
                <div class="list-item">
                    <div>
                        <h3><?php echo htmlspecialchars($usuario['nome_completo']); ?></h3>
                        <p><?php echo htmlspecialchars($usuario['email']); ?> (Tipo: <strong><?php echo htmlspecialchars(ucfirst($usuario['tipo_usuario'])); ?></strong>)</p>
                    </div>
                    <div class="actions">
                        <a href="editar_usuario.php?id=<?php echo htmlspecialchars($usuario['id_usuario']); ?>" class="action-edit">Editar</a>
                        <a href="excluir_usuario.php?id=<?php echo htmlspecialchars($usuario['id_usuario']); ?>" class="action-delete" onclick="return confirm('Tem certeza que deseja excluir este usuário? Esta ação é irreversível!');">Excluir</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-items">Nenhum usuário encontrado. <a href="adicionar_usuario.php">Adicione um novo usuário.</a></p>
        <?php endif; ?>
        </div>

        <p class="back-link"><a href="index.php">? Voltar para o Dashboard</a></p>

<?php
require_once 'includes/admin_footer.php'; // Inclui o rodapé
?>