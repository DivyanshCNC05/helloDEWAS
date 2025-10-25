<?php
require_once __DIR__ . '/auth.php';
ensure_logged_in();

$admin = $_SESSION['admin'];

// Database connection
$mysqli = require __DIR__ . '/db.php';

// Handle create request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $title = trim($_POST['title'] ?? '');
    $short_description = trim($_POST['short_description'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $links = trim($_POST['links'] ?? '');
    $categories = trim($_POST['categories'] ?? '');
    $view_count = (int)($_POST['view_count'] ?? 0);
    $published_at = trim($_POST['published_at'] ?? '');

    $uploadDir = __DIR__ . '/../uploads';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $main_image_path = null;
    if (!empty($_FILES['main_image']['name'])) {
        $tmp = $_FILES['main_image']['tmp_name'];
        $name = basename($_FILES['main_image']['name']);
        $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $name);
        $target = $uploadDir . '/' . time() . '_' . $safeName;
        if (move_uploaded_file($tmp, $target)) $main_image_path = 'uploads/' . basename($target);
    }

    $thumb_image_path = null;
    if (!empty($_FILES['thumbnail_image']['name'])) {
        $tmp = $_FILES['thumbnail_image']['tmp_name'];
        $name = basename($_FILES['thumbnail_image']['name']);
        $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $name);
        $target = $uploadDir . '/' . time() . '_thumb_' . $safeName;
        if (move_uploaded_file($tmp, $target)) $thumb_image_path = 'uploads/' . basename($target);
    }

    $stmt = $mysqli->prepare('INSERT INTO news (title, short_description, description, links, main_image, categories, thumbnail_image, view_count, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    if ($stmt) {
        $stmt->bind_param('sssssssis', $title, $short_description, $description, $links, $main_image_path, $categories, $thumb_image_path, $view_count, $published_at);
        $stmt->execute();
        $stmt->close();
    }

    header('Location: news-pannel.php');
    exit();
}

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete' && !empty($_POST['news_id'])) {
    $nid = (int)$_POST['news_id'];
    $stmt = $mysqli->prepare('DELETE FROM news WHERE news_id = ? LIMIT 1');
    if ($stmt) {
        $stmt->bind_param('i', $nid);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: news-pannel.php');
    exit();
}

// Handle update request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update' && !empty($_POST['news_id'])) {
    $nid = (int)$_POST['news_id'];
    $title = trim($_POST['title'] ?? '');
    $short_description = trim($_POST['short_description'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $links = trim($_POST['links'] ?? '');
    $categories = trim($_POST['categories'] ?? '');
    $view_count = (int)($_POST['view_count'] ?? 0);
    $published_at = trim($_POST['published_at'] ?? '');

    $existing_main = trim($_POST['existing_main_image'] ?? '');
    $existing_thumb = trim($_POST['existing_thumbnail_image'] ?? '');

    $uploadDir = __DIR__ . '/../uploads';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $main_image_path = $existing_main;
    if (!empty($_FILES['main_image']['name'])) {
        $tmp = $_FILES['main_image']['tmp_name'];
        $name = basename($_FILES['main_image']['name']);
        $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $name);
        $target = $uploadDir . '/' . time() . '_' . $safeName;
        if (move_uploaded_file($tmp, $target)) $main_image_path = 'uploads/' . basename($target);
    }

    $thumb_image_path = $existing_thumb;
    if (!empty($_FILES['thumbnail_image']['name'])) {
        $tmp = $_FILES['thumbnail_image']['tmp_name'];
        $name = basename($_FILES['thumbnail_image']['name']);
        $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $name);
        $target = $uploadDir . '/' . time() . '_thumb_' . $safeName;
        if (move_uploaded_file($tmp, $target)) $thumb_image_path = 'uploads/' . basename($target);
    }

    $stmt = $mysqli->prepare('UPDATE news SET title=?, short_description=?, description=?, links=?, main_image=?, categories=?, thumbnail_image=?, view_count=?, published_at=? WHERE news_id=? LIMIT 1');
    if ($stmt) {
        $stmt->bind_param('sssssssisi', $title, $short_description, $description, $links, $main_image_path, $categories, $thumb_image_path, $view_count, $published_at, $nid);
        $stmt->execute();
        $stmt->close();
    }

    header('Location: news-pannel.php');
    exit();
}

