<?php
include "includes/init.php";

$isAdmin = isUserAdmin($db, $_SESSION['id']);

if ($_SESSION['id'] == $_GET['id'] && !$isAdmin) {
    header("Location: acces_denied.php");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['rating'])) {
        $rating = $_POST['rating'];
        $profileUserId = $_POST['profile_user_id'];

        $userId = $_SESSION['id'];
        $sql = "UPDATE ratings SET value = :value WHERE user_id = :user_id AND profile_id = :profile_id";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'profile_id' => $profileUserId,
            'value' => $rating,
        ]);

        header("Location: user_profile.php?id=$profileUserId");
        exit();
    }
}


$selectedUserId = $_GET['id'];
$userId = $_SESSION['id'];
$sql = "SELECT value FROM ratings WHERE user_id = :user_id AND profile_id = :profile_id";
$stmt = $db->prepare($sql);
$stmt->execute(['user_id' => $userId, 'profile_id' => $selectedUserId]);
$existingRating = $stmt->fetchColumn();
?>

<?php include "includes/header.php" ?>
<?php include "includes/nav.php" ?>

<div class="container">
    <form method="post" action="update_rating.php" style="margin-top:30px">
        <div class="form-group">
            <label for="rating">Edit Rating (Intre 1 si 5):</label>
            <input type="number" class="form-control" name="rating" id="rating" max="5" min="1" value="<?= $existingRating ?>" required>
        </div>
        <input type="hidden" name="profile_user_id" value="<?= $selectedUserId ?>">
        <button type="submit" class="btn btn-primary">Save Rating</button>
    </form>
</div>


<?php include "includes/footer.php"; ?>