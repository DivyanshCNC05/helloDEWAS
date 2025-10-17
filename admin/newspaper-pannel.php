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
    $name = preg_replace('/[^a-zA-Z0-9._-]/', '_', $name);
    return $name;
}

// Create
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $title = trim($_POST['title'] ?? '');
    $published_at = trim($_POST['published_at'] ?? '');
    if (strpos($published_at, 'T') !== false) $published_at = str_replace('T', ' ', $published_at);

    $pdf_path = null;
    if (!empty($_FILES['pdf']['name'])) {
        $name = basename($_FILES['pdf']['name']);
        $name = time() . '_' . safe_filename($name);
        $target = $uploadDir . '/' . $name;
        if (move_uploaded_file($_FILES['pdf']['tmp_name'], $target)) $pdf_path = 'uploads/' . $name;
    }

    $thumb_path = null;
    if (!empty($_FILES['thumbnail']['name'])) {
        $name = basename($_FILES['thumbnail']['name']);
        $name = time() . '_thumb_' . safe_filename($name);
        $target = $uploadDir . '/' . $name;
        if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $target)) $thumb_path = 'uploads/' . $name;
    }

    $stmt = $mysqli->prepare('INSERT INTO `newspaper` (title, pdf, published_at, thumbnail) VALUES (?, ?, ?, ?)');
    if ($stmt) {
        $stmt->bind_param('ssss', $title, $pdf_path, $published_at, $thumb_path);
        $stmt->execute();
    }
    header('Location: newspaper-pannel.php');
    exit();
}

// Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete' && !empty($_POST['pdf_id'])) {
    $id = (int)$_POST['pdf_id'];
    $stmt = $mysqli->prepare('DELETE FROM `newspaper` WHERE pdf_id = ? LIMIT 1');
    if ($stmt) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }
    header('Location: newspaper-pannel.php');
    exit();
}

// Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update' && !empty($_POST['pdf_id'])) {
    $id = (int)$_POST['pdf_id'];
    $title = trim($_POST['title'] ?? '');
    $published_at = trim($_POST['published_at'] ?? '');
    if (strpos($published_at, 'T') !== false) $published_at = str_replace('T', ' ', $published_at);

    $existing_pdf = trim($_POST['existing_pdf'] ?? '');
    $existing_thumb = trim($_POST['existing_thumbnail'] ?? '');

    $pdf_path = $existing_pdf ?: null;
    if (!empty($_FILES['pdf']['name'])) {
        $name = basename($_FILES['pdf']['name']);
        $name = time() . '_' . safe_filename($name);
        $target = $uploadDir . '/' . $name;
        if (move_uploaded_file($_FILES['pdf']['tmp_name'], $target)) $pdf_path = 'uploads/' . $name;
    }

    $thumb_path = $existing_thumb ?: null;
    if (!empty($_FILES['thumbnail']['name'])) {
        $name = basename($_FILES['thumbnail']['name']);
        $name = time() . '_thumb_' . safe_filename($name);
        $target = $uploadDir . '/' . $name;
        if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $target)) $thumb_path = 'uploads/' . $name;
    }

    $stmt = $mysqli->prepare('UPDATE `newspaper` SET title = ?, pdf = ?, published_at = ?, thumbnail = ? WHERE pdf_id = ? LIMIT 1');
    if ($stmt) {
        $stmt->bind_param('ssssi', $title, $pdf_path, $published_at, $thumb_path, $id);
        $stmt->execute();
    }
    header('Location: newspaper-pannel.php');
    exit();
}

