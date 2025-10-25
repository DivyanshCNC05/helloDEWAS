<?php
// banner_slider.php - Dynamic Bootstrap Carousel

// 1️⃣ Load DB connection
$mysqli = require __DIR__ . '/admin/db.php';

// 2️⃣ Fetch active banners (only where display = 1)
$banners = [];
$sql = "SELECT banner_id, image, categories, display FROM banner WHERE display = 1 ORDER BY banner_id DESC";
$result = $mysqli->query($sql);

if ($result) {
  while ($row = $result->fetch_assoc()) {
    $banners[] = $row;
  }
} else {
  $error = $mysqli->error;
}

// 3️⃣ Helper function for safe image path
function img_for($path, $fallback = './Assets/slider-placeholder.png') {
  $p = trim((string)$path);
  if ($p === '' || $p === null) return $fallback;
  if (preg_match('#^https?://#i', $p)) return $p;
  while (strpos($p, '../') === 0) $p = substr($p, 3);
  if (strpos($p, '/') === 0) return '.' . $p;
  if (strpos($p, './') === 0) return $p;
  if (strpos($p, 'uploads/') === 0) return './' . $p;
  if (strpos($p, '/') === false) return './uploads/' . $p;
  return './' . $p;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Auto Image Slider</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .carousel-item img {
      height: 450px;
      object-fit: contain;
      width: 100%;
      border-radius: 10px;
    }
  </style>
</head>

<body>
<div class="container my-4">

  <?php if (!empty($banners)): ?>
    <!-- 🔹 Dynamic Carousel Start -->
    <div id="bannerCarousel" class="carousel slide" data-bs-ride="carousel">
      <div class="carousel-inner">

        <?php foreach ($banners as $i => $b): 
          $img = img_for($b['image'] ?? '', './Assets/slider-placeholder.png');
          $isActive = ($i === 0) ? 'active' : '';
        ?>
          <div class="carousel-item <?= $isActive ?>">
            <img src="<?= htmlspecialchars($img) ?>" class="d-block w-100" alt="Banner <?= (int)$b['banner_id'] ?>">
            <div class="carousel-caption d-none d-md-block">
              <?php if (!empty($b['categories'])): ?>
                <span class="badge bg-warning text-dark px-3 py-2 fs-6">
                  <?= htmlspecialchars($b['categories']) ?>
                </span>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>

      </div>

      <!-- Controls -->
      <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span>
      </button>
    </div>
    <!-- 🔹 Carousel End -->
  <?php else: ?>
    <div class="alert alert-warning text-center mt-5">
      No banners available to display.
    </div>
  <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
  // Auto-slide every 3 seconds
  const myCarousel = document.querySelector('#bannerCarousel');
  if (myCarousel) {
    const carousel = new bootstrap.Carousel(myCarousel, {
      interval: 3000,
      ride: 'carousel'
    });
  }
</script>

</body>
</html>
