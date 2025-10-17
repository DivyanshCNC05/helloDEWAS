<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Hello Dewas Publications</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #fff;
      font-family: 'Poppins', sans-serif;
    }
    .section-title {
      text-align: center;
      font-size: 2.8rem;
      font-weight: 700;
      margin-top: 50px;
    }
    .section-subtitle {
      text-align: center;
      color: #555;
      margin-bottom: 40px;
    }
    .card {
      border: none;
      border-radius: 15px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 15px rgba(0,0,0,0.15);
    }
    .card-img-top {
      border-top-left-radius: 15px;
      border-top-right-radius: 15px;
      height: 200px;
      object-fit: cover;
    }
    .card-body { padding: 20px; }
    .date { color: #ff6f61; font-weight: 600; }
    .category { font-weight: 600; margin-top: 5px; color: #333; }
    .card-footer {
      background: transparent;
      border-top: none;
      display: flex;
      justify-content: space-between;
    }
    .action-links a {
      font-size: 0.9rem;
      text-decoration: none;
      font-weight: 500;
      margin-right: 15px;
    }
    .pdf { color: #ffb400; }
  </style>
</head>
<body>
  <div class="container">
    <h1 class="section-title">Hello Dewas Publications</h1>
    <p class="section-subtitle">
      Bringing you the latest stories, events, and voices that capture the true spirit of our city — Dewas.
    </p>

    <div class="row g-4">
      <?php
      // Load DB config
      $config = require __DIR__ . '/.env';
      $mysqli = new mysqli($config['host'], $config['user'], $config['pass'], $config['dbname'], $config['port']);

      if ($mysqli->connect_errno) {
          echo '<div class="col-12"><p class="text-danger">Database connection failed: ' 
              . htmlspecialchars($mysqli->connect_error) . '</p></div>';
      } else {
          $sql = "SELECT pdf_id, title, published_at, thumbnail FROM newspaper ORDER BY published_at DESC LIMIT 12";
          $res = $mysqli->query($sql);

          if ($res && $res->num_rows > 0) {
              while ($r = $res->fetch_assoc()) {
                  // Determine image path
                  $thumb = trim($r['thumbnail']);
                  if (!empty($thumb)) {
                      // Add relative path if necessary
                      if (strpos($thumb, './uploads/') === 0) {
                          $thumb = '../' . $thumb;  // adjust if needed
                      }
                  } else {
                      $thumb = 'assets/no-thumb.jpg'; // local fallback
                  }

                  $title = htmlspecialchars($r['title'] ?? 'Untitled');
                  $published = !empty($r['published_at'])
                      ? date('F j, Y', strtotime($r['published_at']))
                      : '';

                  echo '<div class="col-lg-3 col-md-6">';
                  echo '  <div class="card h-100">';
                  echo '    <img src="' . htmlspecialchars($thumb) . '" class="card-img-top" alt="' . $title . '">';
                  echo '    <div class="card-body">';
                  echo '      <p class="date">' . htmlspecialchars($published) . '</p>';
                  echo '      <h5 class="category">' . $title . '</h5>';
                  echo '    </div>';
                  echo '    <div class="card-footer action-links">';
                  echo '      <a href="view-pdf.php?pdf_id=' . urlencode($r['pdf_id']) . '" class="pdf" target="_blank">View PDF</a>';
                  echo '    </div>';
                  echo '  </div>';
                  echo '</div>';
              }
              $res->free();
          } else {
              echo '<div class="col-12"><p class="text-center text-muted">No publications found.</p></div>';
          }
      }
      ?>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
