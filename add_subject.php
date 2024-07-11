<?php
include "includes/init.php";

$isAdmin = isUserAdmin($db, $_SESSION['id']);

if (!$isAdmin) {
    header("Location: acces_denied.php");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // var_dump($_POST);
    $subject = $_POST['subject'];

    $sql = "INSERT INTO subjects (subject)
            VALUES (:subject)";

    $stmt = $db->prepare($sql);

    $stmt->execute([
        'subject' => $subject
    ]);

    header("Location: subjects.php");
    exit();
}
?>

<?php include "includes/header.php" ?>
<?php include "includes/nav.php" ?>

<div class="container">
    <h2>Adauga Materie</h2>
    <form method="post" action="add_subject.php">
        <div class="form-group">
            <label for="subject">Nume materie:</label>
            <input class="form-control" type="text" name="subject" require>
        </div>
        <input class="btn btn-primary log-in" type="submit" value="Save Changes">
    </form>
</div>

<?php include "includes/footer.php"; ?>