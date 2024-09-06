<?php
// Requer os arquivos de configuração e classes necessárias
require_once 'config.php';
require_once 'models/Auth.php';
require_once 'dao/PostLikeDaoMysql.php';

// Cria uma instância de autenticação e verifica o token do usuário
$auth = new Auth($pdo, $base);
$userInfo = $auth->checkToken();

// Captura o ID do post via GET
$id = filter_input(INPUT_GET, 'id');

// Verifica se o ID foi informado
if (!empty($id)) {
    // Cria uma instância do PostLikeDao para gerenciar curtidas
    $postLikeDao = new PostLikeDaoMysql($pdo);

    // Alterna entre curtir e descurtir o post baseado no ID do post e do usuário
    $postLikeDao->likeToggle($id, $userInfo->id);
}
