<?php
include "includes/init.php";

// print_r($_SESSION);

if (isset($_GET['id'])) {
    $selectedUserId = $_GET['id'];
    $user = getUserById($db, $selectedUserId);
    $comments = getCommentsByUserId($db, $selectedUserId);
    // print_r($user[0]['photos']);
    // var_dump($comments);

    $counter = 0;
    if (isset($_SESSION['id'])) {
        $userId = $_SESSION['id'];
        $sql = "SELECT value FROM ratings WHERE user_id = :user_id AND profile_id = :profile_id";
        $stmt = $db->prepare($sql);
        $stmt->execute(['user_id' => $userId, 'profile_id' => $selectedUserId]);
        $counter = $stmt->rowCount();
    }

    $sql = "SELECT value FROM ratings WHERE user_id = :user_id AND profile_id = :profile_id";
    $stmt = $db->prepare($sql);
    $stmt->execute(['user_id' => $userId, 'profile_id' => $selectedUserId]);
    $userRating = $stmt->fetchColumn();

    $user = getUserById($db, $selectedUserId);
    $averageRating = round($user[0]['average_rating'], 2);


    // print_r($user);
    // print_r($averageRating);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['comment'])) {
        $commentText = $_POST['comment'];
        $profileUserId = $_POST['profile_user_id'];

        $sql = "INSERT INTO comments (commenter_id, profile_user_id, comment_text) VALUES (:commenter_id, :profile_user_id, :comment_text)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'commenter_id' => $_SESSION['id'],
            'profile_user_id' => $profileUserId,
            'comment_text' => $commentText,
        ]);

        header("Location: user_profile.php?id=$profileUserId");
        exit();
    }
    if (isset($_POST['rating'])) {
        $rating = $_POST['rating'];
        $profileUserId = $_POST['profile_user_id'];

        $sql = "INSERT INTO ratings (user_id, profile_id, value) VALUES (:user_id, :profile_id, :value)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'user_id' => $_SESSION['id'],
            'profile_id' => $profileUserId,
            'value' => $rating,
        ]);

        header("Location: user_profile.php?id=$profileUserId");
        exit();
    }
}

// print_r($comments);
?>

<?php include "includes/header.php"; ?>
<?php include "includes/nav.php"; ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tiny-slider/2.9.3/tiny-slider.css">

