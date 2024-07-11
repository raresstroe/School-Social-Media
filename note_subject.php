<?php
include "includes/init.php";

$id = $_GET['id'];

$sql = "SELECT users.id AS user_id, users.username AS user_username, notes.id AS note_id, notes.nota, subjects.subject
        FROM users
        JOIN notes ON users.id = notes.user_id
        LEFT JOIN subjects ON notes.subject_id = subjects.id
        WHERE subjects.id = :id";

$stmt = $db->prepare($sql);
$stmt->execute(['id' => $id]);
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
// var_dump($notes);
?>

<?php include "includes/header.php" ?>
<?php include "includes/nav.php" ?>


<div class="container">
    <!-- <a href="add_note.php" class="btn btn-primary log-in">Adauga Nota</a> -->
    <?php if (isset($notes)) : ?>
        <?php if (count($notes) > 0) : ?>
            <div class="card-header">
                Note pentru materia: <?= $notes[0]['subject']; ?>
            </div>
            <?php foreach ($notes as $note) : ?>
                <div class="card mt-3">
                    <div class="card-body">
                        <p>User: <?= $note['user_username']; ?></p>
                        <p>Nota: <?= $note['nota']; ?></p>
                        <input type="hidden" name="note_id" value="<?= $note['note_id']; ?>">
                        <?php if ($_SESSION['admin'] == true) : ?>
                            <button type="button" class="btn btn-danger delete-note" data-id="<?= $note['note_id']; ?>">Sterge</button>
                        <?php endif ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="alert alert-info mt-3">
                Nu exista note pentru materia selectata.
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
<script>
    $(document).ready(function() {
        $('.delete-note').click(function() {
            var id = $(this).data('id');

            var confirmDelete = confirm('Esti sigur ca vrei sa stergi aceasta nota?');
            if (confirmDelete) {
                $.ajax({
                    url: 'delete.php',
                    type: 'POST',
                    data: {
                        id: id,
                        type: 'note',
                    },
                    success: function(response) {
                        if (response == 'success') {
                            alert('Nota stearsa cu succes');
                            location.reload();
                        } else {
                            alert('Nota nu a putut fi stearsa');
                        }
                    }
                });
            }
        });
    });
</script>
<?php include "includes/footer.php"; ?>