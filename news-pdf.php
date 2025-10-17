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
      <div class="col-lg-3 col-md-6">
        <div class="card h-100">
          <img src="https://via.placeholder.com/400x200" class="card-img-top" alt="News 1">
          <div class="card-body">
            <p class="date">March 15, 2025</p>
            <p class="category">City Updates / Local News</p>
            <p class="desc">Road development, cleanliness drives, government events, or new public facilities.</p>
          </div>
          <div class="card-footer action-links">
            <a href="#" class="read">Read News</a>
            <a href="#" class="pdf">View PDF</a>
            <a href="#" class="share">Share ↗</a>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6">
        <div class="card h-100">
          <img src="https://via.placeholder.com/400x200" class="card-img-top" alt="News 2">
          <div class="card-body">
            <p class="date">March 15, 2025</p>
            <p class="category">City Updates / Local News</p>
            <p class="desc">Road development, cleanliness drives, government events, or new public facilities.</p>
          </div>
          <div class="card-footer action-links">
            <a href="#" class="read">Read News</a>
            <a href="#" class="pdf">View PDF</a>
            <a href="#" class="share">Share ↗</a>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6">
        <div class="card h-100">
          <img src="https://via.placeholder.com/400x200" class="card-img-top" alt="News 3">
          <div class="card-body">
            <p class="date">March 15, 2025</p>
            <p class="category">City Updates / Local News</p>
            <p class="desc">Road development, cleanliness drives, government events, or new public facilities.</p>
          </div>
          <div class="card-footer action-links">
            <a href="#" class="read">Read News</a>
            <a href="#" class="pdf">View PDF</a>
            <a href="#" class="share">Share ↗</a>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6">
        <div class="card h-100">
          <img src="https://via.placeholder.com/400x200" class="card-img-top" alt="News 4">
          <div class="card-body">
            <p class="date">March 15, 2025</p>
            <p class="category">City Updates / Local News</p>
            <p class="desc">Road development, cleanliness drives, government events, or new public facilities.</p>
          </div>
          <div class="card-footer action-links">
            <a href="#" class="read">Read News</a>
            <a href="#" class="pdf">View PDF</a>
            <a href="#" class="share">Share ↗</a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
