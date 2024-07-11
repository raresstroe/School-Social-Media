<?php
function isUserAdmin($conn, $user_id)
{
    $sql = "SELECT isAdmin FROM users WHERE id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return $user ? $user['isAdmin'] : false;
}

function isUsernameTaken($conn, $username)
{
    $sql = "SELECT id FROM users WHERE username = :username";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return $user ? true : false;
}
function isUsernameTakenUpdate($conn, $username, $userId = null)
{
    $sql = "SELECT id FROM users WHERE username = :username";
    if ($userId !== null) {
        $sql .= " AND id != :userId";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    if ($userId !== null) {
        $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
    }

    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return $user ? true : false;
}

function uploadProfilePicture($file, $prefix, $user_id)
{
    $upload_dir = 'uploads/profile/';

    if ($file['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $file['tmp_name'];
        $photo_name = $file['name'];
        $fileExtension = strtolower(pathinfo($photo_name, PATHINFO_EXTENSION));

        $picture_name = $prefix . '_' . $user_id . '.' . $fileExtension;
        move_uploaded_file($tmp_name, $upload_dir . $picture_name);

        return $picture_name;
    }

    return null;
}

function isImage($file)
{
    $allowedExtensions = array('jpg', 'jpeg', 'png', 'gif');
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    return in_array($fileExtension, $allowedExtensions);
}



function updateLikeStatus($photoId, $isLiked)
{
    global $db;

    if ($isLiked) {
        $sql = "INSERT INTO photo_likes (photo_id, user_id) VALUES (:photo_id, :user_id)";
    } else {
        $sql = "DELETE FROM photo_likes WHERE photo_id = :photo_id AND user_id = :user_id";
    }

    $stmt = $db->prepare($sql);
    $stmt->execute(['photo_id' => $photoId, 'user_id' => $_SESSION['id']]);
}
function getPhotos($conn)
{
    $sql = "SELECT photos.*, users.username, users.profile,
            (SELECT COUNT(*) FROM photo_likes WHERE photo_id = photos.id) AS like_count,
            (SELECT COUNT(*) FROM photo_likes WHERE photo_id = photos.id AND user_id = :user_id) AS liked
            FROM photos
            INNER JOIN users ON photos.user_id = users.id
            ORDER BY RAND()";

    $stmt = $conn->prepare($sql);
    $stmt->execute(['user_id' => $_SESSION['id']]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUserByUsername($conn, $username)
{
    $sql = "SELECT * FROM users WHERE username = :username";

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function login()
{
    global $db;

    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $user = getUserByUsername($db, $username);

        if ($user && auth($db, $username, $password)) {
            session_regenerate_id(true);
            $_SESSION['is_logged_in'] = true;
            $_SESSION['id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['admin'] = $user['isAdmin'];
        }
    }
}

function auth($conn, $username, $password)
{
    $sql = "SELECT * FROM users WHERE username = :username";

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);

    $stmt->execute();

    if ($user = $stmt->fetch()) {
        $hashedPassword = md5($password);

        return $hashedPassword === $user['password'];
    }

    return false;
}

function logout()
{
    $_SESSION = array();

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();
}
function getNamebyID($db, $photoID)
{
    $sql = "SELECT photo_name FROM photos WHERE id= :id;";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        'id' => $photoID,
    ]);

    return $stmt->fetchColumn();
}
function getSubjects($conn)
{
    $sql = "SELECT * FROM subjects";
    $stmt = $conn->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSubjectsUpdate($conn)
{
    $sql = "SELECT id, subject FROM subjects";
    $stmt = $conn->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function getNotesForUser($conn, $user_id)
{
    $sql = "SELECT subjects.id AS subject_id, subjects.subject, notes.id AS note_id, notes.nota
            FROM subjects
            LEFT JOIN notes ON subjects.id = notes.subject_id AND notes.user_id = :user_id";

    $stmt = $conn->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getNoteForSubject($notes, $subject_id)
{
    foreach ($notes as $note) {
        if ($note['subject_id'] == $subject_id) {
            return $note;
        }
    }

    return ['nota' => ''];
}

function isSubjectSelected($subject_id, $associated_subjects)
{
    foreach ($associated_subjects as $subject) {
        if ($subject['id'] == $subject_id) {
            return true;
        }
    }
    return false;
}

function getSubjectName($conn, $subject_id)
{
    $sql = "SELECT subject FROM subjects WHERE id = :subject_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['subject_id' => $subject_id]);
    $subject = $stmt->fetch(PDO::FETCH_ASSOC);

    return $subject ? $subject['subject'] : 'Unknown Subject';
}

function getUserNameById($conn, $user_id)
{
    $sql = "SELECT username FROM users WHERE id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return $user ? $user['username'] : 'Unknown User';
}

// function getPhotosByUserId($conn, $user_id)
// {
//     $sql = "SELECT * FROM photos WHERE user_id = :user_id";
//     $stmt = $conn->prepare($sql);
//     $stmt->execute(['user_id' => $user_id]);
//     return $stmt->fetchAll(PDO::FETCH_ASSOC);
// }

// function deletePhoto($conn, $user_id, $photo_id)
// {
//     $sql = "SELECT photo_name FROM photos WHERE id = :photo_id AND user_id = :user_id";
//     $stmt = $conn->prepare($sql);
//     $stmt->execute(['photo_id' => $photo_id, 'user_id' => $user_id]);
//     $photo = $stmt->fetch(PDO::FETCH_ASSOC);

//     if ($photo) {
//         $upload_dir = 'uploads/proiecte/';
//         $photo_name = $photo['photo_name'];

//         $photo_path = $upload_dir . $photo_name;
//         if (file_exists($photo_path)) {
//             unlink($photo_path);
//         }

//         $sql = "DELETE FROM photos WHERE id = :photo_id AND user_id = :user_id";
//         $stmt = $conn->prepare($sql);
//         $stmt->execute(['photo_id' => $photo_id, 'user_id' => $user_id]);
//     }
// }

function getUserById($conn, $user_id)
{
    $sql = "SELECT users.id, users.username, users.profile,
                   users.nume, users.prenume, users.email, users.tel,
                   GROUP_CONCAT(DISTINCT subjects.subject) AS subjects,
                   GROUP_CONCAT(notes.nota) AS grades,
                   IFNULL(AVG(ratings.value), 0) AS average_rating
            FROM users
            LEFT JOIN user_subjects ON users.id = user_subjects.user_id
            LEFT JOIN subjects ON user_subjects.subject_id = subjects.id
            LEFT JOIN notes ON users.id = notes.user_id AND subjects.id = notes.subject_id
            LEFT JOIN ratings ON users.id = ratings.profile_id
            WHERE users.id = :user_id
            GROUP BY users.id;";

    $stmt = $conn->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sqlPhotos = "SELECT id, GROUP_CONCAT(photo_name) AS photos FROM photos WHERE user_id = :user_id GROUP BY id";
    $stmtPhotos = $conn->prepare($sqlPhotos);
    $stmtPhotos->execute(['user_id' => $user_id]);
    $photos = $stmtPhotos->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($photos)) {
        $user[0]['photos'] = $photos;
    } else {
        $user[0]['photos'] = array();
    }

    return $user;
}
function getCommentsByUserId($conn, $user_id)
{
    $sql = "SELECT comments.id, comments.commenter_id, users.username AS commenter_username,
                   comments.comment_text, users.profile AS commenter_profile,
                   comments.parent_comment_id
            FROM comments
            INNER JOIN users ON comments.commenter_id = users.id
            WHERE comments.profile_user_id = :user_id
            ORDER BY comments.id";

    $stmt = $conn->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $commentsTree = [];
    foreach ($comments as $comment) {
        $commentId = $comment['id'];
        $comment['replies'] = [];
        $commentsTree[$commentId] = $comment;
    }

    foreach ($commentsTree as $comment) {
        $parentCommentId = $comment['parent_comment_id'];
        if (!empty($parentCommentId)) {
            $commentsTree[$parentCommentId]['replies'][] = $comment;
        }
    }

    $mainComments = array_filter($commentsTree, function ($comment) {
        return empty($comment['parent_comment_id']);
    });

    $addRepliesRecursively = function (&$comment) use (&$commentsTree, &$addRepliesRecursively) {
        if (isset($comment['replies'])) {
            foreach ($comment['replies'] as &$reply) {
                if (isset($commentsTree[$reply['id']])) {
                    $reply['replies'] = $commentsTree[$reply['id']]['replies'];
                    $addRepliesRecursively($reply);
                }
            }
        }
    };

    foreach ($mainComments as &$mainComment) {
        $addRepliesRecursively($mainComment);
    }

    return $mainComments;
}


function calculateAverage($user)
{
    $totalGrades = 0;
    $numGrades = 0;

    if (!empty($user[0]['grades'])) {
        $gradesArray = explode(',', $user[0]['grades']);
        foreach ($gradesArray as $grade) {
            $totalGrades += (int) $grade;
            $numGrades++;
        }
    }

    $average = $numGrades > 0 ? $totalGrades / $numGrades : 0;
    return round($average, 2);
}
function calculateAverageLogin($userGrades)
{
    $totalGrades = 0;
    $numGrades = 0;

    foreach ($userGrades as $grade) {
        if (!empty($grade['nota'])) {
            $totalGrades += $grade['nota'];
            $numGrades++;
        }
    }

    $average = $numGrades > 0 ? $totalGrades / $numGrades : 0;
    return round($average, 2);
}
function displayNestedReplies($comments, $selectedUserId, $level = 0)
{
    foreach ($comments as $comment) {
        $commenterProfilePicture = !empty($comment['commenter_profile']) ? "uploads/profile/{$comment['commenter_profile']}" : 'path_to_default_profile_picture.jpg';
?>

        <div class="media mb-4 comment-reply" style="margin-left: <?= $level * 30 ?>px;">
            <a href="user_profile_login.php?id=<?= $comment['commenter_id'] ?>">
                <img src="<?= $commenterProfilePicture ?>" alt="<?= $comment['commenter_username'] ?>" class="mr-3 rounded-circle" style="width: 50px; height: 50px; object-fit: cover; object-position: center;">
            </a>
            <div class="media-body">
                <a href="user_profile_login.php?id=<?= $comment['commenter_id'] ?>">
                    <h5 class="mt-0"><?= $comment['commenter_username'] ?></h5>
                </a>
                <p><?= $comment['comment_text'] ?></p>
                <?php if (isset($comment['replies']) && count($comment['replies']) > 0) : ?>
                    <div class='comment-actions show-more' style='margin-top: 5px;'>
                        <button class='show-more-btn btn btn-link btn-sm' data-target='replies-container-<?= $comment['id'] ?>'>Arata mai mult</button>
                    </div>
                    <div id='replies-container-<?= $comment['id'] ?>' class='replies-container' style='display: none;'>
                        <?php displayNestedReplies($comment['replies'], $selectedUserId, $level + 1); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php
    }
}

function displayNestedRepliesProfile($comments, $selectedUserId, $level = 0)
{
    foreach ($comments as $comment) {
        $commenterProfilePicture = !empty($comment['commenter_profile']) ? "uploads/profile/{$comment['commenter_profile']}" : '';
        $isCurrentUserComment = ($_SESSION['id'] == $comment['commenter_id']);
    ?>

        <div class="media mb-4 comment-reply" style="margin-left: <?= $level * 30 ?>px;">
            <a href="user_profile.php?id=<?= $comment['commenter_id'] ?>">
                <img src="<?= $commenterProfilePicture ?>" alt="<?= $comment['commenter_username'] ?>" class="mr-3 rounded-circle" style="width: 50px; height: 50px; object-fit: cover; object-position: center;">
            </a>
            <div class="media-body">
                <a href="user_profile.php?id=<?= $comment['commenter_id'] ?>">
                    <h5 class="mt-0"><?= $comment['commenter_username'] ?></h5>
                </a>
                <p><?= $comment['comment_text'] ?></p>
                <?php if ($isCurrentUserComment) : ?>
                    <div class="comment-actions" style="margin-bottom:20px">
                        <a class="edit-profile" data-id="<?= $comment['id']; ?>">Edit</a> |
                        <a class="delete-profile" data-id="<?= $comment['id']; ?>">Sterge</a> |
                        <a class="reply-profile" data-id="<?= $comment['id']; ?>" data-profile-id="<?= $selectedUserId; ?>">Reply</a>
                    </div>
                <?php else : ?>
                    <div class="comment-actions">
                        <a class="reply-profile" data-id="<?= $comment['id']; ?>" data-profile-id="<?= $selectedUserId; ?>">Reply</a>
                    </div>
                <?php endif; ?>

                <?php if (isset($comment['replies']) && count($comment['replies']) > 0) : ?>
                    <div class='comment-actions show-more' style='margin-top: 5px;'>
                        <button class='show-more-btn btn btn-link btn-sm' data-target='replies-container-<?= $comment['id'] ?>'>Arata mai mult</button>
                    </div>
                    <div id='replies-container-<?= $comment['id'] ?>' class='replies-container' style='display: none;'>
                        <?php displayNestedRepliesProfile($comment['replies'], $selectedUserId, $level + 1); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
<?php
    }
}

function getUsers($conn)
{
    $sql = "SELECT users.id, users.username, users.profile, users.isAdmin, 
                   subjects.subject, notes.nota
            FROM users
            LEFT JOIN user_subjects ON users.id = user_subjects.user_id
            LEFT JOIN subjects ON user_subjects.subject_id = subjects.id
            LEFT JOIN notes ON users.id = notes.user_id AND subjects.id = notes.subject_id
            WHERE users.isAdmin = 0
            ORDER BY users.id;";

    $results = $conn->query($sql);
    return $results->fetchAll(PDO::FETCH_ASSOC);
}
function getUsersAll($conn)
{
    $sql = "SELECT users.id, users.username, users.profile, users.isAdmin, 
                   subjects.subject, notes.nota
            FROM users
            LEFT JOIN user_subjects ON users.id = user_subjects.user_id
            LEFT JOIN subjects ON user_subjects.subject_id = subjects.id
            LEFT JOIN notes ON users.id = notes.user_id AND subjects.id = notes.subject_id
            ORDER BY users.id;";

    $results = $conn->query($sql);
    return $results->fetchAll(PDO::FETCH_ASSOC);
}
function calculateAverageUsers($userGrades)
{
    $totalGrades = 0;
    $numGrades = 0;

    foreach ($userGrades as $grade) {
        if (!empty($grade['nota'])) {
            $totalGrades += $grade['nota'];
            $numGrades++;
        }
    }

    $average = $numGrades > 0 ? $totalGrades / $numGrades : 0;
    return round($average, 2);
}
function getUsersWithGrades($conn)
{
    $sql = "SELECT users.id, users.username, users.profile, 
                   subjects.subject, notes.nota
            FROM users
            LEFT JOIN user_subjects ON users.id = user_subjects.user_id
            LEFT JOIN subjects ON user_subjects.subject_id = subjects.id
            LEFT JOIN notes ON users.id = notes.user_id AND subjects.id = notes.subject_id
            WHERE notes.nota IS NOT NULL AND users.isAdmin = 0
            ORDER BY users.id, subjects.subject;";

    $results = $conn->query($sql);
    return $results->fetchAll(PDO::FETCH_ASSOC);
}

function calculateAverageTable($subjectGrades)
{
    $gradesSum = array_sum($subjectGrades);
    $numGrades = count($subjectGrades);
    $average = $numGrades > 0 ? $gradesSum / $numGrades : 0;
    return round($average, 2);
}

function deleteUserSubjectsByUserID($conn, $id)
{
    $sql = "DELETE FROM user_subjects WHERE user_id = :id";

    $stmt = $conn->prepare($sql);

    $stmt->bindValue(":id", $id, PDO::PARAM_INT);

    return $stmt->execute();
}
function deleteNotesByUserID($conn, $id)
{
    $sql = "DELETE FROM notes WHERE user_id = :id";

    $stmt = $conn->prepare($sql);

    $stmt->bindValue(":id", $id, PDO::PARAM_INT);

    return $stmt->execute();
}

function delete($type, $db)
{
    if ($type == 'users') {
        deleteUserSubjectsByUserID($db, $_POST['id']);
        deleteNotesByUserID($db, $_POST['id']);
    }
    if (isset($_POST['id'])) {
        $sql = "DELETE FROM $type WHERE id = :id";
        $stmt = $db->prepare($sql);
        if ($stmt->execute([':id' => $_POST['id']])) {
            echo 'success';
        } else {
            echo 'Error: ' . $stmt->errorInfo()[2];
        }
    }
}
function getUserByIdNot($conn, $user_id)
{
    $sql = "SELECT users.id, users.username, users.profile,
                   users.nume, users.prenume, users.email, users.tel,
                   GROUP_CONCAT(DISTINCT subjects.subject) AS subjects,
                   GROUP_CONCAT(notes.nota) AS grades,
                   IFNULL(AVG(ratings.value), 0) AS average_rating
            FROM users
            LEFT JOIN user_subjects ON users.id = user_subjects.user_id
            LEFT JOIN subjects ON user_subjects.subject_id = subjects.id
            LEFT JOIN notes ON users.id = notes.user_id AND subjects.id = notes.subject_id
            LEFT JOIN ratings ON users.id = ratings.profile_id
            WHERE users.id = :user_id
            GROUP BY users.id;";

    $stmt = $conn->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sqlPhotos = "SELECT GROUP_CONCAT(photo_name) AS photos FROM photos WHERE user_id = :user_id;";
    $stmtPhotos = $conn->prepare($sqlPhotos);
    $stmtPhotos->execute(['user_id' => $user_id]);
    $photos = $stmtPhotos->fetch(PDO::FETCH_ASSOC);

    if (!empty($photos['photos'])) {
        $user[0]['photos'] = $photos['photos'];
    } else {
        $user[0]['photos'] = '';
    }

    return $user;
}
