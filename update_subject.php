<?php
include "includes/init.php";
$isAdmin = isUserAdmin($db, $_SESSION['id']);

if ($_SESSION['id'] != $_GET['id'] && !$isAdmin) {
    header("Location: acces_denied.php");
    exit;
}
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $subject_id = $_GET['id'];

    $sql = "SELECT * FROM subjects WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute(['id' => $subject_id]);

    $subject = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    var_dump($_POST);
    $id = $_POST['id'];
    $subject = $_POST['subject'];

    $sql = "UPDATE subjects SET subject = :subject WHERE id = :id";

    $stmt = $db->prepare($sql);
    $stmt->execute([
        'subject' => $subject,
        'id' => $id,
    ]);

    header("Location: subjects.php");
    exit();
}
?>

<?php include "includes/header.php" ?>
<?php include "includes/nav.php" ?>

<div class="container">
    <h2>Update Materie</h2>
    <form method="post" action="update_subject.php">
        <div class="form-group">
            <input type="hidden" name="id" value="<?= $subject['id']; ?>">
        </div>
        <div class="form-group">
            <label for="subject">Numele nou al materiei:</label>
            <input class="form-control" type="text" name="subject" value="<?= $subject['subject'] ?>" required>
        </div>
        <input class="btn btn-primary log-in" type="submit" value="Save Changes">
    </form>
</div>

<?php include "includes/footer.php"; ?>