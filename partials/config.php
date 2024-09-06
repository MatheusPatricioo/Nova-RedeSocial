<?php
// Inicia a sessão
session_start();

// Define a base URL do site
$base = 'http://localhost/redesocial2/';

// Configurações do banco de dados
$db_name = 'devsbook-teste';
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';

// Define o tamanho máximo para redimensionamento de imagens
$maxWidth = 800;
$maxHeight = 800;

// Estabelece conexão com o banco de dados via PDO
$pdo = new PDO("mysql:dbname=" . $db_name . ";host=" . $db_host, $db_user, $db_pass);
?>