<?php
require_once __DIR__ . '/auth.php';
// require_once __DIR__ . '/db.php';
ensure_logged_in();

$admin = $_SESSION['admin'];

$mysqli = require __DIR__ . '/db.php';

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

// Handle update request from modal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update' && !empty($_POST['news_id'])) {
    $nid = (int)$_POST['news_id'];
    $title = trim($_POST['title'] ?? '');
    $published_at = trim($_POST['published_at'] ?? '');
    $view_count = (int)($_POST['view_count'] ?? 0);

    $stmt = $mysqli->prepare('UPDATE news SET title = ?, published_at = ?, view_count = ? WHERE news_id = ? LIMIT 1');
    if ($stmt) {
        $stmt->bind_param('ssii', $title, $published_at, $view_count, $nid);
        $stmt->execute();
    }
    header('Location: news-pannel.php');
    exit();
}

// Fetch all news
$newsRows = [];
$res = $mysqli->query('SELECT news_id, title, published_at, view_count FROM news ORDER BY published_at DESC');
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

        .cust-left-card{
            padding-top: 1%;
            padding-bottom: 1%;
            background-color: white;
            
        }

        .cust-col-1{
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
                <div class="row">
                        <center class="cust-left-card">
                            <a href="" class="text-decoration-none text-dark">
                                <i class="fa-solid fa-newspaper"></i>
                                <h3 class="pt-3">Post News</h3>
                            </a>
                        </center>
                </div> 

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
                                                    <button class="btn btn-sm btn-primary edit-btn" data-id="<?php echo $row['news_id']; ?>" data-title="<?php echo htmlspecialchars($row['title'], ENT_QUOTES); ?>" data-published_at="<?php echo htmlspecialchars($row['published_at'], ENT_QUOTES); ?>" data-view_count="<?php echo (int)$row['view_count']; ?>">Edit</button>
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

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="post" id="editForm">
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
                    let published_at = this.getAttribute('data-published_at') || '';
                    const view_count = this.getAttribute('data-view_count') || 0;

                    // Convert published_at to datetime-local format if possible
                    if (published_at) {
                        // If published_at includes a space (MySQL DATETIME), convert to 'YYYY-MM-DDTHH:MM'
                        published_at = published_at.replace(' ', 'T');
                    }

                    document.getElementById('modal-news-id').value = id;
                    document.getElementById('modal-title').value = title;
                    document.getElementById('modal-published-at').value = published_at;
                    document.getElementById('modal-view-count').value = view_count;

                    editModal.show();
                });
            });
        })();
    </script>
</body>
</html>
