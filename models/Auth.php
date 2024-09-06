<?php
// Requer o DAO do usuário para operações com o banco de dados
require_once 'dao/UserDaoMysql.php';

// Classe de autenticação para gerenciamento de login e registro
class Auth
{
    private $pdo;   // Conexão com o banco de dados
    private $base;  // Base URL do sistema
    private $dao;   // DAO do usuário

    // Construtor que recebe a instância do PDO e a base do sistema
    public function __construct(PDO $pdo, $base)
    {
        $this->pdo = $pdo;
        $this->base = $base;
        $this->dao = new UserDaoMysql($this->pdo); // Instancia o DAO do usuário
    }

    // Verifica se há um token de usuário na sessão e o valida
    public function checkToken()
    {
        if (!empty($_SESSION['token'])) {
            $token = $_SESSION['token'];
            $user = $this->dao->findByToken($token);

            // Retorna o usuário se o token for válido
            if ($user) {
                return $user;
            }
        }

        // Redireciona para a página de login se o token for inválido
        header("Location: " . $this->base . "/login.php");
        exit;
    }

    // Valida o login do usuário com base no e-mail e senha
    public function validateLogin($email, $password)
    {
        $user = $this->dao->findByEmail($email);

        // Verifica se o usuário existe e se a senha está correta
        if ($user && password_verify($password, $user->password)) {
            $token = md5(time() . rand(0, 9999)); // Gera um novo token

            $_SESSION['token'] = $token;
            $user->token = $token;
            $this->dao->update($user); // Atualiza o token do usuário no banco

            return true;
        }

        return false;
    }

    // Verifica se o e-mail já está registrado no sistema
    public function emailExists($email)
    {
        return $this->dao->findByEmail($email) ? true : false;
    }

    // Registra um novo usuário no sistema
    public function registerUser($name, $email, $password, $birthdate)
    {
        $hash = password_hash($password, PASSWORD_DEFAULT); // Cria o hash da senha
        $token = md5(time() . rand(0, 9999)); // Gera um token

        $newUser = new User();
        $newUser->name = $name;
        $newUser->email = $email;
        $newUser->password = $hash;
        $newUser->birthdate = $birthdate;
        $newUser->token = $token;

        $this->dao->insert($newUser); // Insere o novo usuário no banco

        $_SESSION['token'] = $token; // Armazena o token na sessão
    }
}
