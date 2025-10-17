<?php
require_once __DIR__ . '/auth.php';
// require_once __DIR__ . '/db.php';
ensure_logged_in();

$admin = $_SESSION['admin'];

// database connection
$mysqli = require __DIR__ . '/db.php';

// Handle create request (Post News)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $title = trim($_POST['title'] ?? '');
    $short_description = trim($_POST['short_description'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $links = trim($_POST['links'] ?? '');
    $categories = trim($_POST['categories'] ?? '');
    $view_count = (int)($_POST['view_count'] ?? 0);
    $published_at = trim($_POST['published_at'] ?? '');

    // Handle file uploads (optional)
    $uploadDir = __DIR__ . '/../uploads';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0755, true);
    }

    $main_image_path = null;
    if (!empty($_FILES['main_image']['name'])) {
        $tmp = $_FILES['main_image']['tmp_name'];
        $name = basename($_FILES['main_image']['name']);
        $target = $uploadDir . '/' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $name);
        if (@move_uploaded_file($tmp, $target)) {
            $main_image_path = 'uploads/' . basename($target);
        }
    }

    $thumb_image_path = null;
    if (!empty($_FILES['thumbnail_image']['name'])) {
        $tmp = $_FILES['thumbnail_image']['tmp_name'];
        $name = basename($_FILES['thumbnail_image']['name']);
        $target = $uploadDir . '/' . time() . '_thumb_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $name);
        if (@move_uploaded_file($tmp, $target)) {
            $thumb_image_path = 'uploads/' . basename($target);
        }
    }

    $stmt = $mysqli->prepare('INSERT INTO news (title, short_description, description, links, main_image, categories, thumbnail_image, view_count, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    if ($stmt) {
        $stmt->bind_param('sssssssds', $title, $short_description, $description, $links, $main_image_path, $categories, $thumb_image_path, $view_count, $published_at);
        // Correction: bind_param types must match; published_at is string -> use 's' instead of 'd'
        // We'll re-prepare with correct types below to avoid warnings
        $stmt->close();
        $stmt = $mysqli->prepare('INSERT INTO news (title, short_description, description, links, main_image, categories, thumbnail_image, view_count, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        if ($stmt) {
            $stmt->bind_param('sssssssis', $title, $short_description, $description, $links, $main_image_path, $categories, $thumb_image_path, $view_count, $published_at);
            $stmt->execute();
        }
    }

    header('Location: news-pannel.php');
    exit();
}

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && !empty($_POST['news_id'])) {
    $nid = (int)$_POST['news_id'];
    $stmt = $mysqli->prepare('DELETE FROM news WHERE news_id = ? LIMIT 1');
    if ($stmt) {
        $stmt->bind_param('i', $nid);
        $stmt->execute();
    }
    header('Location: news-pannel.php');
    exit();
}

// Handle update request from modal (update all fields, handle optional image replacement)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update' && !empty($_POST['news_id'])) {
    $nid = (int)$_POST['news_id'];
    $title = trim($_POST['title'] ?? '');
    $short_description = trim($_POST['short_description'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $links = trim($_POST['links'] ?? '');
    $categories = trim($_POST['categories'] ?? '');
    $view_count = (int)($_POST['view_count'] ?? 0);
    $published_at = trim($_POST['published_at'] ?? '');

    // existing image paths (if any)
    $existing_main = trim($_POST['existing_main_image'] ?? '');
    $existing_thumb = trim($_POST['existing_thumbnail_image'] ?? '');

    // Handle optional file uploads
    $uploadDir = __DIR__ . '/../uploads';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0755, true);
    }

    $main_image_path = $existing_main ?: null;
    if (!empty($_FILES['main_image']['name'])) {
        $tmp = $_FILES['main_image']['tmp_name'];
        $name = basename($_FILES['main_image']['name']);
        $target = $uploadDir . '/' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $name);
        if (@move_uploaded_file($tmp, $target)) {
            $main_image_path = 'uploads/' . basename($target);
        }
    }

    $thumb_image_path = $existing_thumb ?: null;
    if (!empty($_FILES['thumbnail_image']['name'])) {
        $tmp = $_FILES['thumbnail_image']['tmp_name'];
        $name = basename($_FILES['thumbnail_image']['name']);
        $target = $uploadDir . '/' . time() . '_thumb_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $name);
        if (@move_uploaded_file($tmp, $target)) {
            $thumb_image_path = 'uploads/' . basename($target);
        }
    }

    // Prepare and execute update with correct types
    $stmt = $mysqli->prepare('UPDATE news SET title = ?, short_description = ?, description = ?, links = ?, main_image = ?, categories = ?, thumbnail_image = ?, view_count = ?, published_at = ? WHERE news_id = ? LIMIT 1');
    if ($stmt) {
        // types: s s s s s s s i s -> 'sssssssisi'
        $stmt->bind_param('sssssssisi', $title, $short_description, $description, $links, $main_image_path, $categories, $thumb_image_path, $view_count, $published_at, $nid);
        $stmt->execute();
    }
    header('Location: news-pannel.php');
    exit();
}

