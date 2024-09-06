<?php
// Requer as classes necessárias para manipulação de posts, usuários, relações, likes e comentários
require_once 'models/Post.php';
require_once 'dao/UserRelationDaoMysql.php';
require_once 'dao/UserDaoMysql.php';
require_once 'dao/PostLikeDaoMysql.php';
require_once 'dao/PostCommentDaoMysql.php';

// Classe responsável pela manipulação de posts no banco de dados
class PostDaoMysql implements PostDAO
{
    private $pdo; // Conexão com o banco de dados

    // Construtor que recebe o driver PDO (conexão com o banco)
    public function __construct(PDO $driver)
    {
        $this->pdo = $driver;
    }

    // Função para inserir um novo post no banco de dados
    public function insert(Post $p)
    {
        $sql = $this->pdo->prepare('INSERT INTO posts (
            id_user, type, created_at, body
        ) VALUES (
            :id_user, :type, :created_at, :body
        )');

        // Atribui os valores do post aos parâmetros da query
        $sql->bindValue(':id_user', $p->id_user);
        $sql->bindValue(':type', $p->type);
        $sql->bindValue(':created_at', $p->created_at);
        $sql->bindValue(':body', $p->body);
        $sql->execute(); // Executa a inserção
    }

    // Função para deletar um post e suas interações associadas (likes, comentários, fotos)
    public function delete($id, $id_user)
    {
        $postLikeDao = new PostLikeDaoMysql($this->pdo); // Instancia DAO de likes
        $postCommentDao = new PostCommentDaoMysql($this->pdo); // Instancia DAO de comentários

        // 1. Verifica se o post existe e se pertence ao usuário
        $sql = $this->pdo->prepare("SELECT * FROM posts 
        WHERE id = :id AND id_user = :id_user");
        $sql->bindValue(':id', $id);
        $sql->bindValue(':id_user', $id_user);
        $sql->execute();

        if ($sql->rowCount() > 0) {
            $post = $sql->fetch(PDO::FETCH_ASSOC);

            // 2. Deleta likes e comentários do post
            $postLikeDao->deleteFromPost($id);
            $postCommentDao->deleteFromPost($id);

            // 3. Se o post for uma foto, deleta o arquivo de imagem
            if ($post['type'] === 'photo') {
                $img = 'media/uploads/' . $post['body'];
                if (file_exists($img)) {
                    unlink($img); // Exclui o arquivo da foto
                }
            }

            // 4. Deleta o post
            $sql = $this->pdo->prepare("DELETE FROM posts 
            WHERE id = :id AND id_user = :id_user");
            $sql->bindValue(':id', $id);
            $sql->bindValue(':id_user', $id_user);
            $sql->execute(); // Executa a exclusão do post
        }
    }

    // Função para obter o feed de posts de um usuário específico
    public function getUserFeed($id_user, $page = 1)
    {
        $array = ['feed' => []]; // Inicializa o array de resposta
        $perPage = 4; // Define o número de posts por página

        $offset = ($page - 1) * $perPage;

        // Seleciona os posts do usuário, ordenados por data de criação
        $sql = $this->pdo->prepare("SELECT * FROM posts
        WHERE id_user = :id_user
        ORDER BY created_at DESC LIMIT $offset,$perPage");
        $sql->bindValue(':id_user', $id_user);
        $sql->execute();

        // Se houver posts, transforma-os em objetos
        if ($sql->rowCount() > 0) {
            $data = $sql->fetchAll(PDO::FETCH_ASSOC);
            $array['feed'] = $this->_postListToObject($data, $id_user);
        }

        // Conta o número total de posts do usuário para paginação
        $sql = $this->pdo->prepare("SELECT COUNT(*) as c FROM posts
        WHERE id_user = :id_user");
        $sql->bindValue(':id_user', $id_user);
        $sql->execute();
        $totalData = $sql->fetch();
        $total = $totalData['c'];

        $array['pages'] = ceil($total / $perPage); // Calcula o número de páginas
        $array['currentPage'] = $page; // Define a página atual

        return $array;
    }

    // Função para obter o feed da home (posts de quem o usuário segue)
    public function getHomeFeed($id_user, $page = 1)
    {
        $array = []; // Inicializa o array de resposta
        $perPage = 4; // Define o número de posts por página

        $offset = ($page - 1) * $perPage;

        // 1. Obtém a lista de usuários que o usuário atual segue
        $urDao = new UserRelationDaoMysql($this->pdo);
        $userList = $urDao->getFollowing($id_user);
        $userList[] = $id_user; // Inclui o próprio usuário na lista

        // 2. Seleciona os posts desses usuários, ordenados por data de criação
        $sql = $this->pdo->query("SELECT * FROM posts
        WHERE id_user IN (" . implode(',', $userList) . ")
        ORDER BY created_at DESC, id DESC LIMIT $offset,$perPage");
        if ($sql->rowCount() > 0) {
            $data = $sql->fetchAll(PDO::FETCH_ASSOC);

            // 3. Transforma o resultado em objetos de posts
            $array['feed'] = $this->_postListToObject($data, $id_user);
        }

        // 4. Conta o número total de posts para paginação
        $sql = $this->pdo->query("SELECT COUNT(*) as c FROM posts
        WHERE id_user IN (" . implode(',', $userList) . ")");
        $totalData = $sql->fetch();
        $total = $totalData['c'];

        $array['pages'] = ceil($total / $perPage); // Calcula o número de páginas
        $array['currentPage'] = $page; // Define a página atual

        return $array;
    }

    // Função para obter as fotos de um usuário específico
    public function getPhotosFrom($id_user)
    {
        $array = []; // Inicializa o array de resposta

        // Seleciona os posts do tipo 'photo' do usuário, ordenados por data de criação
        $sql = $this->pdo->prepare("SELECT * FROM posts
        WHERE id_user = :id_user AND type = 'photo'
        ORDER BY created_at DESC");
        $sql->bindValue(':id_user', $id_user);
        $sql->execute();

        // Se houver fotos, transforma-as em objetos
        if ($sql->rowCount() > 0) {
            $data = $sql->fetchAll(PDO::FETCH_ASSOC);
            $array = $this->_postListToObject($data, $id_user);
        }

        return $array;
    }

    // Função privada para transformar uma lista de posts em objetos Post
    private function _postListToObject($post_list, $id_user)
    {
        $posts = []; // Inicializa o array de posts
        $userDao = new UserDaoMysql($this->pdo); // Instancia o DAO de usuários
        $postLikeDao = new PostLikeDaoMysql($this->pdo); // Instancia o DAO de likes
        $postCommentDao = new PostCommentDaoMysql($this->pdo); // Instancia o DAO de comentários

        // Para cada post na lista, cria um objeto Post e preenche seus dados
        foreach ($post_list as $post_item) {
            $newPost = new Post();
            $newPost->id = $post_item['id'];
            $newPost->type = $post_item['type'];
            $newPost->created_at = $post_item['created_at'];
            $newPost->body = $post_item['body'];
            $newPost->mine = false;

            // Verifica se o post é do usuário atual
            if ($post_item['id_user'] == $id_user) {
                $newPost->mine = true;
            }

            // Preenche as informações do usuário que fez o post
            $newPost->user = $userDao->findById($post_item['id_user']);

            // Preenche as informações sobre likes
            $newPost->likeCount = $postLikeDao->getLikeCount($newPost->id);
            $newPost->liked = $postLikeDao->isLiked($newPost->id, $id_user);

            // Preenche as informações sobre comentários
            $newPost->comments = $postCommentDao->getComments($newPost->id);

            // Adiciona o post ao array de retorno
            $posts[] = $newPost;
        }

        return $posts;
    }
}
