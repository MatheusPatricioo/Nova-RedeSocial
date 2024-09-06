<?php
// Classe que representa um "like" em um post
class PostLike
{
    public $id;
    public $id_post;
    public $id_user;
    public $created_at;
}

// Interface para as operações relacionadas a "likes" no banco de dados
interface PostLikeDAO
{
    public function getLikeCount($id_post);
    public function isLiked($id_post, $id_user);
    public function likeToggle($id_post, $id_user);
}
