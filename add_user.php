<?php
include "includes/init.php";

$isAdmin = isUserAdmin($db, $_SESSION['id']);
$subjects = getSubjects($db);

if (!$isAdmin) {
    header("Location: acces_denied.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin = $_POST['isAdmin'];
    $username = $_POST['username'];
    $nume = $_POST['nume'];
    $prenume = $_POST['prenume'];
    $tel = $_POST['tel'];
    $email = $_POST['email'];
    $pass = $_POST['password'];

    $user_id = isUsernameTaken($db, $username);

    if (isUsernameTaken($db, $username)) {
        header("Location: add_user.php?id=" . $_GET['id'] . "&error=username_taken");
        exit;
    }
    $sql = "INSERT INTO users (username, nume, prenume, tel, email, password, isAdmin)
            VALUES (:username, :nume, :prenume, :tel, :email, :password, :isAdmin)";

    $stmt = $db->prepare($sql);

    $stmt->execute([
        'username' => $username,
        'nume' => $nume,
        'prenume' => $prenume,
        'tel' => $tel,
        'email' => $email,
        'password' => md5($pass),
        'isAdmin' => $admin,
    ]);

    $user_id = $db->lastInsertId();

    $selected_subjects = $_POST['subject_id'];

    foreach ($selected_subjects as $subject_id) {
        $sql = "INSERT INTO user_subjects (user_id, subject_id) VALUES (:user_id, :subject_id)";
        $stmt = $db->prepare($sql);
        $stmt->execute(['user_id' => $user_id, 'subject_id' => $subject_id]);
    }

    $profile_uploaded = false;

    if ($_FILES['profile']['error'] === UPLOAD_ERR_OK) {
        $profile_error = '';

        if (!isImage($_FILES['profile'])) {
            $profile_error = 'Contul a fost creat cu succes, dar fara poza de profil. Motiv: File type invalid, file types acceptat: jpg, jpeg, png, gif';
        }

        if (empty($profile_error)) {
            $profile_picture = uploadProfilePicture($_FILES['profile'], 'profile', $user_id);

            if ($profile_picture) {
                $sql = "UPDATE users SET profile = :profile WHERE id = :id";
                $stmt = $db->prepare($sql);
                $stmt->execute(['profile' => $profile_picture, 'id' => $user_id]);

                $profile_uploaded = true;
            }
        }
    }

    if (empty($profile_error)) {
        header("Location: users.php");
        exit();
    }
}
?>

<?php include "includes/header.php" ?>
<?php include "includes/nav.php" ?>

<div class="container">
    <h2>Add User</h2>
    <form method="post" action="add_user.php" enctype="multipart/form-data" onsubmit="return validateForm()">
        <div class="form-group d-flex align-items-center">
            <label for="isAdmin" class="mb-0 mr-2">Admin:</label>
            <input type="hidden" name="isAdmin" value="0">
            <input type="checkbox" name="isAdmin" id="isAdmin" value="1">
        </div>
        <div class="form-group">
            <label for="username">Username:</label>
            <input class="form-control" type="text" name="username" required>
            <?php if (isset($_GET['error']) && $_GET['error'] === 'username_taken') {
                echo "<p>Username already in use</p>";
            } ?>
        </div>
        <div class="form-group">
            <label for="nume">Nume:</label>
            <input class="form-control" type="text" name="nume" required>
        </div>
        <div class="form-group">
            <label for="prenume">Prenume:</label>
            <input class="form-control" type="text" name="prenume" required>
        </div>
        <div class="form-group">
            <label for="tel">Telefon:</label>
            <input class="form-control" type="text" name="tel" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input class="form-control" type="text" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input class="form-control" type="password" name="password" required>
        </div>
        <div class="form-group">
            <label for="subject_id">Alege Materii:</label>
            <select class="form-control" name="subject_id[]" multiple required>
                <?php foreach ($subjects as $subject) : ?>
                    <option value="<?= $subject['id']; ?>"><?= $subject['subject']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="profile">Profile Picture:</label>
            <input type="file" class="form-control-file" name="profile" id="profile" accept="image/*">
        </div>
        <?php if (isset($profile_error) && !empty($profile_error)) : ?>
            <div class="alert alert-danger"><?= $profile_error; ?></div>
        <?php endif; ?>
        <input class="btn btn-primary log-in" type="submit" value="Save Changes" style="margin-bottom:100px; float:right">
    </form>
</div>

<script>
    function validateForm() {
        const profileInput = document.getElementById('profile');

        const allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        function isValidFile(fileInput) {
            if (fileInput.value.trim() === '') {
                return true;
            }
            const fileExtension = fileInput.value.split('.').pop().toLowerCase();
            return allowedExtensions.includes(fileExtension);
        }

        if (!isValidFile(profileInput)) {
            alert('Profile picture type invalid. File types accepted: jpg, jpeg, png, gif.');
            return false;
        }

        return true;
    }
</script>

<?php include "includes/footer.php"; ?>