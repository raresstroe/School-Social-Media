<?php
include 'includes/init.php';

$photoId = $_POST['photo_id'];
$isLiked = $_POST['is_liked'];

error_log("photo_id: " . $photoId);

$sql = "SELECT * FROM photos WHERE id = :photo_id";
$stmt = $db->prepare($sql);
$stmt->execute(['photo_id' => $photoId]);
$photo = $stmt->fetch();

if ($photo) {
    if ($isLiked === '1') {
        $sql = "INSERT INTO photo_likes (photo_id, user_id) VALUES (:photo_id, :user_id)";
        $stmt = $db->prepare($sql);
        $stmt->execute(['photo_id' => $photoId, 'user_id' => $_SESSION['id']]);
    } else {
        $sql = "DELETE FROM photo_likes WHERE photo_id = :photo_id AND user_id = :user_id";
        $stmt = $db->prepare($sql);
        $stmt->execute(['photo_id' => $photoId, 'user_id' => $_SESSION['id']]);
    }

    $sql = "SELECT COUNT(*) as like_count FROM photo_likes WHERE photo_id = :photo_id";
    $stmt = $db->prepare($sql);
    $stmt->execute(['photo_id' => $photoId]);
    $like_count = $stmt->fetchColumn();

    echo json_encode(['success' => true, 'photo_id' => $photoId, 'like_count' => $like_count]);
} else {
    echo json_encode(['error' => 'Invalid photo ID', 'photo_id' => $photoId]);
}
