<?php
// Requer os arquivos de configuração e classes necessárias
require_once 'config.php';
require_once 'models/Auth.php';
require_once 'dao/PostDaoMysql.php';

// Cria uma instância de autenticação e verifica o token do usuário
$auth = new Auth($pdo, $base);
$userInfo = $auth->checkToken();

// Array para armazenar erros
$array = ['error' => ''];

// Instancia o DAO para gerenciar posts
$postDao = new PostDaoMysql($pdo);

// Verifica se um arquivo foi enviado e se há conteúdo no arquivo
if (isset($_FILES['photo']) && !empty($_FILES['photo']['tmp_name'])) {
    $photo = $_FILES['photo'];

    // Verifica se o tipo do arquivo é uma imagem JPEG ou PNG
    if (in_array($photo['type'], ['image/jpeg', 'image/jpg', 'image/png'])) {

        // Pega as dimensões originais da imagem
        list($widthOrig, $heightOrig) = getimagesize($photo['tmp_name']);
        $ratio = $widthOrig / $heightOrig;

        // Define novos tamanhos para redimensionar a imagem
        $newWidth = $maxWidth;
        $newHeight = $maxHeight;
        $ratioMax = $maxWidth / $newHeight;

        // Ajusta a largura e altura mantendo a proporção da imagem original
        if ($ratioMax > $ratio) {
            $newWidth = $newHeight * $ratio;
        } else {
            $newHeight = $newWidth / $ratio;
        }

        // Cria uma nova imagem redimensionada
        $finalImage = imagecreatetruecolor($newWidth, $newHeight);
        switch ($photo['type']) {
            case 'image/jpeg':
            case 'image/jpg':
                $image = imagecreatefromjpeg($photo['tmp_name']);
                break;
            case 'image/png':
                $image = imagecreatefrompng($photo['tmp_name']);
                break;
        }

        // Redimensiona a imagem original
        imagecopyresampled(
            $finalImage,
            $image,
            0,
            0,
            0,
            0,
            $newWidth,
            $newHeight,
            $widthOrig,
            $heightOrig
        );

        // Gera um novo nome para a imagem e salva no diretório de uploads
        $photoName = md5(time() . rand(0, 9999)) . '.jpg';
        imagejpeg($finalImage, 'media/uploads/' . $photoName);

        // Cria um novo post com a imagem
        $newPost = new Post();
        $newPost->id_user = $userInfo->id;
        $newPost->type = 'photo';
        $newPost->created_at = date('Y-m-d H:i:s');
        $newPost->body = $photoName;

        // Insere o post no banco de dados
        $postDao->insert($newPost);

    } else {
        // Caso o tipo do arquivo não seja suportado
        $array['error'] = 'Arquivo não suportado (jpg ou png)';
    }

} else {
    // Caso nenhuma imagem tenha sido enviada
    $array['error'] = 'Nenhuma imagem enviada';
}

// Retorna o array como resposta JSON
header("Content-Type: application/json");
echo json_encode($array);
exit;
?>