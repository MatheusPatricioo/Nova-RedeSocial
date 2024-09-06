<?php
// Classe que representa um usuário
class User
{
    public $id;
    public $email;
    public $password;
    public $name;
    public $birthdate;
    public $city;
    public $work;
    public $avatar;
    public $cover;
    public $token;
}

// Interface para as operações relacionadas a usuários no banco de dados
interface UserDAO
{
    public function findByToken($token);
    public function findByEmail($email);
    public function findById($id);
    public function update(User $u);
    public function insert(User $u);
}