// Fetch all news (select all columns used by the app)
$newsRows = [];
$res = $mysqli->query('SELECT news_id, title, short_description, description, links, main_image, categories, thumbnail_image, view_count, published_at FROM news ORDER BY published_at DESC');
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $newsRows[] = $r;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <style>
        .cust-type{
            padding-top: 4%;
            padding-bottom: 4%;
background: rgba(255, 255, 255, 0.2);
border-radius: 16px;
box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
backdrop-filter: blur(11.5px);
-webkit-backdrop-filter: blur(11.5px);
border: 1px solid rgba(255, 255, 255, 0.3);
            
        }

        .post-button{
            background-color:white;
        }

        body{
            background-color: #132e51;
        }


          @media only screen   
  and (min-device-width : 320px)   
  and (max-device-width : 640px)  
  { }






  @media only screen   
  and (min-width: 1030px)   
  and (max-width: 1605px)  
  { 
    .cust-hd-logo{
        height: 40%;
        width: 80%;
    }
  }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-2 cust-col-1">
                <center>
                    <img src="../assets/logo.hd.webp" alt="" class="cust-hd-logo img-fluid">
                </center>
                <div class="row py-4 px-4 text-center text-white">
                    <h4 class="cust-type">News Management</h4>
                </div>
                <div class="row py-4 px-4 text-center text-white">
                    <h4 class="cust-type">Event Management</h4>
                </div>
                <div class="row py-4 px-4 text-center text-white">
                    <h4 class="cust-type">Newspaper management</h4>
                </div>
                <div class="row py-4 px-4 text-center text-white">
                    <h4 class="cust-type">Banner Management</h4>
                </div>
            </div>
            <div class="col-10" style=" height: 100vh; background-image: url('../assets/admin-bg.jpg'); background-size: cover; background-repeat: no-repeat;">

                    <button type="button" class="btn btn-outline-light mt-5" data-bs-toggle="modal" data-bs-target="#createModal">
                        <span class="text-decoration-none text-white">
                            <h3 class="">Post News</h3>
                        </span>
                    </button>


                <div class="row">
                    <!-- table of past news  -->
                    <div class="col-12 mt-4">
                        <div class="card p-3">
                            <h4>Past News</h4>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered" id="news-table">
                                    <thead>
                                        <tr>
                                            <th>news_id</th>
                                            <th>title</th>
                                            <th>published_at</th>
                                            <th>edit</th>
                                            <th>view_count</th>
                                            <th>delete</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($newsRows as $row): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['news_id']); ?></td>
                                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                                <td><?php echo htmlspecialchars($row['published_at']); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary edit-btn"
                                                        data-id="<?php echo $row['news_id']; ?>"
                                                        data-title="<?php echo htmlspecialchars($row['title'], ENT_QUOTES); ?>"
                                                        data-short_description="<?php echo htmlspecialchars($row['short_description'] ?? '', ENT_QUOTES); ?>"
                                                        data-description="<?php echo htmlspecialchars($row['description'] ?? '', ENT_QUOTES); ?>"
                                                        data-links="<?php echo htmlspecialchars($row['links'] ?? '', ENT_QUOTES); ?>"
                                                        data-main_image="<?php echo htmlspecialchars($row['main_image'] ?? '', ENT_QUOTES); ?>"
                                                        data-categories="<?php echo htmlspecialchars($row['categories'] ?? '', ENT_QUOTES); ?>"
                                                        data-thumbnail_image="<?php echo htmlspecialchars($row['thumbnail_image'] ?? '', ENT_QUOTES); ?>"
                                                        data-published_at="<?php echo htmlspecialchars($row['published_at'], ENT_QUOTES); ?>"
                                                        data-view_count="<?php echo (int)$row['view_count']; ?>">
                                                        Edit
                                                    </button>
                                                </td>
                                                <td><?php echo (int)$row['view_count']; ?></td>
                                                <td>
                                                    <form method="post" onsubmit="return confirm('Delete this news item?');" style="display:inline">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="news_id" value="<?php echo (int)$row['news_id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Create Modal -->
                    <div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <form method="post" enctype="multipart/form-data">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Post News</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="action" value="create">
                                        <div class="mb-3">
                                            <label class="form-label">Title</label>
                                            <input type="text" class="form-control" name="title" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Short Description</label>
                                            <textarea class="form-control" name="short_description" rows="2"></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Description</label>
                                            <textarea class="form-control" name="description" rows="6"></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Links (comma separated)</label>
                                            <input type="text" class="form-control" name="links">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Categories (comma separated)</label>
                                            <input type="text" class="form-control" name="categories">
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Main Image</label>
                                                <input type="file" class="form-control" name="main_image" accept="image/*">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Thumbnail Image</label>
                                                <input type="file" class="form-control" name="thumbnail_image" accept="image/*">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">View Count</label>
                                            <input type="number" class="form-control" name="view_count" value="0" min="0">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Published At</label>
                                            <input type="datetime-local" class="form-control" name="published_at">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-success">Post News</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="post" id="editForm" enctype="multipart/form-data">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit News</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="news_id" id="modal-news-id">
                                        <div class="mb-3">
                                            <label class="form-label">Title</label>
                                            <input type="text" class="form-control" name="title" id="modal-title" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Short Description</label>
                                            <textarea class="form-control" name="short_description" id="modal-short-desc" rows="2"></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Description</label>
                                            <textarea class="form-control" name="description" id="modal-desc" rows="6"></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Links (comma separated)</label>
                                            <input type="text" class="form-control" name="links" id="modal-links">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Categories (comma separated)</label>
                                            <input type="text" class="form-control" name="categories" id="modal-categories">
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Main Image</label>
                                                <div id="modal-main-image-preview" class="mb-2"></div>
                                                <input type="file" class="form-control" name="main_image" accept="image/*">
                                                <input type="hidden" name="existing_main_image" id="modal-existing-main">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Thumbnail Image</label>
                                                <div id="modal-thumb-image-preview" class="mb-2"></div>
                                                <input type="file" class="form-control" name="thumbnail_image" accept="image/*">
                                                <input type="hidden" name="existing_thumbnail_image" id="modal-existing-thumb">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Published At</label>
                                            <input type="datetime-local" class="form-control" name="published_at" id="modal-published-at">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">View Count</label>
                                            <input type="number" class="form-control" name="view_count" id="modal-view-count" min="0">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Save changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script>
        (function(){
            const editModalEl = document.getElementById('editModal');
            if (!editModalEl) return;
            const editModal = new bootstrap.Modal(editModalEl);

            document.querySelectorAll('.edit-btn').forEach(btn => {
                btn.addEventListener('click', function(e){
                    const id = this.getAttribute('data-id');
                    const title = this.getAttribute('data-title') || '';
                    const short_desc = this.getAttribute('data-short_description') || '';
                    const desc = this.getAttribute('data-description') || '';
                    const links = this.getAttribute('data-links') || '';
                    const main_image = this.getAttribute('data-main_image') || '';
                    const categories = this.getAttribute('data-categories') || '';
                    const thumb_image = this.getAttribute('data-thumbnail_image') || '';
                    let published_at = this.getAttribute('data-published_at') || '';
                    const view_count = this.getAttribute('data-view_count') || 0;

                    // Convert published_at to datetime-local format if possible
                    if (published_at) {
                        published_at = published_at.replace(' ', 'T');
                    }

                    document.getElementById('modal-news-id').value = id;
                    document.getElementById('modal-title').value = title;
                    document.getElementById('modal-short-desc').value = short_desc;
                    document.getElementById('modal-desc').value = desc;
                    document.getElementById('modal-links').value = links;
                    document.getElementById('modal-categories').value = categories;
                    document.getElementById('modal-existing-main').value = main_image;
                    document.getElementById('modal-existing-thumb').value = thumb_image;

                    // set previews
                    const mainPreview = document.getElementById('modal-main-image-preview');
                    mainPreview.innerHTML = '';
                    if (main_image) {
                        const img = document.createElement('img');
                        img.src = main_image;
                        img.style.maxWidth = '100%';
                        img.style.height = 'auto';
                        mainPreview.appendChild(img);
                    }
                    const thumbPreview = document.getElementById('modal-thumb-image-preview');
                    thumbPreview.innerHTML = '';
                    if (thumb_image) {
                        const img2 = document.createElement('img');
                        img2.src = thumb_image;
                        img2.style.maxWidth = '100%';
                        img2.style.height = 'auto';
                        thumbPreview.appendChild(img2);
                    }

                    document.getElementById('modal-published-at').value = published_at;
                    document.getElementById('modal-view-count').value = view_count;

                    editModal.show();
                });
            });
        })();
    </script>
</body>
</html>
