<?php
// eventsnews.php - dynamic events listing using news table
// Load DB connection
$mysqli = require __DIR__ . '/admin/db.php';

// helper to resolve image path (fallback to assets)
function img_for($path, $fallback) {
  // Return the image value as stored in the database without adding or modifying paths.
  // If the DB value is empty, use the provided fallback path.
  $p = trim((string)$path);
  if ($p === '' || $p === null) return $fallback;
  return $p;
}

// If an id is provided, show that as main, otherwise pick the latest
$selectedId = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Fetch rows from news table
$events = [];
$limit = 22;
$sql = "SELECT news_id, title, short_description, description, links, main_image, categories, thumbnail_image, view_count, published_at FROM news ORDER BY published_at DESC LIMIT $limit";
$res = $mysqli->query($sql);
$sqlError = null;
if ($res) {
  while ($r = $res->fetch_assoc()) $events[] = $r;
} else {
  $sqlError = $mysqli->error;
}

// determine main event
$mainEvent = null;
if ($selectedId) {
  foreach ($events as $e) {
    if ((int)($e['news_id'] ?? 0) === $selectedId) { $mainEvent = $e; break; }
  }
}
if (!$mainEvent && count($events) > 0) $mainEvent = $events[0];

// top small (next 3)
$topSmall = [];
foreach ($events as $e) {
  if ($mainEvent && (int)($e['news_id'] ?? 0) === (int)($mainEvent['news_id'] ?? 0)) continue;
  $topSmall[] = $e;
  if (count($topSmall) >= 4) break;
}

// remaining
$shownIds = [];
if ($mainEvent) $shownIds[] = (int)($mainEvent['news_id'] ?? 0);
foreach ($topSmall as $s) $shownIds[] = (int)($s['news_id'] ?? 0);
$remaining = [];
foreach ($events as $e) {
  if (in_array((int)($e['news_id'] ?? 0), $shownIds, true)) continue;
  $remaining[] = $e;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Upcoming Events</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f3f3f3;
      font-family: 'Poppins', sans-serif;
    }
    .section-title {
      font-weight: 700;
      font-size: 2rem;
      margin-bottom: 1rem;
    }
    .main-card img {
      width: 100%;
      height: 605px;
      object-fit: cover;
      border-radius: 15px;
    }
    .event-card img {
      width: 100%;
      height: 112px;
      object-fit: contain;
      border-radius: 1px;
    }
    .event-info {
      font-size: 0.9rem;
      color: #666;
    }
    .read-more {
      color: #ff6f61;
      text-decoration: none;
      font-weight: 500;
    }
    .read-more:hover {
      text-decoration: underline;
    }
    .stats-box {
      background: white;
      border-radius: 10px;
      padding: 15px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      margin-bottom: 1rem;
    }
    .load-more-btn {
      background: #1c1c1c;
      color: #fff;
      border-radius: 25px;
      padding: 10px 30px;
    }
    .stat-img{
        background-image: url('./Assets/siide-news-baaner.png');
        object-fit: cover;
        width: 100%;
        height: 230px;
        background-repeat: no-repeat;}
    .card-img img{
        width: 100%;
        height: 200px;
        object-fit: cover;
    }
    .top-baner{
      padding: 48px;
      }
      .area{
        padding-top:10px;
        padding-bottom:10px;
        padding-left:5px;
        padding-right:5px;
        color: #ffffff;
        background-color: #011b3d;
      }
    
    @media (max-width: 992px) {
      .main-card img {
        height: 530px;
      }
    } 

  @media only screen   
  and (min-device-width : 320px)   
  and (max-device-width : 640px)  
  { 
    .main-card img {
      height: 400px;
    }
    .top-baner{
      padding: 10px;
      }
    .lower-baner{
      display: none;
    }
  }

  </style>
