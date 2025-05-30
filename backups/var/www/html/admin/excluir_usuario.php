<?php
// admin/excluir_usuario.php
require_once '../db_connect.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$id_usuario = null;
$error_msg = "";
$sucesso_msg = "";

if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $id_usuario_para_excluir = mysqli_real_escape_string($link, $_GET["id"]);

    if (isset($_SESSION["id_usuario"]) && $id_usuario_para_excluir == $_SESSION["id_usuario"]) {
        $error_msg = "Você não pode excluir sua própria conta enquanto estiver logado.";
    } else {
        $sql_delete = "DELETE FROM usuarios WHERE id_usuario = ?";
        if ($stmt_delete = mysqli_prepare($link, $sql_delete)) {
            mysqli_stmt_bind_param($stmt_delete, "i", $param_id_usuario);
            $param_id_usuario = $id_usuario_para_excluir;

            if (mysqli_stmt_execute($stmt_delete)) {
                if (mysqli_stmt_affected_rows($stmt_delete) > 0) {
                    $sucesso_msg = "Usuário excluído com sucesso!";
                } else {
                    $error_msg = "Usuário não encontrado para exclusão.";
                }
            } else {
                $error_msg = "Erro ao excluir usuário: " . mysqli_error($link);
            }
            mysqli_stmt_close($stmt_delete);
        } else {
            $error_msg = "Erro ao preparar query de exclusão de usuário: " . mysqli_error($link);
        }
    }

} else {
    $error_msg = "ID do usuário inválido ou não fornecido.";
}

mysqli_close($link);

if (!empty($sucesso_msg)) {
    header("location: gerenciar_usuarios.php?sucesso_msg=" . urlencode($sucesso_msg));
} elseif (!empty($error_msg)) {
    header("location: gerenciar_usuarios.php?error_msg=" . urlencode($error_msg));
} else {
    header("location: gerenciar_usuarios.php");
}
exit;
?>
