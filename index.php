<?php
include "includes/init.php";


$photos = getPhotos($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['photo_id']) && isset($_POST['is_liked'])) {
        $photoId = $_POST['photo_id'];
        $isLiked = $_POST['is_liked'];

        updateLikeStatus($photoId, $isLiked);

        echo "Success";
        exit();
    }
}

// print_r($photos);
?>

<?php include "includes/header.php" ?>
<?php include "includes/nav.php" ?>

<?php foreach ($photos as $photo) : ?>
    <?php if (!empty($photo['photo_name'])) : ?>
        <div class="container-index">
            <div class="Instagram-card">
                <div class="Instagram-card-header">
                    <img src="<?php echo "uploads/profile/" . $photo['profile']; ?>" class="Instagram-card-user-image">
                    <a class="Instagram-card-user-name hyper-index" href="<?php echo "http://localhost/user_profile.php?id=" . $photo['user_id']; ?>"> <?= $photo['username'] ?> </a>
                </div>

                <div class="Instagram-card-image" style="margin-top: 25px;">
                    <!-- <img src="<?php echo "uploads/proiecte/" . $photo['photo_name']; ?>" height="600px" width="600px" /> -->
                    <img src="<?php echo "uploads/proiecte/" . $photo['photo_name']; ?>" />
                </div>
                <div class="like-section">
                    <div class="like-button" data-photo-id="<?= $photo['id'] ?>" data-liked="<?= boolval($photo['liked']) ?>">
                        <?php if ($photo['liked']) : ?>
                            <i class="fas fa-heart" style="color: red;"></i>
                        <?php else : ?>
                            <i class="far fa-heart"></i>
                        <?php endif; ?>
                    </div>
                    <div class="like-count"><?= $photo['like_count'] ?></div>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endforeach; ?>

<script>
    const likeButtons = document.querySelectorAll('.like-button');

    likeButtons.forEach((button) => {
        button.addEventListener('click', function() {
            const photoId = this.getAttribute('data-photo-id');
            const isLiked = this.getAttribute('data-liked') === '1';
            const url = 'like_photo.php';

            fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `photo_id=${photoId}&is_liked=${isLiked ? '0' : '1'}`,
                })
                .then(response => response.text())
                .then(data => {
                    console.log(data);
                    const responseData = JSON.parse(data);

                    const icon = button.querySelector('i');
                    if (isLiked) {
                        icon.className = 'far fa-heart';
                        icon.style.color = '';
                    } else {
                        icon.className = 'fas fa-heart';
                        icon.style.color = 'red';
                    }
                    button.setAttribute('data-liked', isLiked ? '0' : '1');

                    const likeCount = button.nextElementSibling;
                    likeCount.textContent = responseData.like_count;
                });
        });
    });
</script>

</body>

</html>