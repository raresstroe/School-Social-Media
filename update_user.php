<?php
include "includes/init.php";

$isAdmin = isUserAdmin($db, $_SESSION['id']);

if ($_SESSION['id'] != $_GET['id'] && !$isAdmin) {
    header("Location: acces_denied.php");
    exit;
}
$user_id = null;
$user = [];
$associated_subjects = [];
$notes = [];

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $user_id = $_GET['id'];

    $sql = "SELECT * FROM users WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute(['id' => $user_id]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $sql = "SELECT subjects.* FROM subjects
            JOIN user_subjects ON subjects.id = user_subjects.subject_id
            WHERE user_subjects.user_id = :user_id";
    $stmt = $db->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);
    $associated_subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$notes = getNotesForUser($db, $user_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_subjects = isset($_POST['subject_id']) ? $_POST['subject_id'] : [];
    $user_id = $_POST['user_id'];

    $sql = "DELETE FROM user_subjects WHERE user_id = :user_id";
    $stmt = $db->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);

    foreach ($selected_subjects as $subject_id) {
        $sql = "INSERT INTO user_subjects (user_id, subject_id) VALUES (:user_id, :subject_id)";
        $stmt = $db->prepare($sql);
        $stmt->execute(['user_id' => $user_id, 'subject_id' => $subject_id]);
    }

    if (!empty($selected_subjects)) {
        $sql = "DELETE FROM notes WHERE user_id = :user_id AND subject_id NOT IN (" . implode(",", $selected_subjects) . ")";
        $stmt = $db->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);
    }

    foreach ($selected_subjects as $subject_id) {
        $note = isset($_POST["note_$subject_id"]) ? $_POST["note_$subject_id"] : '';

        $note = empty($note) ? null : $note;

        $sql = "SELECT * FROM notes WHERE user_id = :user_id AND subject_id = :subject_id";
        $stmt = $db->prepare($sql);
        $stmt->execute(['user_id' => $user_id, 'subject_id' => $subject_id]);
        $existing_note = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_note) {
            $note_id = $existing_note['id'];
            $sql = "UPDATE notes SET nota = :nota WHERE id = :note_id";
            $stmt = $db->prepare($sql);
            $stmt->execute(['nota' => $note, 'note_id' => $note_id]);
        } else {
            $sql = "INSERT INTO notes (user_id, subject_id, nota) VALUES (:user_id, :subject_id, :nota)";
            $stmt = $db->prepare($sql);
            $stmt->execute(['user_id' => $user_id, 'subject_id' => $subject_id, 'nota' => $note]);
        }
    }

    $admin = $_POST['isAdmin'];
    $new_user = $_POST['username'];
    $new_nume = $_POST['nume'];
    $new_prenume = $_POST['prenume'];
    $new_tel = $_POST['tel'];
    $new_email = $_POST['email'];
    $new_pass = $_POST['password'];
    if (isUsernameTakenUpdate($db, $new_user, $user_id)) {
        header("Location: update_user.php?id=" . $_GET['id'] . "&error=username_taken");
        exit;
    }
    $sql = "UPDATE users SET username = :username, nume = :nume, prenume = :prenume, tel = :tel, email = :email, isAdmin = :isAdmin";

    if ($_POST['password'] != "") {
        $sql .=  ", password = :password WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'username' => $new_user,
            'nume' => $new_nume,
            'prenume' => $new_prenume,
            'tel' => $new_tel,
            'email' => $new_email,
            'password' => md5($new_pass),
            'id' => $user_id,
            'isAdmin' => $admin
        ]);
    } else {
        $sql .= " WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'username' => $new_user,
            'nume' => $new_nume,
            'prenume' => $new_prenume,
            'tel' => $new_tel,
            'email' => $new_email,
            'id' => $user_id,
            'isAdmin' => $admin
        ]);
    }

    if ($_FILES['profile']['error'] === UPLOAD_ERR_OK) {

        if (!isImage($_FILES['profile'])) {

            header("Location: update_user.php?id=$user_id&error=invalid_file_type");
            exit();
        }

        $upload_dir = 'uploads/profile/';
        $tmp_name = $_FILES['profile']['tmp_name'];
        $photo_name = $_FILES['profile']['name'];

        $user_photo_name = 'profile_' . $user_id . '.' . pathinfo($photo_name, PATHINFO_EXTENSION);

        if (!empty($user['profile'])) {
            $old_photo_path = $upload_dir . $user['profile'];
            if (file_exists($old_photo_path)) {
                unlink($old_photo_path);
            }
        }

        move_uploaded_file($tmp_name, $upload_dir . $user_photo_name);

        $sql = "UPDATE users SET profile = :profile WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute(['profile' => $user_photo_name, 'id' => $user_id]);
    }

    header("Location: users.php");
    exit();
}

