<?php
include "includes/init.php";

$user_id = $_SESSION['id'];

$isAdmin = isUserAdmin($db, $_SESSION['id']);

if ($user_id != $_GET['id'] && !$isAdmin) {
    die("Nu ai acces la aceasta pagina!");
}


function getPhotosByUserId($conn, $user_id)
{
    $sql = "SELECT * FROM photos WHERE user_id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function deletePhoto($conn, $user_id, $photo_id)
{
    $sql = "SELECT photo_name FROM photos WHERE id = :photo_id AND user_id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['photo_id' => $photo_id, 'user_id' => $user_id]);
    $photo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($photo) {
        $upload_dir = 'uploads/proiecte/';
        $photo_name = $photo['photo_name'];

        $photo_path = $upload_dir . $photo_name;
        if (file_exists($photo_path)) {
            unlink($photo_path);
        }

        $sql = "DELETE FROM photos WHERE id = :photo_id AND user_id = :user_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['photo_id' => $photo_id, 'user_id' => $user_id]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        $user_id = $_SESSION['id'];
        $photo_id = $_POST['photo_id'];
        deletePhoto($db, $user_id, $photo_id);
    }
}

$userName = getUserNameById($db, $user_id);
$photos = getPhotosByUserId($db, $user_id);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Galerie</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="plupload1/js/jquery.plupload.queue/css/jquery.plupload.queue.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/js/bootstrap.min.js"></script>

    <style>
        .container {
            margin-top: 30px;
        }

        .file-upload-container {
            border: 1px solid #ddd;
            padding: 20px;
            margin-bottom: 20px;
        }

        .file-list {
            margin-top: 10px;
        }

        .file-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .file-thumbnail {
            width: 50px;
            height: 50px;
            object-fit: cover;
            margin-right: 10px;
        }

        .file-name {
            flex-grow: 1;
        }

        .file-delete {
            margin-left: 10px;
        }

        .progress {
            height: 30px;
            line-height: 30px;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
            margin-top: 10px;
        }

        .progress-bar {
            background-color: #198754;
            color: #fff;
            text-align: center;
        }

        .status-response {
            margin-top: 10px;
        }

        .custom-file {
            overflow: hidden;
        }

        .custom-file-label {
            cursor: pointer;
            padding: 10px 15px;
            background-color: #007bff;
            color: #fff;
            border-radius: 5px;
            transition: background-color 0.2s ease-in-out;
        }

        .custom-file-input:focus~.custom-file-label {
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .custom-file-input {
            visibility: hidden;
            width: 1px;
            height: 1px;
            opacity: 0;
            position: absolute;
        }

        .modal-body {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            padding: 10px;
        }

        .modal-body img {
            width: 100%;
            height: auto;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <?php include "includes/nav.php"; ?>

    <div class="container">
        <h2>Galeria utilizatorului: <?= $userName; ?></h2>
        <div class="file-upload-container">
            <div id="statusResponse" class="status-response"></div>
            <div class="form-group">
                <a href="#" class="btn btn-primary" id="selectFileBtn"><b>Select File</b></a>
            </div>
            <div class="file-list" id="fileList"></div>
            <div class="form-group">
                <a id="uploadBtn" href="javascript:;" class="btn btn-success">Upload</a>
            </div>
            <div class="progress">
                <div class="progress-bar" style="width: 0;"></div>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <div class="row">
            <?php foreach ($photos as $photo) : ?>
                <div class="col-md-4">
                    <div class="file-item">
                        <img src="uploads/proiecte/<?= $photo['photo_name']; ?>" alt="Photo" class="file-thumbnail img-fluid">
                        <div class="file-name"><?= $photo['photo_name']; ?></div>
                        <form method="post" action="user_galerie.php?id=<?= $user_id; ?>" class="mt-2">
                            <input type="hidden" name="photo_id" value="<?= $photo['id']; ?>">
                            <input type="submit" class="btn btn-danger file-delete" name="delete" value="Sterge">
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>


    <div class="modal fade" id="uiWidgetModal" tabindex="-1" role="dialog" aria-labelledby="uiWidgetModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uiWidgetModalLabel">Selected Files</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="thumbsContainer" class="d-flex flex-wrap justify-content-center"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <a href="javascript:;" class="btn btn-success" id="uploadBtnModal">Upload</a>
                </div>
            </div>
        </div>
    </div>
    <script src="plupload1/js/moxie.min.js"></script>
    <script src="plupload1/js/plupload.full.min.js"></script>
    <script src="plupload1/js/jquery.plupload.queue/jquery.plupload.queue.min.js"></script>
    <script src="plupload1/js/jquery.ui.plupload/jquery.ui.plupload.min.js"></script>
    <script>
        $(function() {
            var uploader = new plupload.Uploader({
                runtimes: "html5,flash,silverlight,html4",
                browse_button: "selectFileBtn",
                url: "upload.php",
                flash_swf_url: "plupload1/js/Moxie.swf",
                silverlight_xap_url: "plupload1/js/Moxie.xap",
                multi_selection: true,
                dragdrop: true,

                views: {
                    list: true,
                    thumbs: true, 
                    active: 'thumbs' 
                },

                filters: {
                    max_file_size: "500mb",
                    mime_types: [{
                            title: "Image files",
                            extensions: "jpg,jpeg,gif,png"
                        },
                        {
                            title: "Video files",
                            extensions: "mp4,avi,mpeg,mpg,mov,wmv"
                        },
                        {
                            title: "Zip files",
                            extensions: "zip"
                        },
                        {
                            title: "Document files",
                            extensions: "pdf,docx,xlsx"
                        },
                    ],
                },

                init: {
                    PostInit: function() {
                        document.getElementById("fileList").innerHTML = "";

                        document.getElementById("uploadBtn").onclick = function() {
                            if (uploader.files.length < 1) {
                                document.getElementById("statusResponse").innerHTML =
                                    '<p style="color:#EA4335;">Please select a file to upload.</p>';
                                return false;
                            } else {
                                uploader.start();
                                return false;
                            }
                        };
                    },

                    FilesAdded: function(up, files) {
                        document.getElementById("fileList").innerHTML = "";
                        var thumbsContainer = document.getElementById("thumbsContainer");
                        thumbsContainer.innerHTML = ""; 

                        plupload.each(files, function(file) {
                            document.getElementById("fileList").innerHTML +=
                                '<div id="' +
                                file.id +
                                '">' +
                                file.name +
                                " (" +
                                plupload.formatSize(file.size) +
                                ") <b></b></div>";

                            var thumbImg = new Image();
                            thumbImg.onload = function() {
                                URL.revokeObjectURL(this.src); 
                            };

                            var reader = new FileReader();
                            reader.onload = function(event) {
                                thumbImg.src = event.target.result;
                            };
                            reader.readAsDataURL(file.getSource().getSource()); 

                            thumbImg.alt = file.name;
                            thumbImg.classList.add("thumb");
                            thumbsContainer.appendChild(thumbImg);
                        });

                        $("#uiWidgetModal").modal("show");
                    },


                    UploadProgress: function(up, file) {
                        document
                            .getElementById(file.id)
                            .getElementsByTagName("b")[0].innerHTML =
                            "<span>" + file.percent + "%</span>";
                        document.querySelector(".progress-bar").style.width = file.percent + "%";
                    },

                    FileUploaded: function(up, file, result) {
                        var responseData = result.response.replace('"{', "{").replace('}"', "}");
                        var objResponse = JSON.parse(responseData);
                        document.getElementById("statusResponse").innerHTML =
                            '<p style="color:#198754;">' + objResponse.result.message + "</p>";

                        $("#uiWidgetModal").modal("hide");
                    },

                    Error: function(up, err) {
                        document.getElementById("statusResponse").innerHTML =
                            '<p style="color:#EA4335;">Error #' +
                            err.code +
                            ": " +
                            err.message +
                            "</p>";
                    },
                    DragDrop: function(up) {
                        var container = document.querySelector(".file-upload-container");

                        container.addEventListener("dragover", function(e) {
                            e.preventDefault();
                            container.classList.add("dragover");
                        });

                        container.addEventListener("dragleave", function(e) {
                            e.preventDefault();
                            container.classList.remove("dragover");
                        });

                        container.addEventListener("drop", function(e) {
                            e.preventDefault();
                            container.classList.remove("dragover");
                            uploader.addFile(e.dataTransfer.files);
                        });
                    },
                },

            });



            uploader.init();

            $("#uiWidgetModal").on("hidden.bs.modal", function() {
                uploader.splice(); 
            });

            $("#uploadBtnModal").on("click", function() {
                if (uploader.files.length < 1) {
                    document.getElementById("statusResponse").innerHTML =
                        '<p style="color:#EA4335;">Please select a file to upload.</p>';
                    return false;
                } else {
                    uploader.start();
                    return false;
                }
            });
        });
    </script>


</body>

</html>