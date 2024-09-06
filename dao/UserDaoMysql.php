<?php
// Requer o modelo de User e outras classes DAO relacionadas
require_once 'models/User.php';
require_once 'dao/UserRelationDaoMysql.php';
require_once 'dao/PostDaoMysql.php';

// Classe responsável pelas operações no banco de dados relacionadas ao usuário
class UserDaoMysql implements UserDAO
{
    private $pdo; // Conexão com o banco de dados

    // Construtor que recebe a instância PDO (conexão com o banco)
    public function __construct(PDO $driver)
    {
        $this->pdo = $driver;
    }

    // Função para gerar um objeto User a partir de um array de dados
    private function generateUser($array, $full = false)
    {
        $u = new User();
        $u->id = $array['id'] ?? 0;
        $u->email = $array['email'] ?? '';
        $u->password = $array['password'] ?? '';
        $u->name = $array['name'] ?? '';
        $u->birthdate = $array['birthdate'] ?? '';
        $u->city = $array['city'] ?? '';
        $u->work = $array['work'] ?? '';
        $u->avatar = $array['avatar'] ?? '';
        $u->cover = $array['cover'] ?? '';
        $u->token = $array['token'] ?? '';

        // Se $full for true, busca também as relações de seguidores e posts
        if ($full) {
            $urDaoMysql = new UserRelationDaoMysql($this->pdo);
            $postDaoMysql = new PostDaoMysql($this->pdo);

            // Obter seguidores do usuário
            $u->followers = $urDaoMysql->getFollowers($u->id);
            foreach ($u->followers as $key => $follower_id) {
                $newUser = $this->findById($follower_id);
                $u->followers[$key] = $newUser;
            }

            // Obter quem o usuário está seguindo
            $u->following = $urDaoMysql->getFollowing($u->id);
            foreach ($u->following as $key => $follower_id) {
                $newUser = $this->findById($follower_id);
                $u->following[$key] = $newUser;
            }

            // Obter fotos do usuário
            $u->photos = $postDaoMysql->getPhotosFrom($u->id);
        }

        return $u;
    }

    // Função para encontrar um usuário pelo token
    public function findByToken($token)
    {
        if (!empty($token)) {
            // Prepara a query para buscar o usuário com o token informado
            $sql = $this->pdo->prepare("SELECT * FROM users WHERE token = :token");
            $sql->bindValue(':token', $token);
            $sql->execute();

            // Se encontrar, gera o objeto User
            if ($sql->rowCount() > 0) {
                $data = $sql->fetch(PDO::FETCH_ASSOC);
                $user = $this->generateUser($data);
                return $user;
            }
        }

        return false; // Retorna false se não encontrar o token
    }

    // Função para encontrar um usuário pelo email
    public function findByEmail($email)
    {
        if (!empty($email)) {
            // Prepara a query para buscar o usuário com o email informado
            $sql = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
            $sql->bindValue(':email', $email);
            $sql->execute();

            // Se encontrar, gera o objeto User
            if ($sql->rowCount() > 0) {
                $data = $sql->fetch(PDO::FETCH_ASSOC);
                $user = $this->generateUser($data);
                return $user;
            }
        }

        return false; // Retorna false se não encontrar o email
    }

    // Função para encontrar um usuário pelo ID
    public function findById($id, $full = false)
    {
        if (!empty($id)) {
            // Prepara a query para buscar o usuário com o ID informado
            $sql = $this->pdo->prepare("SELECT * FROM users WHERE id = :id");
            $sql->bindValue(':id', $id);
            $sql->execute();

            // Se encontrar, gera o objeto User (completo ou não, dependendo de $full)
            if ($sql->rowCount() > 0) {
                $data = $sql->fetch(PDO::FETCH_ASSOC);
                $user = $this->generateUser($data, $full);
                return $user;
            }
        }

        return false; // Retorna false se não encontrar o ID
    }

    // Função para buscar usuários pelo nome (busca parcial com LIKE)
    public function findByName($name)
    {
        $array = [];

        if (!empty($name)) {
            // Prepara a query para buscar os usuários cujo nome contenha o valor informado
            $sql = $this->pdo->prepare("SELECT * FROM users WHERE name LIKE :name");
            $sql->bindValue(':name', '%' . $name . '%');
            $sql->execute();

            // Se encontrar, gera objetos User para cada resultado
            if ($sql->rowCount() > 0) {
                $data = $sql->fetchAll(PDO::FETCH_ASSOC);

                foreach ($data as $item) {
                    $array[] = $this->generateUser($item);
                }
            }
        }

        return $array; // Retorna um array de usuários encontrados
    }

    // Função para atualizar as informações de um usuário
    public function update(User $u)
    {
        // Prepara a query de atualização de dados do usuário
        $sql = $this->pdo->prepare("UPDATE users SET 
            email = :email,
            password = :password,
            name = :name,
            birthdate = :birthdate,
            city = :city,
            work = :work,
            avatar = :avatar,
            cover = :cover,
            token = :token
            WHERE id = :id");

        // Atribui os valores aos parâmetros da query
        $sql->bindValue(':email', $u->email);
        $sql->bindValue(':password', $u->password);
        $sql->bindValue(':name', $u->name);
        $sql->bindValue(':birthdate', $u->birthdate);
        $sql->bindValue(':city', $u->city);
        $sql->bindValue(':work', $u->work);
        $sql->bindValue(':avatar', $u->avatar);
        $sql->bindValue(':cover', $u->cover);
        $sql->bindValue(':token', $u->token);
        $sql->bindValue(':id', $u->id);
        $sql->execute(); // Executa a query

        return true;
    }

    // Função para inserir um novo usuário no banco de dados
    public function insert(User $u)
    {
        // Prepara a query de inserção de um novo usuário
        $sql = $this->pdo->prepare("INSERT INTO users (
            email, password, name, birthdate, token
        ) VALUES (
            :email, :password, :name, :birthdate, :token
        )");

        // Atribui os valores aos parâmetros da query
        $sql->bindValue(':email', $u->email);
        $sql->bindValue(':password', $u->password);
        $sql->bindValue(':name', $u->name);
        $sql->bindValue(':birthdate', $u->birthdate);
        $sql->bindValue(':token', $u->token);
        $sql->execute(); // Executa a query

        return true;
    }
}