<style>

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
        $photosArray = array();
        foreach ($user[0]['photos'] as $photo) {
            $photosArray[] = $photo['photos'];
        }
        // print_r($photosArray);
        ?>

        <h2 class='mb-4'>Profil </h2>
        <?php if ($_SESSION['id'] == $userId) : ?>
            <a href="user_galerie.php?id=<?= $userId ?>" class="btn btn-primary">Galerie</a>
            <a href="update_user.php?id=<?= $userId ?>" class="btn btn-primary mr-2">Edit</a>

        <?php endif; ?>
        <p></p>
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
            <?php // print_r($photo) 
            ?>
            <?php // print_r($photosArray) 
            ?>
            <div class="col-md-8">
                <?php if (!empty($user[0]['photos'])) : ?>
                    <h2 class="mb-4">Galerie</h2>
                    <div class="slider-controls">
                        <button class="slider-prev">&lt;</button>
                        <div id="slider" class="slider">
                            <?php foreach ($photosArray as $photo) : ?>
                                <a href="user_photo.php?photo=<?= urlencode($photo) ?>">
                                    <img src="uploads/proiecte/<?= $photo ?>" alt="Poza" class="img-fluid">
                                </a>
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

        <?php
        $user_id = $_GET['id'];
        if ($user_id != $_SESSION['id']) : ?>
            <div class="container mt-5">
                <h2 class="mt-4" style="margin-bottom: 40px">Adauga utilizatorului un rating</h2>
                <?php if ($counter === 0) : ?>
                    <form method="post" action="user_profile.php" style="margin-top:30px">
                        <div class="form-group">
                            <label for="rating">Adauga un rating (Intre 1 si 5):</label>
                            <input type="number" class="form-control" name="rating" id="rating" max="5" min="1" required></textarea>
                        </div>
                        <input type="hidden" name="profile_user_id" value="<?= $selectedUserId ?>">
                        <button type="submit" class="btn btn-primary">Send</button>
                    </form>
                <?php else : ?>
                    <p>Ai acordat deja rating-ul de <?= $userRating ?> stele.</p>
                    <a href="update_rating.php?id=<?= $selectedUserId ?>">Poti edita rating-ul aici</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>


        <div class="container mt-5">
            <div class="comment-form">
                <form method="post" action="user_profile.php" style="margin-top:20px">
                    <div class="form-group">
                        <label for="comment">Adauga un comentariu (maxim 400 de caractere):</label>
                        <textarea class="form-control" name="comment" id="comment" rows="4" maxlength="400" required></textarea>
                    </div>
                    <input type="hidden" name="profile_user_id" value="<?= $selectedUserId ?>">
                    <button type="submit" class="btn btn-primary" style="margin-bottom:20px">Send</button>
                </form>
            </div>

            <?php if (!empty($comments)) : ?>
                <div class="comments-section">
                    <h2 class="mt-4" style="margin-bottom: 20px">Comentarii</h2>
                    <div class="row">
                        <?php foreach ($comments as $comment) : ?>
                            <?php
                            $commenterProfilePicture = !empty($comment['commenter_profile']) ? "uploads/profile/{$comment['commenter_profile']}" : '';
                            $isCurrentUserComment = ($_SESSION['id'] == $comment['commenter_id']);
                            ?>
                            <div class="col-md-12">
                                <div class="media mb-4">
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
                                                <?php if ($isCurrentUserComment || $_SESSION['admin'] == true) : ?>
                                                    <a class="delete-profile" data-id="<?= $comment['id']; ?>">Sterge</a> |
                                                <?php endif; ?>
                                                <a class="reply-profile" data-id="<?= $comment['id']; ?>" data-profile-id="<?= $selectedUserId; ?>">Reply</a>
                                            </div>
                                        <?php else : ?>
                                            <div class="comment-actions">
                                                <a class="reply-profile" data-id="<?= $comment['id']; ?>" data-profile-id="<?= $selectedUserId; ?>">Reply</a>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (isset($comment['replies'])) : ?>

                                            <div class='comment-actions show-more' style='margin-top: 5px;'>
                                                <button class='show-more-btn btn btn-link btn-sm' data-target='replies-container-<?= $comment['id'] ?>'>Arata mai mult</button>
                                            </div>
                                            <div id='replies-container-<?= $comment['id'] ?>' class='replies-container' style='display: none;'>
                                                <?php displayNestedRepliesProfile($comment['replies'], $selectedUserId); ?>
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
        <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Update Comentariu</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="replyModal" tabindex="-1" role="dialog" aria-labelledby="replyModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="replyModalLabel">Raspunde la un comentariu</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                    </div>
                </div>
            </div>
        </div>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/tiny-slider/2.9.3/min/tiny-slider.js"></script>
        <script>
            document.querySelectorAll('.reply-profile').forEach(function(link) {
                link.addEventListener('click', function(event) {
                    event.preventDefault();
                    var commentId = this.getAttribute('data-id');
                    var profileId = this.getAttribute('data-profile-id');
                    $.ajax({
                        url: 'reply_comment.php?parent_id=' + commentId + '&profile_id=' + profileId,
                        type: 'GET',
                        success: function(data) {
                            document.querySelector('#replyModal .modal-body').innerHTML = data;
                            $('#replyModal').modal('show');
                        }
                    });
                });
            });
            document.querySelectorAll('.edit-profile').forEach(function(link) {
                link.addEventListener('click', function(event) {
                    event.preventDefault();
                    var commentId = this.getAttribute('data-id');
                    $.ajax({
                        url: 'update_comment.php?id=' + commentId,
                        type: 'GET',
                        success: function(data) {
                            document.querySelector('#editModal .modal-body').innerHTML = data;
                            $('#editModal').modal('show');
                        }
                    });
                });
            });
            document.addEventListener("DOMContentLoaded", function() {
                const showMoreButtons = document.querySelectorAll(".show-more-btn");
                showMoreButtons.forEach((button) => {
                    button.addEventListener("click", function() {
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

            $(document).ready(function() {
                $('.delete-profile').click(function() {
                    var id = $(this).data('id');

                    var confirmDelete = confirm('Esti sigur ca vrei sa stergi acest comentariu?');
                    if (confirmDelete) {
                        $.ajax({
                            url: 'delete.php',
                            type: 'POST',
                            data: {
                                id: id,
                                type: 'comment',
                            },
                            success: function(response) {
                                if (response == 'success') {
                                    alert('Comentariul sters cu succes');
                                    location.reload();
                                } else {
                                    alert('Comentariul nu a putut fi sters');
                                }
                            }
                        });
                    }
                });
            });
        </script>
        </body>

        </html>