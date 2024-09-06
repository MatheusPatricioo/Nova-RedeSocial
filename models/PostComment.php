<?php
// Classe que representa um comentário em um post
class PostComment
{
    public $id;
    public $id_post;
    public $id_user;
    public $created_at;
    public $body;
}

// Interface para as operações relacionadas a comentários no banco de dados
interface PostCommentDAO
{
    public function getComments($id_post);
    public function addComment(PostComment $pc);
}
