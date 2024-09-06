<?php
// Classe que representa um post
class Post
{
    public $id;
    public $id_user;
    public $type; // Tipo de post: texto ou foto
    public $created_at;
    public $body;
}

// Interface para as operações relacionadas a posts no banco de dados
interface PostDAO
{
    public function insert(Post $p);
    public function delete($id, $id_user);
    public function getUserFeed($id_user);
    public function getHomeFeed($id_user);
    public function getPhotosFrom($id_user);
}
