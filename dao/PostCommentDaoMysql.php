<?php
// Requer as classes necessárias para trabalhar com comentários e usuários
require_once 'models/PostComment.php';
require_once 'dao/UserDaoMysql.php';

// Classe responsável pela manipulação de comentários no banco de dados
class PostCommentDaoMysql implements PostCommentDAO
{
    private $pdo; // Conexão com o banco de dados

    // Construtor que recebe o driver PDO (conexão com o banco)
    public function __construct(PDO $driver)
    {
        $this->pdo = $driver;
    }

    // Função para obter todos os comentários de um post específico
    public function getComments($id_post)
    {
        $array = [];

        // Prepara a consulta SQL para selecionar os comentários do post
        $sql = $this->pdo->prepare("SELECT * FROM postcomments
        WHERE id_post = :id_post");

        // Atribui o ID do post ao parâmetro da query
        $sql->bindValue(':id_post', $id_post);
        $sql->execute();

        // Verifica se existem comentários para o post
        if ($sql->rowCount() > 0) {
            // Obtém todos os comentários do post como um array associativo
            $data = $sql->fetchAll(PDO::FETCH_ASSOC);

            // Instancia o DAO de usuários para buscar informações do autor do comentário
            $userDao = new UserDaoMysql($this->pdo);

            // Para cada comentário, cria um objeto PostComment e o preenche
            foreach ($data as $item) {
                $commentItem = new PostComment();
                $commentItem->id = $item['id']; // ID do comentário
                $commentItem->id_post = $item['id_post']; // ID do post
                $commentItem->id_user = $item['id_user']; // ID do usuário que comentou
                $commentItem->body = $item['body']; // Conteúdo do comentário
                $commentItem->created_at = $item['created_at']; // Data de criação do comentário

                // Preenche o objeto com as informações do autor (usuário)
                $commentItem->user = $userDao->findById($item['id_user']);

                // Adiciona o comentário ao array de retorno
                $array[] = $commentItem;
            }
        }

        // Retorna o array de comentários
        return $array;
    }

    // Função para adicionar um novo comentário ao banco de dados
    public function addComment(PostComment $pc)
    {
        // Prepara a consulta SQL para inserir um novo comentário
        $sql = $this->pdo->prepare("INSERT INTO postcomments
        (id_post, id_user, body, created_at) VALUES
        (:id_post, :id_user, :body, :created_at)");

        // Atribui os valores do comentário aos parâmetros da query
        $sql->bindValue(':id_post', $pc->id_post);
        $sql->bindValue(':id_user', $pc->id_user);
        $sql->bindValue(':body', $pc->body);
        $sql->bindValue(':created_at', $pc->created_at);
        $sql->execute(); // Executa a inserção
    }

    // Função para deletar todos os comentários de um post específico
    public function deleteFromPost($id_post)
    {
        // Prepara a consulta SQL para deletar comentários de um post
        $sql = $this->pdo->prepare("DELETE FROM postcomments WHERE id_post = :id_post");

        // Atribui o ID do post ao parâmetro da query
        $sql->bindValue(':id_post', $id_post);
        $sql->execute(); // Executa a exclusão
    }
}
