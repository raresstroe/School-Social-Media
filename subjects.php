<?php
include "includes/init.php";

$subjects = getSubjects($db);
?>

<?php include "includes/header.php" ?>
<?php include "includes/nav.php" ?>

<div class="container">

    <h2 style="margin-top:10px">Materii</h2>
    <?php if ($_SESSION['admin'] == true) : ?>
        <a href="add_subject.php" class="btn btn-primary" style="margin-top:10px; margin-bottom:10px">Adauga Materie</a>
    <?php endif ?>
    <div class="list-group">
        <?php foreach ($subjects as $subject) : ?>
            <div class="list-group-item d-flex justify-content-between align-items-center" style="margin-bottom:10px">
                <span><?= $subject['subject']; ?></span>
                <div>
                    <a href="note_subject.php?id=<?= $subject['id']; ?>" class="btn btn-primary mr-2">
                        Note
                    </a>
                    <?php if ($_SESSION['admin'] == true) : ?>
                        <a href="update_subject.php?id=<?= $subject['id']; ?>" class="btn btn-primary mr-2">
                            Edit
                        </a>
                        <button type="button" class="btn btn-danger delete-subject" data-id="<?= $subject['id']; ?>">Sterge</button>

                    <?php endif ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('.delete-subject').click(function() {
            var id = $(this).data('id');

            var confirmDelete = confirm('Esti sigur ca vrei sa stergi acest subiect?');
            if (confirmDelete) {
                $.ajax({
                    url: 'delete.php',
                    type: 'POST',
                    data: {
                        id: id,
                        type: 'subject',
                    },
                    success: function(response) {
                        if (response == 'success') {
                            alert('Subiect sters cu succes');
                            location.reload();
                        } else {
                            alert('Subiect nu a putut fi sters');
                        }
                    }
                });
            }
        });
    });
</script>
<?php include "includes/footer.php"; ?>