</head>
<body>
  <div class="container-fluid top-baner ">
    
    <div class="row g-4">
      <div class="col-lg-6">
        <h1 class="section-title ">Latest News</h1>
        <div class="main-card position-relative">
          <?php if ($mainEvent):
              $resolvedMainImg = img_for($mainEvent['main_image'] ?? '', './Assets/news-banner-1.png'); ?>
              <img src="<?= htmlspecialchars($resolvedMainImg) ?>" alt="Main Event">
              <div class="position-absolute bottom-0 start-0 text-white p-3" style="background:rgba(0,0,0,0.4); border-radius:0 0 15px 15px;">
                <small><i class="bi bi-calendar"></i> <?= htmlspecialchars(!empty($mainEvent['published_at']) ? date('F j, Y', strtotime($mainEvent['published_at'])) : '') ?></small>
                <?php if (!empty($mainEvent['categories'])): ?>
                  <span class="ms- badge bg-warning text-dark"><?= htmlspecialchars($mainEvent['categories']) ?></span>
                <?php endif; ?>
                <h5 class="mt-1"><?= htmlspecialchars($mainEvent['title'] ?? '') ?></h5>
                <p class="mb-1"><?= htmlspecialchars($mainEvent['short_description'] ?? '') ?></p>
                <small class="d-inline-block me-2"><i class="bi bi-eye"></i> <?= (int)($mainEvent['view_count'] ?? 0) ?></small>
                <a href="eventsnews.php?id=<?= urlencode($mainEvent['news_id']) ?>" class="read-more">Read More</a>
              </div>
          <?php else: ?>
              <img src="./Assets/news-banner-1.png" alt="Main Event">
          <?php endif; ?>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="d-flex flex-column gap-3">
          <?php if (!empty($topSmall)):
              foreach ($topSmall as $ev):
                $thumb = img_for($ev['thumbnail_image'] ?? '', './Assets/small-news.png'); ?>
                <div class="d-flex gap-2 event-card">
                  <img src="<?= htmlspecialchars($thumb) ?>" alt="Small Event" class="rounded">
                  <div>
                    <small class="text-danger"><?= htmlspecialchars(!empty($ev['published_at']) ? date('F j, Y', strtotime($ev['published_at'])) : '') ?></small>
                    <?php if (!empty($ev['categories'])): ?>
                      <div><span class="badge bg-secondary"><?= htmlspecialchars($ev['categories']) ?></span></div>
                    <?php endif; ?>
                    <h6 class="mb-1"><?= htmlspecialchars($ev['title'] ?? '') ?></h6>
                    <p class="event-info mb-1"><?= htmlspecialchars($ev['short_description'] ?? '') ?></p>
                    <a href="eventsnews.php?id=<?= urlencode($ev['news_id']) ?>" class="read-more">Read More</a>
                  </div>
                </div>
          <?php
              endforeach;
          else: ?>
            <div class="d-flex gap-2 event-card">
              <img src="./Assets/small-news-baner1.png" alt="Small Event" class="rounded">
              <div>
                <small class="text-danger">--</small>
                <h6 class="mb-1">No Events</h6>
                <p class="event-info mb-1">No upcoming events at this time.</p>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="stats-box">
          <p class="mb-1 fw-bold fs-6">Region covered by district</p>
          <h5 class="mt-3 area">7,020 sq km</h5>
        </div>
        <div class="stats-box">
          <p class="mb-1 fw-bold fs-6">Total People in district</p>
          <h5 class="mt-3 area">15.63 lac</h5>
        </div>
        <div class="stats-box">
          <p class="mb-1 fw-bold fs-6">Region covered by city</p>
          <h5 class="mt-3 area">50 sq km</h5>
        </div>
        <div class="stats-box stat-img text-center">
          <!-- <p>Best local news & updates platform hello dewas</p>
          <a href="#" class="btn btn-outline-dark btn-sm">Read Now</a> -->
        </div>
      </div>
    </div>

    <div class="row g-4 mt-4 lower-baner">
      <?php
      if (!empty($remaining)) {
        $gridShown = 0;
        foreach ($remaining as $ev) {
          if ($gridShown >= 3) break;
          $img = img_for($ev['main_image'] ?? '', './Assets/news-img1.png');
          ?>
          <div class="col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm card-img">
              <img src="<?= htmlspecialchars($img) ?>" class="card-img-top" alt="News">
              <div class="card-body">
                <small class="text-danger"><?= htmlspecialchars(!empty($ev['published_at']) ? date('F j, Y', strtotime($ev['published_at'])) : '') ?></small>
                <h6 class="mt-2"><?= htmlspecialchars($ev['title'] ?? '') ?></h6>
                <p class="event-info mb-1"><?= htmlspecialchars($ev['short_description'] ?? '') ?></p>
                <a href="eventsnews.php?id=<?= urlencode($ev['news_id']) ?>" class="read-more">Read More</a>
              </div>
            </div>
          </div>
          <?php
          $gridShown++;
        }
      }
      ?>
      
    </div>

    <div class="text-center mt-4">
      <button class="btn load-more-btn">Load More</button>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>