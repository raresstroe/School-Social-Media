<?php
session_start();
require "config.php";
include "includes/functions.php";
$db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);




if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (auth($db, $_POST['username'], $_POST['password'])) {
        login();

        header("location:/");
        exit;
    } else {
        $error = "Login incorrect";
    }
}
?>

<?php include "includes/header.php" ?>

<div class="container" style="margin-top: 30px;">
    <main>
        <h2>Login</h2>

        <?php if (!empty($error)) : ?>
            <p><?= $error ?></p>
        <?php endif; ?>

        <form method='post'>
            <div class="form-group">
                <label for="username">Username</label>
                <input name="username" id="username" class="form-control">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input name="password" id="password" type="password" class="form-control">
            </div>

            <button class="btn btn-primary log-in">Login</button>
        </form>
    </main>
    <a href="users_login.php" style="padding-top: 30px;">
        <h2 style="text-align:center; margin-top:50px">Vizualizare Profile</h2>
    </a>
</div>

<?php include "includes/footer.php"; ?>