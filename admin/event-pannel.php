<?php
require_once __DIR__ . '/auth.php';
// require_once __DIR__ . '/db.php';
ensure_logged_in();

$admin = $_SESSION['admin'];

// database connection
$mysqli = require __DIR__ . '/db.php';

// Handle create request (Post Event)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $title = trim($_POST['title'] ?? '');
    $short_description = trim($_POST['short_description'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $links = trim($_POST['links'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $time = trim($_POST['time'] ?? '');
    $venue = trim($_POST['venue'] ?? '');
    $countdown = (int)($_POST['countdown'] ?? 0);

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

    $stmt = $mysqli->prepare('INSERT INTO events (title, short_description, description, links, main_image, thumbnail_image, date, time, venue, countdown) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    if ($stmt) {
        // 9 strings + 1 int -> 'sssssssss i' => 'sssssssssi'
        $stmt->bind_param('sssssssssi', $title, $short_description, $description, $links, $main_image_path, $thumb_image_path, $date, $time, $venue, $countdown);
        $stmt->execute();
    }

    header('Location: event-pannel.php');
    exit();
}

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && !empty($_POST['event_id'])) {
    $eid = (int)$_POST['event_id'];
    $stmt = $mysqli->prepare('DELETE FROM events WHERE event_id = ? LIMIT 1');
    if ($stmt) {
        $stmt->bind_param('i', $eid);
        $stmt->execute();
    }
    header('Location: event-pannel.php');
    exit();
}

// Handle update request from modal (update all fields, handle optional image replacement)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update' && !empty($_POST['event_id'])) {
    $eid = (int)$_POST['event_id'];
    $title = trim($_POST['title'] ?? '');
    $short_description = trim($_POST['short_description'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $links = trim($_POST['links'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $time = trim($_POST['time'] ?? '');
    $venue = trim($_POST['venue'] ?? '');
    $countdown = (int)($_POST['countdown'] ?? 0);

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
    $stmt = $mysqli->prepare('UPDATE events SET title = ?, short_description = ?, description = ?, links = ?, main_image = ?, thumbnail_image = ?, date = ?, time = ?, venue = ?, countdown = ? WHERE event_id = ? LIMIT 1');
    if ($stmt) {
        // 9 strings and 2 ints -> 9 s then i i -> 'sssssssssii'
        $stmt->bind_param('sssssssssii', $title, $short_description, $description, $links, $main_image_path, $thumb_image_path, $date, $time, $venue, $countdown, $eid);
        $stmt->execute();
    }
    header('Location: event-pannel.php');
    exit();
}

// Fetch all events (select all columns used by the app)
$eventsRows = [];
$res = $mysqli->query('SELECT event_id, title, short_description, description, links, main_image, thumbnail_image, date, time, venue, countdown FROM events ORDER BY event_id DESC');
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $eventsRows[] = $r;
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
                    <a href="./news-pannel.php" class="text-white text-decoration-none">
                        <h4 class="cust-type">News Management</h4>
                    </a>
                </div>
                <div class="row py-4 px-4 text-center text-white">
                    <a href="./event-pannel.php" class="text-white text-decoration-none">
                        <h4 class="cust-type">Event Management</h4>
                    </a>
                    </div>
                <div class="row py-4 px-4 text-center text-white">
                    <a href="./newspaper-pannel.php" class="text-white text-decoration-none">
                        <h4 class="cust-type">Newspaper management</h4>
                    </a>
                </div>
                <div class="row py-4 px-4 text-center text-white">
                    <a href="./banner-pannel.php" class="text-white text-decoration-none">
                        <h4 class="cust-type">Banner Management</h4>
                    </a>
                </div>
            </div>
            <div class="col-10" style=" height: 100vh; background-image: url('../assets/admin-bg.jpg'); background-size: cover; background-repeat: no-repeat;">

                    <button type="button" class="btn btn-outline-light mt-5" data-bs-toggle="modal" data-bs-target="#createModal">
                        <span class="text-decoration-none text-white">
                            <h3 class="">Post Event</h3>
                        </span>
                    </button>


                <div class="row">
                    <!-- table of past news  -->
                    <div class="col-12 mt-4">
                        <div class="card p-3">
                            <h4>Past Events</h4>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered" id="events-table">
                                    <thead>
                                        <tr>
                                            <th>event_id</th>
                                            <th>title</th>
                                            <th>date</th>
                                            <th>time</th>
                                            <th>venue</th>
                                            <th>edit</th>
                                            <th>delete</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($eventsRows as $row): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['event_id']); ?></td>
                                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                                <td><?php echo htmlspecialchars($row['date']); ?></td>
                                                <td><?php echo htmlspecialchars($row['time']); ?></td>
                                                <td><?php echo htmlspecialchars($row['venue']); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary edit-btn"
                                                        data-id="<?php echo $row['event_id']; ?>"
                                                        data-title="<?php echo htmlspecialchars($row['title'], ENT_QUOTES); ?>"
                                                        data-short_description="<?php echo htmlspecialchars($row['short_description'] ?? '', ENT_QUOTES); ?>"
                                                        data-description="<?php echo htmlspecialchars($row['description'] ?? '', ENT_QUOTES); ?>"
                                                        data-links="<?php echo htmlspecialchars($row['links'] ?? '', ENT_QUOTES); ?>"
                                                        data-main_image="<?php echo htmlspecialchars($row['main_image'] ?? '', ENT_QUOTES); ?>"
                                                        data-thumbnail_image="<?php echo htmlspecialchars($row['thumbnail_image'] ?? '', ENT_QUOTES); ?>"
                                                        data-date="<?php echo htmlspecialchars($row['date'] ?? '', ENT_QUOTES); ?>"
                                                        data-time="<?php echo htmlspecialchars($row['time'] ?? '', ENT_QUOTES); ?>"
                                                        data-venue="<?php echo htmlspecialchars($row['venue'] ?? '', ENT_QUOTES); ?>"
                                                        data-countdown="<?php echo (int)($row['countdown'] ?? 0); ?>">
                                                        Edit
                                                    </button>
                                                </td>
                                                <td>
                                                    <form method="post" onsubmit="return confirm('Delete this event?');" style="display:inline">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="event_id" value="<?php echo (int)$row['event_id']; ?>">
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
                                        <h5 class="modal-title">Post Event</h5>
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
                                        <!-- categories removed - not used for events -->
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
                                            <label class="form-label">Date</label>
                                            <input type="date" class="form-control" name="date">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Time</label>
                                            <input type="time" class="form-control" name="time">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Venue</label>
                                            <input type="text" class="form-control" name="venue">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Countdown (seconds)</label>
                                            <input type="number" class="form-control" name="countdown" value="0" min="0">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-success">Post Event</button>
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
                                        <h5 class="modal-title">Edit Event</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="event_id" id="modal-event-id">
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
                                        <!-- categories removed - not used for events -->
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
                                            <label class="form-label">Date</label>
                                            <input type="date" class="form-control" name="date" id="modal-date">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Time</label>
                                            <input type="time" class="form-control" name="time" id="modal-time">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Venue</label>
                                            <input type="text" class="form-control" name="venue" id="modal-venue">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Countdown (seconds)</label>
                                            <input type="number" class="form-control" name="countdown" id="modal-countdown" min="0">
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
                    const thumb_image = this.getAttribute('data-thumbnail_image') || '';
                    const date = this.getAttribute('data-date') || '';
                    const time = this.getAttribute('data-time') || '';
                    const venue = this.getAttribute('data-venue') || '';
                    const countdown = this.getAttribute('data-countdown') || 0;

                    // (no datetime conversion needed for separate date/time fields)

                    document.getElementById('modal-event-id').value = id;
                    document.getElementById('modal-title').value = title;
                    document.getElementById('modal-short-desc').value = short_desc;
                    document.getElementById('modal-desc').value = desc;
                    document.getElementById('modal-links').value = links;
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

                    document.getElementById('modal-date').value = date;
                    document.getElementById('modal-time').value = time;
                    document.getElementById('modal-venue').value = venue;
                    document.getElementById('modal-countdown').value = countdown;

                    editModal.show();
                });
            });
        })();
    </script>
</body>
</html>
