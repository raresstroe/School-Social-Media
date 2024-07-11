<?php
include "includes/init.php";

$isAdmin = isUserAdmin($db, $_SESSION['id']);

if ($_SESSION['id'] != $_GET['id'] && !$isAdmin) {
    header("Location: acces_denied.php");
    exit;
}
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $comment_id = $_GET['id'];

    $sql = "SELECT * FROM comments WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute(['id' => $comment_id]);

    $comment = $stmt->fetch(PDO::FETCH_ASSOC);
}

$photo_name = getNameByID($db, $comment['photo_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id'])) {
        $id = $_POST['id'];
        $profile_id = $comment['profile_user_id'];
        $comment_text = $_POST['comment'];

        $sql = "UPDATE comments SET comment_text = :comment_text WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'comment_text' => $comment_text,
            'id' => $id,
        ]);

        header("Location: user_photo.php?photo={$photo_name}");
        exit();
    }
}
?>

<?php include "includes/header.php"; ?>
<?php include "includes/nav.php"; ?>

<div class="container">
    <h2>Update Comentariu</h2>
    <form method="post" action="update_comment_photo.php?id=<?= $comment['id']; ?>&profile_id=<?= $comment['profile_user_id']; ?>">
        <div class="form-group">
            <input type="hidden" name="id" value="<?= $comment['id']; ?>">
        </div>
        <div class="form-group">
            <label for="comment">Noul comentariu:</label>
            <input class="form-control" type="text" name="comment" value="<?= $comment['comment_text'] ?>" required>
        </div>
        <input class="btn btn-primary log-in" type="submit" value="Save">
    </form>
</div>

<?php include "includes/footer.php"; ?>