<?php
include "includes/init.php";

$photoName = getNamebyID($db, $_GET['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['reply_comment'])) {
        $commentText = $_POST['reply_comment'];
        $parentCommentId = $_POST['parent_comment_id'];
        $photoID = $_POST['photo_id'];
        $photoName = $_POST['photo_name'];

        $insertReplySQL = "INSERT INTO comments (commenter_id, photo_id, comment_text, parent_comment_id) 
                           VALUES (:commenter_id, :photo_id, :comment_text, :parent_comment_id)";
        $stmt = $db->prepare($insertReplySQL);
        $stmt->execute([
            'commenter_id' => $_SESSION['id'],
            'photo_id' => $photoID,
            'comment_text' => $commentText,
            'parent_comment_id' => $parentCommentId,
        ]);

        header("Location: user_photo.php?photo=$photoName");
        exit();
    }
}
?>

<?php include "includes/header.php"; ?>
<?php include "includes/nav.php"; ?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2 class="mb-4">Reply</h2>
            <form method="post" action="reply_comment_photo.php" style="margin-top: 30px;">
                <div class="form-group">
                    <label for="reply_comment">Reply:</label>
                    <textarea class="form-control" name="reply_comment" id="reply_comment" rows="4" maxlength="400" required></textarea>
                </div>
                <input type="hidden" name="parent_comment_id" value="<?= $_GET['parent_id']; ?>">
                <input type="hidden" name="photo_id" value="<?= $_GET['id']; ?>">
                <input type="hidden" name="photo_name" value="<?= $photoName ?>">
                <button type="submit" class="btn btn-primary">Reply</button>
            </form>
        </div>
    </div>
</div>
<?php include "includes/footer.php"; ?>