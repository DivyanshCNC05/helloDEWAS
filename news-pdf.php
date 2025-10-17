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
    .card-body {
      padding: 20px;
    }
    .date {
      color: #ff6f61;
      font-weight: 600;
    }
    .category {
      font-weight: 600;
      margin-top: 5px;
      color: #333;
    }
    .desc {
      color: #666;
      font-size: 0.9rem;
      margin: 10px 0;
    }
    .card-footer {
      background: transparent;
      border-top: none;
      display: flex;
      justify-content: space-between;
      flex-wrap: wrap;
    }
    .action-links a {
      font-size: 0.9rem;
      text-decoration: none;
      font-weight: 500;
      margin-right: 15px;
    }
    .read { color: #ff6f61; }
    .pdf { color: #ffb400; }
    .share { color: #008cff; }
    @media (max-width: 768px) {
      .section-title {
        font-size: 2rem;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <h1 class="section-title">Hello Dewas Publications</h1>
    <p class="section-subtitle">Bringing you the latest stories, events, and voices that capture the true spirit of our city — Dewas.</p>

    <div class="row g-4">
      <?php
      // Load DB config and connect
      $config = require __DIR__ . '/.env';
      $mysqli = new mysqli($config['host'], $config['user'], $config['pass'], $config['dbname'], $config['port']);
      if ($mysqli->connect_errno) {
          echo '<div class="col-12"><p class="text-danger">Database connection failed.</p></div>';
      } else {
  $res = $mysqli->query("SELECT `pdf_id` AS pdf_id, title, published_at, thumbnail FROM newspaper ORDER BY published_at DESC LIMIT 12");
          if ($res) {
              while ($r = $res->fetch_assoc()) {
          $thumb = !empty($r['thumbnail']) ? $r['thumbnail'] : 'https://via.placeholder.com/400x200';
          $title = htmlspecialchars($r['title'] ?? 'Untitled');
          $published = !empty($r['published_at']) ? date('F j, Y', strtotime($r['published_at'])) : '';
                  echo "<div class=\"col-lg-3 col-md-6\">";
                  echo "<div class=\"card h-100\">";
                  echo "<img src=\"" . htmlspecialchars($thumb) . "\" class=\"card-img-top\" alt=\"" . $title . "\">";
                  echo "<div class=\"card-body\">";
                  echo "<p class=\"date\">" . htmlspecialchars($published) . "</p>";
                  echo "<h5 class=\"category\">" . $title . "</h5>";
                  echo "</div>"; // card-body
                  echo "<div class=\"card-footer action-links\">";
                  echo "<a href=\"view-pdf.php?pdf_id=" . urlencode($r['pdf_id']) . "\" class=\"pdf\" target=\"_blank\">View PDF</a>";
                  echo "</div>"; // card-footer
                  echo "</div>"; // card
                  echo "</div>"; // col
              }
              $res->free();
          } else {
              echo '<div class="col-12"><p>No news found.</p></div>';
          }
      }
      ?>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
