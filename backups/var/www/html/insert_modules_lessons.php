<?php
require_once 'db_connect.php';

// ID do curso ao qual os módulos e aulas serão vinculados.
// VERIFIQUE ESTE ID! Se seu curso de Python tiver um ID diferente de 1, mude aqui.
$id_curso_python = 1;

// --- Inserindo Módulo 1: Fundamentos de Python ---
$titulo_modulo1 = "Fundamentos de Python";
$descricao_modulo1 = "Os conceitos essenciais para começar a programar em Python.";
$ordem_modulo1 = 1;

$sql_modulo1 = "INSERT INTO modulos (id_curso, titulo_modulo, descricao_modulo, ordem) VALUES (?, ?, ?, ?)";
if ($stmt_modulo1 = mysqli_prepare($link, $sql_modulo1)) {
    mysqli_stmt_bind_param($stmt_modulo1, "issi", $id_curso_python, $titulo_modulo1, $descricao_modulo1, $ordem_modulo1);
    if (mysqli_stmt_execute($stmt_modulo1)) {
        $id_modulo1 = mysqli_stmt_insert_id($stmt_modulo1); // Pega o ID do módulo recém-inserido
        echo "Módulo '$titulo_modulo1' inserido com sucesso (ID: $id_modulo1).<br>";

        // --- Aulas para o Módulo 1 ---
        $aulas_modulo1 = [
            ["Introdução e Instalação", "Configuração do ambiente de desenvolvimento Python.", "texto", null, 1],
            ["Variáveis e Tipos de Dados", "Entendendo os diferentes tipos de dados em Python.", "texto", null, 2],
            ["Operadores e Expressões", "Como usar operadores aritméticos, lógicos e de comparação.", "texto", null, 3],
            ["Estruturas Condicionais (if/else)", "Tomada de decisões no código.", "texto", null, 4]
        ];

        $sql_aula = "INSERT INTO aulas (id_modulo, titulo_aula, conteudo, tipo_conteudo, url_video, ordem) VALUES (?, ?, ?, ?, ?, ?)";
        foreach ($aulas_modulo1 as $aula_data) {
            if ($stmt_aula = mysqli_prepare($link, $sql_aula)) {
                mysqli_stmt_bind_param($stmt_aula, "issssi", $id_modulo1, $aula_data[0], $aula_data[1], $aula_data[2], $aula_data[3], $aula_data[4]);
                if (mysqli_stmt_execute($stmt_aula)) {
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;- Aula '" . $aula_data[0] . "' inserida.<br>";
                } else {
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;ERRO: Não foi possível inserir a aula '" . $aula_data[0] . "'. " . mysqli_error($link) . "<br>";
                }
                mysqli_stmt_close($stmt_aula);
            } else {
                echo "&nbsp;&nbsp;&nbsp;&nbsp;ERRO: Não foi possível preparar a query para a aula '" . $aula_data[0] . "'. " . mysqli_error($link) . "<br>";
            }
        }

    } else {
        echo "ERRO: Não foi possível inserir o módulo '$titulo_modulo1'. " . mysqli_error($link) . "<br>";
    }
    mysqli_stmt_close($stmt_modulo1);
} else {
    echo "ERRO: Não foi possível preparar a query para o módulo '$titulo_modulo1'. " . mysqli_error($link) . "<br>";
}


// --- Inserindo Módulo 2: Estruturas de Dados e Controle de Fluxo ---
$titulo_modulo2 = "Estruturas de Dados e Controle de Fluxo";
$descricao_modulo2 = "Listas, tuplas, dicionários e laços de repetição.";
$ordem_modulo2 = 2;

$sql_modulo2 = "INSERT INTO modulos (id_curso, titulo_modulo, descricao_modulo, ordem) VALUES (?, ?, ?, ?)";
if ($stmt_modulo2 = mysqli_prepare($link, $sql_modulo2)) {
    mysqli_stmt_bind_param($stmt_modulo2, "issi", $id_curso_python, $titulo_modulo2, $descricao_modulo2, $ordem_modulo2);
    if (mysqli_stmt_execute($stmt_modulo2)) {
        $id_modulo2 = mysqli_stmt_insert_id($stmt_modulo2);
        echo "Módulo '$titulo_modulo2' inserido com sucesso (ID: $id_modulo2).<br>";

        // --- Aulas para o Módulo 2 ---
        $aulas_modulo2 = [
            ["Listas em Python", "Manipulação de listas e seus métodos.", "texto", null, 1],
            ["Tuplas e Dicionários", "Estruturas de dados imutáveis e mapeamentos.", "texto", null, 2],
            ["Laços de Repetição (for e while)", "Automação de tarefas com loops.", "texto", null, 3],
            ["Funções em Python", "Organizando o código com funções.", "texto", null, 4]
        ];

        foreach ($aulas_modulo2 as $aula_data) {
            if ($stmt_aula = mysqli_prepare($link, $sql_aula)) { // Reutiliza a query de aula
                mysqli_stmt_bind_param($stmt_aula, "issssi", $id_modulo2, $aula_data[0], $aula_data[1], $aula_data[2], $aula_data[3], $aula_data[4]);
                if (mysqli_stmt_execute($stmt_aula)) {
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;- Aula '" . $aula_data[0] . "' inserida.<br>";
                } else {
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;ERRO: Não foi possível inserir a aula '" . $aula_data[0] . "'. " . mysqli_error($link) . "<br>";
                }
                mysqli_stmt_close($stmt_aula);
            } else {
                echo "&nbsp;&nbsp;&nbsp;&nbsp;ERRO: Não foi possível preparar a query para a aula '" . $aula_data[0] . "'. " . mysqli_error($link) . "<br>";
            }
        }

    } else {
        echo "ERRO: Não foi possível inserir o módulo '$titulo_modulo2'. " . mysqli_error($link) . "<br>";
    }
    mysqli_stmt_close($stmt_modulo2);
} else {
    echo "ERRO: Não foi possível preparar a query para o módulo '$titulo_modulo2'. " . mysqli_error($link) . "<br>";
}

// Fecha a conexão com o banco de dados
mysqli_close($link);
?>
