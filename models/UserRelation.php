<?php
// Classe que representa a relação entre usuários (seguidores/seguidos)
class UserRelation
{
    public $id;
    public $user_from;
    public $user_to;
}

// Interface para as operações relacionadas às relações de usuários no banco de dados
interface UserRelationDAO
{
    public function insert(UserRelation $u);
    public function delete(UserRelation $u);
    public function getFollowing($id);
    public function getFollowers($id);
    public function isFollowing($id1, $id2);
}
