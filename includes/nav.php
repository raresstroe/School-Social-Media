<?php
$user_nav = getUserById($db, $_SESSION['id']);
// var_dump($user_nav);
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand font-weight-bold" href="/">Index</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <a class="nav-link font-weight-bold active" href="users.php">Utilizatori</a>
            </li>
            <li class="nav-item">
                <a class="nav-link font-weight-bold active" href="users_table.php">Tabel Utilizatori</a>
            </li>
            <li class="nav-item">
                <a class="nav-link font-weight-bold active" href="subjects.php">Materii</a>
            </li>
            <?php if ($_SESSION['admin'] == true) : ?>
                <li class="nav-item">
                    <a class="nav-link font-weight-bold active" href="all_users.php">Toti Utilizatorii</a>
                </li>
            <?php endif; ?>
        </ul>
        <form class="form-inline my-2 my-lg-0">
            <div class="d-flex align-items-center mr-3">
                <a href="user_profile.php?id=<?= $user_nav[0]['id']; ?>">
                    <img src="uploads/profile/<?= $user_nav[0]['profile'] ?>" alt="profile" class="rounded-circle mr-2" style="width: 50px; height: 50px; object-fit: cover; object-position: center;">
                </a>
                <a>Bun venit, <?= $user_nav[0]['username']; ?></a>
            </div>
        </form>
        <form class="form-inline my-2 my-lg-0">

            <a href="logout.php">Logout</a>
        </form>
    </div>
</nav>