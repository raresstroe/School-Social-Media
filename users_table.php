<?php
include "includes/init.php";

$usersWithGrades = getUsersWithGrades($db);

$subjectGrades = [];
$userAverages = [];

foreach ($usersWithGrades as $record) {
    $userId = $record['id'];
    $subject = $record['subject'];
    $grade = $record['nota'];

    if (!isset($subjectGrades[$subject])) {
        $subjectGrades[$subject] = ['grades' => [], 'average' => 0];
    }

    if (!empty($grade)) {
        $subjectGrades[$subject]['grades'][$userId] = $grade;
    }

    if (!isset($userAverages[$userId])) {
        $userAverages[$userId] = ['grades' => [], 'average' => 0];
    }

    if (!empty($grade)) {
        $userAverages[$userId]['grades'][] = $grade;
    }
}

foreach ($subjectGrades as &$subjectData) {
    $subjectData['average'] = calculateAverageTable($subjectData['grades']);
}
unset($subjectData);

foreach ($userAverages as &$userData) {
    $userData['average'] = calculateAverageTable($userData['grades']);
}
unset($userData);
?>

<?php include "includes/header.php" ?>
<?php include "includes/nav.php" ?>

<div class="container mx-auto" style="max-width: 80%;">
    <h2 style="margin-top: 10px">Utilizatori</h2>
    <?php if ($_SESSION['admin'] == true) : ?>
        <a href="add_user.php" class="btn btn-primary log-in" style="margin-bottom: 20px; margin-top: 10px;">Adauga Utilizator</a>
    <?php endif ?>

    <table class="table">
        <thead>
            <tr>
                <th>Crt.</th>
                <th>ID</th>
                <th>Nume</th>

                <?php foreach ($subjectGrades as $subject => $data) : ?>
                    <th><?= $subject; ?><br>Medie: <?= $data['average']; ?></th>
                <?php endforeach; ?>

                <th>Medie</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php $counter = 1; ?>
            <?php $userIdsProcessed = []; ?>
            <?php foreach ($usersWithGrades as $record) : ?>
                <?php if (!in_array($record['id'], $userIdsProcessed)) : ?>
                    <tr>
                        <td><?= $counter++; ?></td>
                        <td><?= $record['id']; ?></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <?php if (!empty($record['profile'])) : ?>
                                    <div class="rounded-circle mr-3" style="width: 50px; height: 50px; overflow: hidden;">
                                        <img src="uploads/profile/<?= $record['profile']; ?>" alt="Profile Photo" style="width: 100%; height: 100%;  object-fit: cover;
                                        object-position: center;">
                                    </div>
                                <?php endif; ?>
                                <span><?= $record['username']; ?></span>
                            </div>
                        </td>

                        <?php foreach ($subjectGrades as $subject => $data) : ?>
                            <td>
                                <?php
                                $grade = $data['grades'][$record['id']] ?? '-';
                                echo $grade;
                                ?>
                            </td>
                        <?php endforeach; ?>

                        <td><?= round($userAverages[$record['id']]['average'], 2); ?></td>

                        <td>
                            <a href="user_profile.php?id=<?= $record['id']; ?>" class="btn btn-primary mr-2 table-btn">Profil</a>

                            <?php if ($_SESSION['id'] == $record['id'] || $_SESSION['admin'] == true) : ?>
                                <?php if ($_SESSION['id'] == $record['id']) : ?>
                                    <a href="user_galerie.php?id=<?= $record['id']; ?>" class="btn btn-primary mr-2 table-btn">Galerie</a>
                                <?php endif ?>
                                <a href="update_user.php?id=<?= $record['id']; ?>" class="btn btn-primary mr-2 table-btn">Edit</a>
                                <button type="button" class="btn btn-danger delete-user" data-id="<?= $record['id']; ?>">Sterge</button>
                            <?php endif; ?>

                        </td>
                    </tr>
                    <?php $userIdsProcessed[] = $record['id']; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
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
</body>

</html>