// Fetch all news
$newsRows = [];
$res = $mysqli->query('SELECT news_id, title, short_description, description, links, main_image, categories, thumbnail_image, view_count, published_at FROM news ORDER BY published_at DESC');
if ($res) while ($r = $res->fetch_assoc()) $newsRows[] = $r;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .cust-type {padding:4%; background: rgba(255,255,255,0.2); border-radius:16px; box-shadow:0 4px 30px rgba(0,0,0,0.1); backdrop-filter: blur(11.5px); border:1px solid rgba(255,255,255,0.3);}
        body {background-color:#132e51;}
        .cust-hd-logo {max-width:80%; height:auto;}
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-2 text-center text-white">
            <img src="../assets/logo.hd.webp" class="cust-hd-logo img-fluid">
            <div class="py-4"><a href="./news-pannel.php" class="text-white text-decoration-none"><h4 class="cust-type">News Management</h4></a></div>
            <div class="py-4"><a href="./event-pannel.php" class="text-white text-decoration-none"><h4 class="cust-type">Event Management</h4></a></div>
            <div class="py-4"><a href="./newspaper-pannel.php" class="text-white text-decoration-none"><h4 class="cust-type">Newspaper Management</h4></a></div>
            <div class="py-4"><a href="./banner-pannel.php" class="text-white text-decoration-none"><h4 class="cust-type">Banner Management</h4></a></div>
        </div>
        <div class="col-10" style="background-image:url('../assets/admin-bg.jpg');background-size:cover;background-repeat:no-repeat;">
            <button type="button" class="btn btn-outline-light mt-5" data-bs-toggle="modal" data-bs-target="#createModal"><h3 class="text-white">Post News</h3></button>
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card p-3">
                        <h4>Past News</h4>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead><tr><th>ID</th><th>Title</th><th>Published At</th><th>Edit</th><th>Views</th><th>Delete</th></tr></thead>
                                <tbody>
                                <?php foreach($newsRows as $row): ?>
                                    <tr>
                                        <td><?=htmlspecialchars($row['news_id'])?></td>
                                        <td><?=htmlspecialchars($row['title'])?></td>
                                        <td><?=htmlspecialchars($row['published_at'])?></td>
                                        <td><button class="btn btn-sm btn-primary edit-btn"
                                                    data-id="<?=$row['news_id']?>"
                                                    data-title="<?=htmlspecialchars($row['title'],ENT_QUOTES)?>"
                                                    data-short_description="<?=htmlspecialchars($row['short_description'] ?? '',ENT_QUOTES)?>"
                                                    data-description="<?=htmlspecialchars($row['description'] ?? '',ENT_QUOTES)?>"
                                                    data-links="<?=htmlspecialchars($row['links'] ?? '',ENT_QUOTES)?>"
                                                    data-main_image="<?=htmlspecialchars($row['main_image'] ?? '',ENT_QUOTES)?>"
                                                    data-categories="<?=htmlspecialchars($row['categories'] ?? '',ENT_QUOTES)?>"
                                                    data-thumbnail_image="<?=htmlspecialchars($row['thumbnail_image'] ?? '',ENT_QUOTES)?>"
                                                    data-published_at="<?=htmlspecialchars($row['published_at'],ENT_QUOTES)?>"
                                                    data-view_count="<?=$row['view_count']?>">Edit</button></td>
                                        <td><?=$row['view_count']?></td>
                                        <td>
                                            <form method="post" onsubmit="return confirm('Delete this news item?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="news_id" value="<?=$row['news_id']?>">
                                                <button class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach;?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Create Modal -->
            <div class="modal fade" id="createModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form method="post" enctype="multipart/form-data">
                            <div class="modal-header"><h5 class="modal-title">Post News</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                            <div class="modal-body">
                                <input type="hidden" name="action" value="create">
                                <div class="mb-3"><label>Title</label><input type="text" class="form-control" name="title" required></div>
                                <div class="mb-3"><label>Short Description</label><textarea class="form-control" name="short_description" rows="2"></textarea></div>
                                <div class="mb-3"><label>Description</label><textarea class="form-control" name="description" rows="6"></textarea></div>
                                <div class="mb-3"><label>Links</label><input type="text" class="form-control" name="links"></div>
                                <div class="mb-3"><label>Categories</label><input type="text" class="form-control" name="categories"></div>
                                <div class="row">
                                    <div class="col-md-6 mb-3"><label>Main Image</label><input type="file" class="form-control" name="main_image" accept="image/*"></div>
                                    <div class="col-md-6 mb-3"><label>Thumbnail Image</label><input type="file" class="form-control" name="thumbnail_image" accept="image/*"></div>
                                </div>
                                <div class="mb-3"><label>View Count</label><input type="number" class="form-control" name="view_count" value="0" min="0"></div>
                                <div class="mb-3"><label>Published At</label><input type="datetime-local" class="form-control" name="published_at"></div>
                            </div>
                            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-success">Post News</button></div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit Modal -->
            <div class="modal fade" id="editModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="post" id="editForm" enctype="multipart/form-data">
                            <div class="modal-header"><h5 class="modal-title">Edit News</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                            <div class="modal-body">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="news_id" id="modal-news-id">
                                <div class="mb-3"><label>Title</label><input type="text" class="form-control" name="title" id="modal-title" required></div>
                                <div class="mb-3"><label>Short Description</label><textarea class="form-control" name="short_description" id="modal-short-desc" rows="2"></textarea></div>
                                <div class="mb-3"><label>Description</label><textarea class="form-control" name="description" id="modal-desc" rows="6"></textarea></div>
                                <div class="mb-3"><label>Links</label><input type="text" class="form-control" name="links" id="modal-links"></div>
                                <div class="mb-3"><label>Categories</label><input type="text" class="form-control" name="categories" id="modal-categories"></div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label>Main Image</label>
                                        <div id="modal-main-image-preview" class="mb-2"></div>
                                        <input type="file" class="form-control" name="main_image" accept="image/*">
                                        <input type="hidden" name="existing_main_image" id="modal-existing-main">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Thumbnail Image</label>
                                        <div id="modal-thumb-image-preview" class="mb-2"></div>
                                        <input type="file" class="form-control" name="thumbnail_image" accept="image/*">
                                        <input type="hidden" name="existing_thumbnail_image" id="modal-existing-thumb">
                                    </div>
                                </div>
                                <div class="mb-3"><label>Published At</label><input type="datetime-local" class="form-control" name="published_at" id="modal-published-at"></div>
                                <div class="mb-3"><label>View Count</label><input type="number" class="form-control" name="view_count" id="modal-view-count" min="0"></div>
                            </div>
                            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Save changes</button></div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function(){
    const editModalEl = document.getElementById('editModal');
    if (!editModalEl) return;
    const editModal = new bootstrap.Modal(editModalEl);

    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function(){
            document.getElementById('modal-news-id').value = this.dataset.id;
            document.getElementById('modal-title').value = this.dataset.title || '';
            document.getElementById('modal-short-desc').value = this.dataset.short_description || '';
            document.getElementById('modal-desc').value = this.dataset.description || '';
            document.getElementById('modal-links').value = this.dataset.links || '';
            document.getElementById('modal-categories').value = this.dataset.categories || '';
            document.getElementById('modal-existing-main').value = this.dataset.main_image || '';
            document.getElementById('modal-existing-thumb').value = this.dataset.thumbnail_image || '';

            const mainPreview = document.getElementById('modal-main-image-preview');
            mainPreview.innerHTML = '';
            if (this.dataset.main_image) {
                const img = document.createElement('img');
                img.src = this.dataset.main_image;
                img.style.maxWidth = '100%';
                img.style.height = 'auto';
                mainPreview.appendChild(img);
            }

            const thumbPreview = document.getElementById('modal-thumb-image-preview');
            thumbPreview.innerHTML = '';
            if (this.dataset.thumbnail_image) {
                const img2 = document.createElement('img');
                img2.src = this.dataset.thumbnail_image;
                img2.style.maxWidth = '100%';
                img2.style.height = 'auto';
                thumbPreview.appendChild(img2);
            }

            let published_at = this.dataset.published_at || '';
            if (published_at) published_at = published_at.replace(' ', 'T');
            document.getElementById('modal-published-at').value = published_at;
            document.getElementById('modal-view-count').value = this.dataset.view_count || 0;

            editModal.show();
        });
    });
})();
</script>
</body>
</html>