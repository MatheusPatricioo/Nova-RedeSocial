<?php
// Requer a classe PostLike, que representa o like em um post
require_once 'models/PostLike.php';

// Classe responsável pela manipulação de likes no banco de dados
class PostLikeDaoMysql implements PostLikeDAO
{
    private $pdo; // Conexão com o banco de dados

    // Construtor que recebe o driver PDO (conexão com o banco)
    public function __construct(PDO $driver)
    {
        $this->pdo = $driver;
    }

    // Função para contar o número de likes em um post específico
    public function getLikeCount($id_post)
    {
        // Prepara a query para contar os likes no post
        $sql = $this->pdo->prepare("SELECT COUNT(*) as c FROM postlikes
        WHERE id_post = :id_post");

        // Atribui o valor do ID do post ao parâmetro da query
        $sql->bindValue(':id_post', $id_post);
        $sql->execute(); // Executa a query

        // Retorna a contagem de likes
        $data = $sql->fetch();
        return $data['c'];
    }

    // Função para verificar se um usuário já curtiu um post
    public function isLiked($id_post, $id_user)
    {
        // Prepara a query para verificar se o like existe
        $sql = $this->pdo->prepare("SELECT * FROM postlikes
        WHERE id_post = :id_post AND id_user = :id_user");

        // Atribui os valores do ID do post e do usuário aos parâmetros da query
        $sql->bindValue(':id_post', $id_post);
        $sql->bindValue(':id_user', $id_user);
        $sql->execute(); // Executa a query

        // Se houver um like registrado, retorna true, caso contrário, false
        if ($sql->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    // Função para alternar entre curtir e descurtir (toggle) um post
    public function likeToggle($id_post, $id_user)
    {
        // Verifica se o usuário já curtiu o post
        if ($this->isLiked($id_post, $id_user)) {
            // Se já curtiu, deleta o like (descurtir)
            $sql = $this->pdo->prepare("DELETE FROM postlikes
            WHERE id_post = :id_post AND id_user = :id_user");
        } else {
            // Se não curtiu, insere um novo like (curtir)
            $sql = $this->pdo->prepare("INSERT INTO postlikes
            (id_post, id_user, created_at) VALUES 
            (:id_post, :id_user, NOW())");
        }

        // Atribui os valores do ID do post e do usuário aos parâmetros da query
        $sql->bindValue(':id_post', $id_post);
        $sql->bindValue(':id_user', $id_user);
        $sql->execute(); // Executa a query
    }

    // Função para deletar todos os likes de um post específico
    public function deleteFromPost($id_post)
    {
        // Prepara a query para deletar os likes do post
        $sql = $this->pdo->prepare("DELETE FROM postlikes WHERE id_post = :id_post");
        $sql->bindValue(':id_post', $id_post);
        $sql->execute(); // Executa a query
    }
}
