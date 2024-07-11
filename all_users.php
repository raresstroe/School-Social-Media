<?php
include "includes/init.php";
$isAdmin = isUserAdmin($db, $_SESSION['id']);

$users = getUsersAll($db);
if (!$isAdmin) {
    header("Location: acces_denied.php");
    exit;
}

$processedUsers = [];
foreach ($users as $user) {
    $userId = $user['id'];
    if (!isset($processedUsers[$userId])) {
        $processedUsers[$userId] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'profile' => $user['profile'],
            'isAdmin' => $user['isAdmin'],
            'nota' => [],
        ];
    }


    if (!empty($user['subject']) && !empty($user['nota'])) {
        $processedUsers[$userId]['nota'][] = [
            'subject' => $user['subject'],
            'nota' => $user['nota'],
        ];
    }
}
?>

<?php include "includes/header.php" ?>
<?php include "includes/nav.php" ?>
<div class="container">
    <a href="add_user.php" class="btn btn-primary log-in" style="margin-bottom: 20px; margin-top: 10px;">Adauga Utilizator</a>

    <h2 style="margin-top: 10px">Admin</h2>
    <div class="row">
        <?php foreach ($processedUsers as $user) : ?>
            <?php if ($user['isAdmin'] == 1) : ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-img-top-container">
                            <img src="uploads/profile/<?= $user['profile']; ?>" class="card-img-top" alt="Profile Photo" style="max-height: 200px;">
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?= $user['username']; ?></h5>
                            <?php

                            if (!empty($user['nota'])) {
                            ?>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">
                                        <h6>Note:</h6>
                                        <ul>
                                            <?php foreach ($user['nota'] as $grade) : ?>
                                                <li><?= $grade['subject'] . ': ' . $grade['nota']; ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                        <?php

                                        $average = calculateAverageUsers($user['nota']);
                                        echo '<p><strong>Media: ' . round($average, 2) . '</strong></p>';
                                        ?>
                                    </li>
                                </ul>
                            <?php } ?>

                            <div class="card-text">
                                <a href="user_profile.php?id=<?= $user['id']; ?>" class="btn btn-primary mr-2" style="margin-top:10px">
                                    Profil
                                </a>
                                <?php if ($_SESSION['id'] == $user['id']) : ?>
                                    <a href="user_galerie.php?id=<?= $user['id']; ?>" class="btn btn-primary mr-2" style="margin-top:10px">Galerie</a>
                                <?php endif ?>
                                <a href="update_user.php?id=<?= $user['id']; ?>" class="btn btn-primary mr-2" style="margin-top:10px">Edit</a>
                                <button type="button" class="btn btn-danger delete-user" data-id="<?= $user['id']; ?>" style="margin-top:10px">Sterge</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <h2 style="margin-top: 10px">Utilizatori</h2>
    <div class="row">
        <?php foreach ($processedUsers as $user) : ?>
            <?php if ($user['isAdmin'] == 0) : ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-img-top-container">
                            <img src="uploads/profile/<?= $user['profile']; ?>" class="card-img-top" alt="Profile Photo" style="max-height: 200px;">
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?= $user['username']; ?></h5>
                            <?php

                            if (!empty($user['nota'])) {
                            ?>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">
                                        <h6>Note:</h6>
                                        <ul>
                                            <?php foreach ($user['nota'] as $grade) : ?>
                                                <li><?= $grade['subject'] . ': ' . $grade['nota']; ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                        <?php

                                        $average = calculateAverageUsers($user['nota']);
                                        echo '<p><strong>Media: ' . round($average, 2) . '</strong></p>';
                                        ?>
                                    </li>
                                </ul>
                            <?php } ?>

                            <div class="card-text">
                                <a href="user_profile.php?id=<?= $user['id']; ?>" class="btn btn-primary mr-2" style="margin-top:10px">
                                    Profil
                                </a>
                                <?php if ($_SESSION['id'] == $user['id']) : ?>
                                    <a href="user_galerie.php?id=<?= $user['id']; ?>" class="btn btn-primary mr-2" style="margin-top:10px">Galerie</a>
                                <?php endif ?> <a href="update_user.php?id=<?= $user['id']; ?>" class="btn btn-primary mr-2" style="margin-top:10px">Edit</a>
                                <button type="button" class="btn btn-danger delete-user" data-id="<?= $user['id']; ?>" style="margin-top:10px">Sterge</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.delete-user').click(function() {
            var id = $(this).data('id');

            var confirmDelete = confirm('Esti sigur ca vrei sa stergi acest user?');
            if (confirmDelete) {
                $.ajax({
                    url: 'delete.php',
                    type: 'POST',
                    data: {
                        id: id,
                        type: 'user',
                    },
                    success: function(response) {
                        if (response == 'success') {
                            alert('User sters cu succes');
                            location.reload();
                        } else {
                            alert('Userul nu a putut fi sters');
                        }
                    }
                });
            }
        });
    });
</script>
<?php include "includes/footer.php"; ?>