$subjects = getSubjectsUpdate($db);

$selected_subjects = array_column($associated_subjects, 'id');
?>

<?php include "includes/header.php" ?>
<?php include "includes/nav.php" ?>

<div class="container">
    <h2>Edit Utilizator</h2>

    <?php if (!empty($user['profile'])) : ?>
        <img src="uploads/profile/<?= $user['profile']; ?>" alt="Profile Photo" style="max-width: 200px; max-height: 200px;">
    <?php endif; ?>

    <form method="post" action="update_user.php?id=<?= $user_id; ?>" enctype="multipart/form-data">
        <div class="form-group">
            <input type="hidden" name="user_id" value="<?= $user_id; ?>">
        </div>
        <?php if ($_SESSION['admin'] == true) : ?>
            <div class="form-group d-flex align-items-center">
                <label for="isAdmin" class="mb-0 mr-2">Admin:</label>
                <input type="hidden" name="isAdmin" value="0">
                <input type="checkbox" name="isAdmin" id="isAdmin" value="1" <?php if ($user['isAdmin']) echo 'checked'; ?>>
            </div>
        <?php endif; ?>
        <div class="form-group">
            <label for="username">Username:</label>
            <input class="form-control" type="text" name="username" value="<?= $user['username']; ?>">
            <?php if (isset($_GET['error']) && $_GET['error'] === 'username_taken') {
                echo '<div class="alert alert-danger">Numele de utilizator este deja folosit</div>';
            } ?>
        </div>
        <div class="form-group">
            <label for="nume">Nume:</label>
            <input class="form-control" type="text" name="nume" value="<?= $user['nume']; ?>">
        </div>
        <div class="form-group">
            <label for="prenume">Prenume:</label>
            <input class="form-control" type="text" name="prenume" value="<?= $user['prenume']; ?>">
        </div>
        <div class="form-group">
            <label for="tel">Telefon:</label>
            <input class="form-control" type="text" name="tel" value="<?= $user['tel']; ?>">
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input class="form-control" type="text" name="email" value="<?= $user['email']; ?>">
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input class="form-control" type="password" name="password">
        </div>
        <div class="group-subjects" style="display: <?= $_SESSION['admin'] == true ? 'block' : 'none' ?>">
            <div class="form-group">
                <label for="subject_id">Alege Materiile:</label>
                <select class="form-control" name="subject_id[]" multiple>
                    <?php foreach ($subjects as $subject) : ?>
                        <option value="<?= $subject['id']; ?>" <?= in_array($subject['id'], $selected_subjects) ? 'selected' : ''; ?>>
                            <?= $subject['subject']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php foreach ($subjects as $subject) : ?>
                <div class="form-group <?= in_array($subject['id'], $selected_subjects) ? '' : 'd-none'; ?>">
                    <label for="note_<?= $subject['id']; ?>"><?= $subject['subject']; ?>:</label>
                    <input class="form-control" type="number" name="note_<?= $subject['id']; ?>" value="<?= getNoteForSubject($notes, $subject['id'])['nota'] ?? ''; ?>">
                </div>
            <?php endforeach; ?>
        </div>
        <div class="form-group">
            <label for="profile">Poza de Profil:</label>
            <input type="file" class="form-control-file" name="profile" require>
        </div>

        <?php
        if (isset($_GET['error']) && $_GET['error'] === 'invalid_file_type') {
            echo '<div class="alert alert-danger">File type invalid. File types acceptate: jpg, jpeg, png, gif.</div>';
        }
        ?>

        <input class="btn btn-primary log-in" style="margin-bottom: 100px; margin-top:20px;  float: right;" type="submit" value="Salveaza">
    </form>
</div>

<?php include "includes/footer.php"; ?>