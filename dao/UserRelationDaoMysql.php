<?php
// Requer o modelo de UserRelation
require_once 'models/UserRelation.php';

// Classe responsável pelas operações no banco de dados relacionadas às relações entre usuários (seguidores e seguidos)
class UserRelationDaoMysql implements UserRelationDAO
{
    private $pdo; // Conexão com o banco de dados

    // Construtor que recebe a instância PDO (conexão com o banco)
    public function __construct(PDO $driver)
    {
        $this->pdo = $driver;
    }

    // Função para inserir uma nova relação de seguidores no banco de dados
    public function insert(UserRelation $u)
    {
        // Prepara a query para inserir uma nova relação de usuário
        $sql = $this->pdo->prepare("INSERT INTO userrelations
        (user_from, user_to) VALUES 
        (:user_from, :user_to)");

        // Atribui os valores aos parâmetros da query
        $sql->bindValue(':user_from', $u->user_from);
        $sql->bindValue(':user_to', $u->user_to);
        $sql->execute(); // Executa a query
    }

    // Função para deletar uma relação de seguidores
    public function delete(UserRelation $u)
    {
        // Prepara a query para deletar a relação entre dois usuários
        $sql = $this->pdo->prepare("DELETE FROM userrelations
        WHERE user_from = :user_from AND user_to = :user_to");

        // Atribui os valores aos parâmetros da query
        $sql->bindValue(':user_from', $u->user_from);
        $sql->bindValue(':user_to', $u->user_to);
        $sql->execute(); // Executa a query
    }

    // Função para obter a lista de usuários que um usuário específico está seguindo
    public function getFollowing($id)
    {
        $users = []; // Array que irá armazenar os IDs dos usuários seguidos
        // Prepara a query para buscar os IDs de quem o usuário está seguindo
        $sql = $this->pdo->prepare("SELECT user_to FROM userrelations
        WHERE user_from = :user_from");

        $sql->bindValue(':user_from', $id); // Atribui o valor do ID do usuário
        $sql->execute(); // Executa a query

        // Se houver resultados, preenche o array com os IDs dos usuários seguidos
        if ($sql->rowCount() > 0) {
            $data = $sql->fetchAll();
            foreach ($data as $item) {
                $users[] = $item['user_to']; // Adiciona cada ID ao array
            }
        }

        return $users; // Retorna o array com os usuários seguidos
    }

    // Função para obter a lista de seguidores de um usuário específico
    public function getFollowers($id)
    {
        $users = []; // Array que irá armazenar os IDs dos seguidores
        // Prepara a query para buscar os IDs dos seguidores do usuário
        $sql = $this->pdo->prepare("SELECT user_from FROM userrelations
        WHERE user_to = :user_to");

        $sql->bindValue(':user_to', $id); // Atribui o valor do ID do usuário
        $sql->execute(); // Executa a query

        // Se houver resultados, preenche o array com os IDs dos seguidores
        if ($sql->rowCount() > 0) {
            $data = $sql->fetchAll();
            foreach ($data as $item) {
                $users[] = $item['user_from']; // Adiciona cada ID ao array
            }
        }

        return $users; // Retorna o array com os seguidores
    }

    // Função para verificar se um usuário está seguindo outro
    public function isFollowing($id1, $id2)
    {
        // Prepara a query para verificar a relação entre dois usuários
        $sql = $this->pdo->prepare("SELECT * FROM userrelations WHERE
        user_from = :user_from AND user_to = :user_to");

        // Atribui os valores dos IDs dos dois usuários
        $sql->bindValue(':user_from', $id1);
        $sql->bindValue(':user_to', $id2);
        $sql->execute(); // Executa a query

        // Retorna true se a relação existir, senão retorna false
        if ($sql->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }
}
