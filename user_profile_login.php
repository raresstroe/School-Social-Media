<?php
require "config.php";
include "includes/functions.php";
$db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (isset($_GET['id'])) {
    $selectedUserId = $_GET['id'];
    $user = getUserByIdNot($db, $selectedUserId);
    $comments = getCommentsByUserId($db, $selectedUserId);

    $user = getUserByIdNot($db, $selectedUserId);
    $averageRating = round($user[0]['average_rating'], 2);

    // print_r($user);
    // print_r($averageRating);
}
// print_r($comments);
?>

<?php include "includes/header.php"; ?>

<style>
    .slider-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 10px;
    }

    .slider-prev,
    .slider-next {
        font-size: 24px;
        background-color: #f0f0f0;
        padding: 5px 10px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .slider-prev:hover,
    .slider-next:hover {
        background-color: #ddd;
    }

    .comment-reply {
        margin-left: 30px;
    }

    .show-more {
        margin-top: 5px;
    }

    .replies-container {
        display: none;
    }
</style>

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
                    <?php
                    $fullStars = floor($averageRating);
                    $halfStar = ($averageRating - $fullStars >= 0.5) ? 1 : 0;
                    for ($i = 0; $i < $fullStars; $i++) : ?>
                        <i class="fa fa-star" aria-hidden="true"></i>
                    <?php endfor; ?>
                    <?php if ($halfStar) : ?>
                        <i class="fa fa-star-half" aria-hidden="true"></i>
                    <?php endif; ?>
                <?php else : ?>
                    <p>Acest utilizator nu are inca rating</p>
                <?php endif ?>
            </div>

            <div class="col-md-8">
                <?php if (!empty($user[0]['photos'])) : ?>
                    <h2 class="mb-4">Galerie</h2>
                    <div class="slider-controls">
                        <div id="slider" class="slider owl-carousel">
                            <?php $index = 0; ?>
                            <?php while ($index < count($photosArray)) : ?>
                                <div class="item">
                                    <img src="uploads/proiecte/<?= $photosArray[$index] ?>" alt="Poza" class="img-fluid slider-img">
                                </div>
                                <?php $index++; ?>
                            <?php endwhile; ?>
                        </div>
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
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
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


            $(document).ready(function() {
                var owl = $('#slider');
                owl.owlCarousel({
                    items: 1,
                    loop: true,
                    margin: 10,
                    autoHeight: true,
                    autoplay: true,
                    autoplayTimeout: 5000,
                    autoplayHoverPause: true,
                    nav: true,
                    dots: true,
                    responsive: {
                        640: {
                            items: 1
                        },
                        700: {
                            items: 1
                        },
                        1000: {
                            items: 1
                        }
                    }
                });
            });
        </script>
        </body>

        </html>