<?php
require "config.php";
$db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (isset($_GET['id'])) {
    $selectedUserId = $_GET['id'];
    $user = getUserById($db, $selectedUserId);
    $comments = getCommentsByUserId($db, $selectedUserId);

    $user = getUserById($db, $selectedUserId);
    $averageRating = round($user[0]['average_rating'], 2);

    // print_r($user);
    // print_r($averageRating);
}
// print_r($comments);
?>

<?php include "includes/header.php"; ?>
<?php include "includes/nav.php"; ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tiny-slider/2.9.3/tiny-slider.css">


<div class="container mt-5">
    <?php if (!empty($user)) : ?>
        <?php
        $profileUsername = $user[0]['username'];
        $profilePicture = $user[0]['profile'];
        $userId = $user[0]['id'];
        $nume = $user[0]['nume'];
        $prenume = $user[0]['prenume'];
        $email = $user[0]['email'];
        $tel = $user[0]['tel'];
        $subjectsArray = explode(',', $user[0]['subjects']);
        $gradesArray = explode(',', $user[0]['grades']);
        $photosArray = explode(',', $user[0]['photos']);
        ?>

        <h2 class='mb-4'>Profil Utilizator</h2>

        <div class='row'>
            <div class='col-md-4'>
                <?php if (!empty($profilePicture)) : ?>
                    <img src='uploads/profile/<?= $profilePicture ?>' alt='<?= $profileUsername ?>' class='img-fluid rounded'>
                <?php else : ?>
                    <p class='mt-3'>Utilizatorul nu are poza de profil.</p>
                <?php endif; ?>

                <p class='mt-3'><strong>ID:</strong> <?= $userId ?></p>
                <p><strong>Username:</strong> <?= $profileUsername ?></p>
                <p><strong>Nume:</strong> <?= $nume ?></p>
                <p><strong>Prenume:</strong> <?= $prenume ?></p>
                <p><strong>Email:</strong> <?= $email ?></p>
                <p><strong>Telefon:</strong> <?= $tel ?></p>
                <?php if ($user[0]['average_rating'] != 0) : ?>
                    <p><strong>Rating:</strong> <?= $averageRating ?></p>
                    <?php if ($averageRating == 5) : ?>
                        <i class="fa fa-star" aria-hidden="true"></i>
                        <i class="fa fa-star" aria-hidden="true"></i>
                        <i class="fa fa-star" aria-hidden="true"></i>
                        <i class="fa fa-star" aria-hidden="true"></i>
                        <i class="fa fa-star" aria-hidden="true"></i>
                    <?php elseif ($averageRating >= 4.5) : ?>
                        <i class="fa fa-star" aria-hidden="true"></i>
                        <i class="fa fa-star" aria-hidden="true"></i>
                        <i class="fa fa-star" aria-hidden="true"></i>
                        <i class="fa fa-star" aria-hidden="true"></i>
                        <i class="fa fa-star-half" aria-hidden="true"></i>
                    <?php elseif ($averageRating >= 4) : ?>
                        <i class="fa fa-star" aria-hidden="true"></i>
                        <i class="fa fa-star" aria-hidden="true"></i>
                        <i class="fa fa-star" aria-hidden="true"></i>
                        <i class="fa fa-star" aria-hidden="true"></i>
                    <?php elseif ($averageRating >= 3.5) : ?>
                        <i class="fa fa-star" aria-hidden="true"></i>
                        <i class="fa fa-star" aria-hidden="true"></i>
                        <i class="fa fa-star" aria-hidden="true"></i>
                        <i class="fa fa-star-half" aria-hidden="true"></i>
                    <?php elseif ($averageRating >= 3) : ?>
                        <i class="fa fa-star" aria-hidden="true"></i>
                        <i class="fa fa-star" aria-hidden="true"></i>
                        <i class="fa fa-star" aria-hidden="true"></i>
                    <?php elseif ($averageRating >= 2.5) : ?>
                        <i class="fa fa-star" aria-hidden="true"></i>
                        <i class="fa fa-star" aria-hidden="true"></i>
                        <i class="fa fa-star-half" aria-hidden="true"></i>
                    <?php elseif ($averageRating >= 2) : ?>
                        <i class="fa fa-star" aria-hidden="true"></i>
                        <i class="fa fa-star" aria-hidden="true"></i>
                    <?php elseif ($averageRating >= 1.5) : ?>
                        <i class="fa fa-star" aria-hidden="true"></i>
                        <i class="fa fa-star-half" aria-hidden="true"></i>
                    <?php else :  ?>
                        <i class="fa fa-star" aria-hidden="true"></i>
                    <?php endif; ?>
                <?php else : ?>
                    <p>Acest utilizator nu are inca rating</p>
                <?php endif ?>
            </div>

            <div class="col-md-8">
                <?php if (!empty($user[0]['photos'])) : ?>
                    <h2 class="mb-4">Galerie</h2>
                    <div class="slider-controls">
                        <button class="slider-prev">&lt;</button>
                        <div id="slider" class="slider">
                            <?php foreach ($photosArray as $photo) : ?>
                                <img src="uploads/proiecte/<?= $photo ?>" alt="Poza" class="img-fluid">
                            <?php endforeach; ?>
                        </div>
                        <button class="slider-next">&gt;</button>
                    </div>
                <?php endif; ?>

                <h2 class='mt-4'>Materii si Note</h2>

                <?php if (!empty($user)) : ?>
                    <table class='table table-bordered'>
                        <thead>
                            <tr>
                                <th>Materie</th>
                                <th>Nota</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subjectsArray as $key => $subject) : ?>
                                <?php
                                $grade = $gradesArray[$key];
                                ?>
                                <tr>
                                    <td><?= $subject ?></td>
                                    <td><?= $grade ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p>Utilizatorul nu are note.</p>
                <?php endif; ?>

                <?php $averageGrade = calculateAverage($user); ?>
                <p class="mt-4"><strong>Medie:</strong> <?= $averageGrade ?></p>
            </div>
        <?php else : ?>
            <p>Utilizatorul nu a fost gasit.</p>
        <?php endif; ?>
        <?php if (!empty($comments)) : ?>
            <div class="comments-section">
                <h2 class="mt-4" style="margin-bottom: 20px">Comentarii</h2>
                <div class="row">
                    <?php foreach ($comments as $comment) : ?>
                        <?php
                        $commenterProfilePicture = !empty($comment['commenter_profile']) ? "uploads/profile/{$comment['commenter_profile']}" : 'path_to_default_profile_picture.jpg';
                        ?>
                        <div class="col-md-12">
                            <div class="media mb-4">
                                <a href="user_profile_login.php?id=<?= $comment['commenter_id'] ?>">
                                    <img src="<?= $commenterProfilePicture ?>" alt="<?= $comment['commenter_username'] ?>" class="mr-3 rounded-circle" style="width: 50px; height: 50px;">
                                </a>
                                <div class="media-body">
                                    <a href="user_profile_login.php?id=<?= $comment['commenter_id'] ?>">
                                        <h5 class="mt-0"><?= $comment['commenter_username'] ?></h5>
                                    </a>
                                    <p><?= $comment['comment_text'] ?></p>
                                    <?php if (isset($comment['replies'])) : ?>
                                        <div class='comment-actions show-more' style='margin-top: 5px;'>
                                            <button class='show-more-btn btn btn-link btn-sm' data-target='replies-container-<?= $comment['id'] ?>'>Arata mai mult</button>
                                        </div>
                                        <div id='replies-container-<?= $comment['id'] ?>' class='replies-container' style='display: none;'>
                                            <?php displayNestedReplies($comment['replies'], $selectedUserId); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        </div>
                </div>
            <?php endif; ?>
            </div>
        </div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/tiny-slider/2.9.3/min/tiny-slider.js"></script>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const showMoreButtons = document.querySelectorAll(".show-more-btn");
                showMoreButtons.forEach((button) => {
                    button.addEventListener("click", function() {
                        console.log("Show more button clicked");
                        const target = this.getAttribute("data-target");
                        const repliesContainer = document.getElementById(target);
                        if (repliesContainer) {
                            if (repliesContainer.style.display === "none") {
                                repliesContainer.style.display = "block";
                                this.innerText = "Arata mai putin";
                            } else {
                                repliesContainer.style.display = "none";
                                this.innerText = "Arata mai mult";
                            }
                        }
                    });
                });
            });


            var slider = tns({
                container: '#slider',
                items: 1,
                slideBy: 'page',
                autoplay: true,
                controls: false,
                nav: false,
                autoplayButtonOutput: false,
                mouseDrag: true,
                responsive: {
                    640: {
                        edgePadding: 25,
                        gutter: 20,
                        items: 2
                    },
                    700: {
                        gutter: 30
                    },
                    900: {
                        items: 3
                    }
                }
            });

            var prevButton = document.querySelector('.slider-prev');
            var nextButton = document.querySelector('.slider-next');

            prevButton.addEventListener('click', function() {
                slider.goTo('prev');
            });

            nextButton.addEventListener('click', function() {
                slider.goTo('next');
            });
        </script>
        </body>

        </html>