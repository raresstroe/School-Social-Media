<?php
include "includes/init.php";

$comment = array('id' => '', 'comment_text' => '', 'profile_user_id' => '');

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $comment_id = $_GET['id'];

    $sql = "SELECT * FROM comments WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute(['id' => $comment_id]);

    $comment = $stmt->fetch(PDO::FETCH_ASSOC);
}
// if ($_SESSION['id'] != $comment['profile_user_id'] && $_SESSION['admin'] = true) {
//     die("Nu ai acces la aceasta pagina!");
// }
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

        header("Location: user_profile.php?id={$profile_id}");
        exit();
    }
}
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
?>
    <div class="container">
        <form method="post" action="update_comment.php?id=<?= $comment['id']; ?>&profile_id=<?= $comment['profile_user_id']; ?>">
            <div class="form-group">
                <input type="hidden" name="id" value="<?= $comment['id']; ?>">
            </div>
            <div class="form-group">
                <label for="comment">Noul comentariu:</label>
                <input class="form-control" type="text" name="comment" value="<?= $comment['comment_text'] ?>" required>
            </div>
            <input class="btn btn-primary log-in" type="submit" value="Salveaza">
        </form>
    </div>
<?php
} else {
    include "includes/header.php";
    include "includes/nav.php";
    include "includes/footer.php";
}
