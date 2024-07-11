<?php
require "config.php";
include "includes/functions.php";
$db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$users = getUsers($db);

$processedUsers = [];
foreach ($users as $user) {
    $userId = $user['id'];
    if (!isset($processedUsers[$userId])) {
        $processedUsers[$userId] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'profile' => $user['profile'],
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


<div class="container">
    <h2 style="margin-top: 30px; margin-bottom:20px">Utilizatori</h2>
    <div class="row">
        <?php foreach ($processedUsers as $user) : ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="uploads/profile/<?= $user['profile']; ?>" class="card-img-top" alt="Profile Photo" style="max-height: 200px;">
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

                                    $average = calculateAverageLogin($user['nota']);
                                    echo '<p><strong>Media: ' . round($average, 2) . '</strong></p>';
                                    ?>
                                </li>
                            </ul>
                        <?php } ?>

                        <div class="card-text">
                            <a href="user_profile_login.php?id=<?= $user['id']; ?>" class="btn btn-primary mr-2" style="margin-top:10px">
                                Profil
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include "includes/footer.php"; ?>