// Fetch rows
$rows = [];
$res = $mysqli->query('SELECT pdf_id, title, pdf, published_at, thumbnail FROM `newspaper` ORDER BY published_at DESC, pdf_id DESC');
if ($res) {
    while ($r = $res->fetch_assoc()) $rows[] = $r;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Newspaper Management</title>
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
        <div class="col-2 cust-col-1">
            <center>
                <img src="../assets/logo.hd.webp" alt="" class="cust-hd-logo img-fluid">
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
        <div class="col-10" style="height: 100vh; background-image: url('../assets/admin-bg.jpg'); background-size: cover; background-repeat: no-repeat;">

            <button type="button" class="btn btn-outline-light mt-5" data-bs-toggle="modal" data-bs-target="#createModal">
                <span class="text-decoration-none text-white"><h3>Post Newspaper</h3></span>
            </button>

            <div class="row">
                <div class="col-12 mt-4">
                    <div class="card p-3">
                        <h4>Newspaper List</h4>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered" id="newspaper-table">
                                <thead>
                                <tr>
                                    <th>pdf_id</th>
                                    <th>title</th>
                                    <th>published_at</th>
                                    <th>view</th>
                                    <th>edit</th>
                                    <th>delete</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($rows as $r): ?>
                                    <tr>
                                        <td><?php echo (int)$r['pdf_id']; ?></td>
                                        <td><?php echo htmlspecialchars($r['title']); ?></td>
                                        <td><?php echo htmlspecialchars($r['published_at']); ?></td>
                                        <td><a class="btn btn-sm btn-info" target="_blank" href="../view-pdf.php?pdf-id=<?php echo (int)$r['pdf_id']; ?>">View PDF</a></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary edit-btn"
                                                    data-id="<?php echo (int)$r['pdf_id']; ?>"
                                                    data-title="<?php echo htmlspecialchars($r['title'], ENT_QUOTES); ?>"
                                                    data-pdf="<?php echo htmlspecialchars($r['pdf'] ?? '', ENT_QUOTES); ?>"
                                                    data-published_at="<?php echo htmlspecialchars($r['published_at'] ?? '', ENT_QUOTES); ?>"
                                                    data-thumbnail="<?php echo htmlspecialchars($r['thumbnail'] ?? '', ENT_QUOTES); ?>">
                                                Edit
                                            </button>
                                        </td>
                                        <td>
                                            <form method="post" onsubmit="return confirm('Delete this entry?');" style="display:inline">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="pdf_id" value="<?php echo (int)$r['pdf_id']; ?>">
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
                                    <h5 class="modal-title">Post Newspaper</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="action" value="create">
                                    <div class="mb-3">
                                        <label class="form-label">Title</label>
                                        <input type="text" class="form-control" name="title" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">PDF file</label>
                                        <input type="file" class="form-control" name="pdf" accept="application/pdf" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Thumbnail</label>
                                        <input type="file" class="form-control" name="thumbnail" accept="image/*">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Published At</label>
                                        <input type="datetime-local" class="form-control" name="published_at">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-success">Post</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Edit Modal -->
                <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form method="post" id="editForm" enctype="multipart/form-data">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Newspaper</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="pdf_id" id="modal-pdf-id">
                                    <div class="mb-3">
                                        <label class="form-label">Title</label>
                                        <input type="text" class="form-control" name="title" id="modal-title" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Existing PDF</label>
                                        <div id="modal-existing-pdf" class="mb-2"></div>
                                        <input type="file" class="form-control" name="pdf" accept="application/pdf">
                                        <input type="hidden" name="existing_pdf" id="modal-existing-pdf-hidden">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Thumbnail Preview</label>
                                        <div id="modal-thumb-preview" class="mb-2"></div>
                                        <input type="file" class="form-control" name="thumbnail" accept="image/*">
                                        <input type="hidden" name="existing_thumbnail" id="modal-existing-thumb-hidden">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Published At</label>
                                        <input type="datetime-local" class="form-control" name="published_at" id="modal-published-at">
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
            const title = this.getAttribute('data-title') || '';
            const pdf = this.getAttribute('data-pdf') || '';
            const published_at = this.getAttribute('data-published_at') || '';
            const thumbnail = this.getAttribute('data-thumbnail') || '';

            document.getElementById('modal-pdf-id').value = id;
            document.getElementById('modal-title').value = title;
            document.getElementById('modal-existing-pdf-hidden').value = pdf;

            const existingPdfDiv = document.getElementById('modal-existing-pdf');
            existingPdfDiv.innerHTML = '';
            if (pdf) {
                const a = document.createElement('a');
                a.href = pdf;
                a.target = '_blank';
                a.textContent = 'Open current PDF';
                existingPdfDiv.appendChild(a);
            }

            document.getElementById('modal-existing-thumb-hidden').value = thumbnail;
            const thumbDiv = document.getElementById('modal-thumb-preview');
            thumbDiv.innerHTML = '';
            if (thumbnail) {
                const img = document.createElement('img');
                img.src = thumbnail;
                img.style.maxWidth = '200px';
                img.style.height = 'auto';
                thumbDiv.appendChild(img);
            }

            let dtVal = published_at;
            if (dtVal.indexOf(' ') !== -1) dtVal = dtVal.replace(' ', 'T');
            if (dtVal.length >= 16) dtVal = dtVal.substr(0,16);
            document.getElementById('modal-published-at').value = dtVal;

            editModal.show();
        });
    });
})();
</script>
</body>
</html>
