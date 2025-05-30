<?php
// admin/index.php
// Lógica PHP existente para proteção e sessão
// Não precisa de session_start() aqui, pois já está no header
// Não precisa de require_once '../db_connect.php'; aqui, pois não usa DB

$page_title = "Dashboard"; // Define o título da página

require_once 'includes/admin_header.php'; // Inclui o cabeçalho
?>

        <p style="text-align: center;">Bem-vindo(a), <?php echo htmlspecialchars($_SESSION["nome"]); ?>! Aqui você pode gerenciar o conteúdo do seu site.</p>

        <p style="text-align: center;">Selecione uma opção no menu lateral para começar a gerenciar.</p>
        <?php
require_once 'includes/admin_footer.php'; // Inclui o rodapé
?>
