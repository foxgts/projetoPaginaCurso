<?php
// Definições de conexão com o banco de dados
define('DB_SERVER', 'localhost'); // Onde o MySQL está rodando
define('DB_USERNAME', 'admin_cursos'); // Seu usuário MySQL criado
define('DB_PASSWORD', '@Cq124578'); // A senha forte que você definiu para admin_cursos
define('DB_NAME', 'site_cursos_db'); // O nome do seu banco de dados

// Tentar conectar ao banco de dados MySQL
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Checar a conexão
if ($link === false) {
    die("ERRO: Não foi possível conectar ao banco de dados. " . mysqli_connect_error());
}
// Opcional: para debug, você pode adicionar uma mensagem de sucesso
// else {
//     echo "Conexão com o banco de dados estabelecida com sucesso!<br>";
// }
?>
