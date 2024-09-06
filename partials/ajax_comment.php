<?php
// Requisitos de arquivos de configuração e classes necessárias
require_once 'config.php'; // Configurações gerais do sistema
require_once 'models/Auth.php'; // Classe para autenticação de usuário
require_once 'dao/PostCommentDaoMysql.php'; // Classe de interação com banco de dados para comentários

// Instancia o sistema de autenticação e verifica o token do usuário
$auth = new Auth($pdo, $base);
$userInfo = $auth->checkToken(); // Verifica se o usuário está autenticado e retorna as informações dele

// Captura as variáveis enviadas pelo formulário (id do post e texto do comentário)
$id = filter_input(INPUT_POST, 'id');
$txt = filter_input(INPUT_POST, 'txt');

// Inicializa o array de resposta
$array = [];

if ($id && $txt) { // Verifica se o ID do post e o texto do comentário foram fornecidos
    // Instancia o DAO responsável por manipular comentários no banco de dados
    $postCommentDao = new PostCommentDaoMysql($pdo);

    // Cria um novo comentário e preenche seus dados
    $newComment = new PostComment();
    $newComment->id_post = $id; // ID do post que está recebendo o comentário
    $newComment->id_user = $userInfo->id; // ID do usuário que fez o comentário
    $newComment->body = $txt; // Texto do comentário
    $newComment->created_at = date('Y-m-d H:i:s'); // Data e hora do comentário

    // Adiciona o novo comentário no banco de dados
    $postCommentDao->addComment($newComment);

    // Prepara o array de resposta com as informações do comentário e do usuário
    $array = [
        'error' => '', // Nenhum erro
        'link' => $base . '/perfil.php?id=' . $userInfo->id, // Link para o perfil do usuário
        'avatar' => $base . '/media/avatars/' . $userInfo->avatar, // URL do avatar do usuário
        'name' => $userInfo->name, // Nome do usuário
        'body' => $txt // Texto do comentário
    ];
}

// Define o cabeçalho para resposta JSON
header("Content-Type: application/json");

// Converte o array de resposta para JSON e exibe
echo json_encode($array);

// Encerra o script
exit;
?>