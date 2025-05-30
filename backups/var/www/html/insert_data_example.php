<?php
// Inclui o arquivo de conexão com o banco de dados
require_once 'db_connect.php';

// --- Inserindo um usuário de exemplo ---
$nome = "Aluno Teste";
$email = "aluno@example.com";
$senha_pura = "SenhaSegura123!"; // Senha em texto puro, será hashed
$senha_hash = password_hash($senha_pura, PASSWORD_DEFAULT); // Hash da senha para segurança

$sql_usuario = "INSERT INTO usuarios (nome_completo, email, senha_hash) VALUES (?, ?, ?)";

if ($stmt = mysqli_prepare($link, $sql_usuario)) {
    mysqli_stmt_bind_param($stmt, "sss", $nome, $email, $senha_hash);
    if (mysqli_stmt_execute($stmt)) {
        echo "Usuário '$nome' inserido com sucesso.<br>";
    } else {
        echo "ERRO: Não foi possível inserir o usuário. " . mysqli_error($link) . "<br>";
    }
    mysqli_stmt_close($stmt);
} else {
    echo "ERRO: Não foi possível preparar a query para o usuário. " . mysqli_error($link) . "<br>";
}

// --- Inserindo um curso de exemplo ---
$titulo_curso = "Introdução à Programação com Python";
$descricao_curso = "Aprenda os fundamentos da programação usando Python.";
$nivel_curso = "Iniciante";
$idioma_curso = "Português";
$imagem_url_curso = "http://seusite.com/imagens/python.jpg"; // Substitua por uma URL real depois
$ativo_curso = 1; // 1 para ativo, 0 para inativo

$sql_curso = "INSERT INTO cursos (titulo, descricao, nivel, idioma, imagem_url, ativo) VALUES (?, ?, ?, ?, ?, ?)";

if ($stmt = mysqli_prepare($link, $sql_curso)) {
    mysqli_stmt_bind_param($stmt, "sssssi", $titulo_curso, $descricao_curso, $nivel_curso, $idioma_curso, $imagem_url_curso, $ativo_curso);
    if (mysqli_stmt_execute($stmt)) {
        echo "Curso '$titulo_curso' inserido com sucesso.<br>";
    } else {
        echo "ERRO: Não foi possível inserir o curso. " . mysqli_error($link) . "<br>";
    }
    mysqli_stmt_close($stmt);
} else {
    echo "ERRO: Não foi possível preparar a query para o curso. " . mysqli_error($link) . "<br>";
}

// Fecha a conexão com o banco de dados
mysqli_close($link);
?>
