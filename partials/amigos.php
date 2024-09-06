<?php
// Requer os arquivos de configuração e classes necessárias
require_once 'config.php';
require_once 'models/Auth.php';
require_once 'dao/PostDaoMysql.php';

// Cria uma instância de autenticação e verifica o token do usuário
$auth = new Auth($pdo, $base);
$userInfo = $auth->checkToken();

// Define a aba ativa e inicializa variáveis
$activeMenu = 'friends';
$user = [];
$feed = [];

// Captura o ID do usuário via GET, ou define o ID do usuário logado
$id = filter_input(INPUT_GET, 'id');
if (!$id) {
    $id = $userInfo->id;
}

// Se o ID informado for diferente do ID do usuário logado, desativa a aba
if ($id != $userInfo->id) {
    $activeMenu = '';
}

// Instancia DAOs para gerenciar posts e usuários
$postDao = new PostDaoMysql($pdo);
$userDao = new UserDaoMysql($pdo);

// Busca informações do usuário pelo ID
$user = $userDao->findById($id, true);
if (!$user) {
    // Redireciona para a página inicial se o usuário não for encontrado
    header("Location: " . $base);
    exit;
}

// Calcula a idade do usuário
$dateFrom = new DateTime($user->birthdate);
$dateTo = new DateTime('today');
$user->ageYears = $dateFrom->diff($dateTo)->y;

// Inclui os arquivos de cabeçalho e menu
require 'partials/header.php';
require 'partials/menu.php';
?>

<!-- Início da seção de feed -->
<section class="feed">

    <div class="row">
        <div class="box flex-1 border-top-flat">
            <div class="box-body">
                <!-- Exibe a capa e as informações do perfil do usuário -->
                <div class="profile-cover"
                    style="background-image: url('<?= $base; ?>/media/covers/<?= $user->cover; ?>');"></div>
                <div class="profile-info m-20 row">
                    <div class="profile-info-avatar">
                        <img src="<?= $base; ?>/media/avatars/<?= $user->avatar; ?>" />
                    </div>
                    <div class="profile-info-name">
                        <div class="profile-info-name-text"><?= $user->name; ?></div>
                        <?php if (!empty($user->city)): ?>
                            <div class="profile-info-location"><?= $user->city; ?></div>
                        <?php endif; ?>
                    </div>
                    <!-- Exibe os dados de seguidores, seguindo e fotos -->
                    <div class="profile-info-data row">
                        <div class="profile-info-item m-width-20">
                            <div class="profile-info-item-n"><?= count($user->followers); ?></div>
                            <div class="profile-info-item-s">Seguidores</div>
                        </div>
                        <div class="profile-info-item m-width-20">
                            <div class="profile-info-item-n"><?= count($user->following); ?></div>
                            <div class="profile-info-item-s">Seguindo</div>
                        </div>
                        <div class="profile-info-item m-width-20">
                            <div class="profile-info-item-n"><?= count($user->photos); ?></div>
                            <div class="profile-info-item-s">Fotos</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Exibe seguidores e quem o usuário está seguindo -->
    <div class="row">

        <div class="column">

            <div class="box">
                <div class="box-body">

                    <!-- Tabs para alternar entre seguidores e seguindo -->
                    <div class="tabs">
                        <div class="tab-item" data-for="followers">
                            Seguidores
                        </div>
                        <div class="tab-item active" data-for="following">
                            Seguindo
                        </div>
                    </div>
                    <div class="tab-content">
                        <div class="tab-body" data-item="followers">

                            <div class="full-friend-list">

                                <?php foreach ($user->followers as $item): ?>
                                    <div class="friend-icon">
                                        <a href="<?= $base; ?>/perfil.php?id=<?= $item->id; ?>">
                                            <div class="friend-icon-avatar">
                                                <img src="<?= $base; ?>/media/avatars/<?= $item->avatar; ?>" />
                                            </div>
                                            <div class="friend-icon-name">
                                                <?= $item->name; ?>
                                            </div>
                                        </a>
                                    </div>
                                <?php endforeach; ?>

                            </div>

                        </div>
                        <div class="tab-body" data-item="following">

                            <div class="full-friend-list">
                                <?php foreach ($user->following as $item): ?>
                                    <div class="friend-icon">
                                        <a href="<?= $base; ?>/perfil.php?id=<?= $item->id; ?>">
                                            <div class="friend-icon-avatar">
                                                <img src="<?= $base; ?>/media/avatars/<?= $item->avatar; ?>" />
                                            </div>
                                            <div class="friend-icon-name">
                                                <?= $item->name; ?>
                                            </div>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                        </div>
                    </div>

                </div>
            </div>

        </div>

    </div>

</section>
<?php
require 'partials/footer.php';
?>