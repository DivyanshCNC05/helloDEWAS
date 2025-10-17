<?php
require_once __DIR__ . '/auth.php';
ensure_logged_in();

$mysqli = require __DIR__ . '/db.php';

$uploadDir = __DIR__ . '/../uploads';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Helper to sanitize filenames
function safe_filename($name) {
    return preg_replace('/[^a-zA-Z0-9._-]/', '_', $name);
}

// CREATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $categories = trim($_POST['categories'] ?? '');
    $display = isset($_POST['display']) ? 1 : 0;

    $image_path = null;
    if (!empty($_FILES['image']['name'])) {
        $name = time() . '_' . safe_filename(basename($_FILES['image']['name']));
        $target = $uploadDir . '/' . $name;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $image_path = 'uploads/' . $name;
        }
    }

    $stmt = $mysqli->prepare('INSERT INTO `banner` (image, categories, display) VALUES (?, ?, ?)');
    if ($stmt) {
        $stmt->bind_param('ssi', $image_path, $categories, $display);
        $stmt->execute();
    }
    header('Location: banner-pannel.php');
    exit();
}

// DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete' && !empty($_POST['banner_id'])) {
    $id = (int)$_POST['banner_id'];
    $stmt = $mysqli->prepare('DELETE FROM `banner` WHERE banner_id = ? LIMIT 1');
    if ($stmt) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }
    header('Location: banner-pannel.php');
    exit();
}

// UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update' && !empty($_POST['banner_id'])) {
    $id = (int)$_POST['banner_id'];
    $categories = trim($_POST['categories'] ?? '');
    $display = isset($_POST['display']) ? 1 : 0;
    $existing_image = trim($_POST['existing_image'] ?? '');

    $image_path = $existing_image;
    if (!empty($_FILES['image']['name'])) {
        $name = time() . '_' . safe_filename(basename($_FILES['image']['name']));
        $target = $uploadDir . '/' . $name;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $image_path = 'uploads/' . $name;
        }
    }

    $stmt = $mysqli->prepare('UPDATE `banner` SET image = ?, categories = ?, display = ? WHERE banner_id = ? LIMIT 1');
    if ($stmt) {
        $stmt->bind_param('ssii', $image_path, $categories, $display, $id);
        $stmt->execute();
    }
    header('Location: banner-pannel.php');
    exit();
}

// FETCH
$rows = [];
$res = $mysqli->query('SELECT banner_id, image, categories, display FROM `banner` ORDER BY banner_id DESC');
if ($res) {
    while ($r = $res->fetch_assoc()) $rows[] = $r;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Banner Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .cust-type {
            padding-top: 4%;
            padding-bottom: 4%;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(11.5px);
            -webkit-backdrop-filter: blur(11.5px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        body { background-color: #132e51; }
        .cust-hd-logo { max-width: 100%; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-2">
            <center>
                <img src="../assets/logo.hd.webp" alt="Logo" class="cust-hd-logo img-fluid mt-3">
            </center>
            <div class="row py-4 px-4 text-center text-white">
                <a href="./news-pannel.php" class="text-white text-decoration-none"><h4 class="cust-type">News Management</h4></a>
            </div>
            <div class="row py-4 px-4 text-center text-white">
                <a href="./event-pannel.php" class="text-white text-decoration-none"><h4 class="cust-type">Event Management</h4></a>
            </div>
            <div class="row py-4 px-4 text-center text-white">
                <a href="./newspaper-pannel.php" class="text-white text-decoration-none"><h4 class="cust-type">Newspaper Management</h4></a>
            </div>
            <div class="row py-4 px-4 text-center text-white">
                <a href="./banner-pannel.php" class="text-white text-decoration-none"><h4 class="cust-type">Banner Management</h4></a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-10" style="height: 100vh; background-image: url('../assets/admin-bg.jpg'); background-size: cover; background-repeat: no-repeat;">

            <button type="button" class="btn btn-outline-light mt-5" data-bs-toggle="modal" data-bs-target="#createModal">
                <span class="text-decoration-none text-white"><h3>Add Banner</h3></span>
            </button>

            <div class="row">
                <div class="col-12 mt-4">
                    <div class="card p-3">
                        <h4>Banner List</h4>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Categories</th>
                                    <th>Display</th>
                                    <th>Edit</th>
                                    <th>Delete</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($rows as $r): ?>
                                    <tr>
                                        <td><?php echo (int)$r['banner_id']; ?></td>
                                        <td>
                                            <?php if (!empty($r['image'])): ?>
                                                <img src="../<?php echo htmlspecialchars($r['image']); ?>" alt="Banner" style="max-width:120px; height:auto;">
                                            <?php else: ?>
                                                <span>No image</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($r['categories']); ?></td>
                                        <td><?php echo $r['display'] ? 'Yes' : 'No'; ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary edit-btn"
                                                data-id="<?php echo (int)$r['banner_id']; ?>"
                                                data-image="<?php echo htmlspecialchars($r['image'] ?? '', ENT_QUOTES); ?>"
                                                data-categories="<?php echo htmlspecialchars($r['categories'] ?? '', ENT_QUOTES); ?>"
                                                data-display="<?php echo (int)$r['display']; ?>">
                                                Edit
                                            </button>
                                        </td>
                                        <td>
                                            <form method="post" onsubmit="return confirm('Delete this banner?');" style="display:inline">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="banner_id" value="<?php echo (int)$r['banner_id']; ?>">
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
                                    <h5 class="modal-title">Add Banner</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="action" value="create">
                                    <div class="mb-3">
                                        <label class="form-label">Image</label>
                                        <input type="file" class="form-control" name="image" accept="image/*" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Categories</label>
                                        <input type="text" class="form-control" name="categories" required>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="display" id="displayCreate" checked>
                                        <label class="form-check-label" for="displayCreate">Display this banner</label>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-success">Add</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Edit Modal -->
                <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form method="post" enctype="multipart/form-data">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Banner</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="banner_id" id="modal-banner-id">
                                    <div class="mb-3">
                                        <label class="form-label">Current Image</label>
                                        <div id="modal-existing-image" class="mb-2"></div>
                                        <input type="file" class="form-control" name="image" accept="image/*">
                                        <input type="hidden" name="existing_image" id="modal-existing-image-hidden">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Categories</label>
                                        <input type="text" class="form-control" name="categories" id="modal-categories" required>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="display" id="modal-display">
                                        <label class="form-check-label" for="modal-display">Display this banner</label>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function(){
    const editModalEl = document.getElementById('editModal');
    if (!editModalEl) return;
    const editModal = new bootstrap.Modal(editModalEl);

    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function(){
            const id = this.getAttribute('data-id');
            const image = this.getAttribute('data-image') || '';
            const categories = this.getAttribute('data-categories') || '';
            const display = parseInt(this.getAttribute('data-display')) || 0;

            document.getElementById('modal-banner-id').value = id;
            document.getElementById('modal-categories').value = categories;
            document.getElementById('modal-existing-image-hidden').value = image;

            const existingImageDiv = document.getElementById('modal-existing-image');
            existingImageDiv.innerHTML = '';
            if (image) {
                const img = document.createElement('img');
                img.src = '../' + image;
                img.style.maxWidth = '200px';
                img.style.height = 'auto';
                existingImageDiv.appendChild(img);
            }

            document.getElementById('modal-display').checked = !!display;

            editModal.show();
        });
    });
})();
</script>
</body>
</html>
