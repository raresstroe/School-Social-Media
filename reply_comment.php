<?php
include "includes/init.php";

$comment = array('id' => '', 'comment_text' => '', 'profile_user_id' => '');

if (isset($_GET['parent_id']) && !empty($_GET['parent_id'])) {
    $parent_comment_id = $_GET['parent_id'];

    $sql = "SELECT * FROM comments WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute(['id' => $parent_comment_id]);

    $comment = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['reply_comment'])) {
        $commentText = $_POST['reply_comment'];
        $parentCommentId = $_POST['parent_comment_id'];
        $profileUserId = $_POST['profile_user_id'];

        $insertReplySQL = "INSERT INTO comments (commenter_id, profile_user_id, comment_text, parent_comment_id) 
                           VALUES (:commenter_id, :profile_user_id, :comment_text, :parent_comment_id)";
        $stmt = $db->prepare($insertReplySQL);
        $stmt->execute([
            'commenter_id' => $_SESSION['id'],
            'profile_user_id' => $profileUserId,
            'comment_text' => $commentText,
            'parent_comment_id' => $parentCommentId,
        ]);

        header("Location: user_profile.php?id={$profileUserId}");
        exit();
    }
}

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
?>
    <div class="container">
        <form method="post" action="reply_comment.php?parent_id=<?= $comment['id']; ?>&profile_id=<?= $comment['profile_user_id']; ?>">
            <div class="form-group">
                <input type="hidden" name="parent_comment_id" value="<?= $comment['id']; ?>">
                <input type="hidden" name="profile_user_id" value="<?= $comment['profile_user_id']; ?>">
            </div>
            <div class="form-group">
                <label for="reply_comment">Reply:</label>
                <textarea class="form-control" name="reply_comment" id="reply_comment" rows="4" maxlength="400" required></textarea>
            </div>
            <input class="btn btn-primary log-in" type="submit" value="Reply">
        </form>
    </div>
<?php
}
?>