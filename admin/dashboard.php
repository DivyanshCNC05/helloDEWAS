<?php
require_once __DIR__ . '/auth.php';
// require_once __DIR__ . '/db.php';
ensure_logged_in();

$admin = $_SESSION['admin'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <style>
        .cust-card{
            margin-top: 2%;
background: rgba(255, 255, 255, 0.2);
border-radius: 16px;
box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
backdrop-filter: blur(11.5px);
-webkit-backdrop-filter: blur(11.5px);
border: 1px solid rgba(255, 255, 255, 0.3);
            
        }

        body{
            background-image: url('../assets/admin-dashboard.png');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            color: white;
        }


          @media only screen   
  and (min-device-width : 320px)   
  and (max-device-width : 640px)  
  { }






  @media only screen   
  and (min-width: 1030px)   
  and (max-width: 1605px)  
  { 
    .admin-title{
        padding-top: 5%;
    }
  }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <h1 class="pt-5"><center>Welcome, <?php echo htmlspecialchars($admin['name']); ?></center></h1>
        </div>

        <div class="row">
            <div class="col-1"></div>
            <div class="col-4 py-5 cust-card">
                <center>
                    <a href="./news-pannel.php" class="text-white text-decoration-none">
                        <img src="../assets/admin-news.png" alt="" class="admin-img img-fluid">
                        <h3 class="admin-title">News Management</h3>
                    </a>
                </center>
            </div>
            <div class="col-2"></div>
            <div class="col-4 py-5 cust-card">
                <center>
                    <a href="./event-pannel.php" class="text-white text-decoration-none">
                        <img src="../assets/admin-events.png" alt="" class="admin-img img-fluid">
                        <h3 class="admin-title">Event Management</h3>
                    </a>
                </center>
            </div>
            <div class="col-1"></div>
        </div>

        <div class="row">
            <div class="col-1"></div>
            <div class="col-4 py-5 cust-card">
                <center>
                    <a href="./newspaper-pannel.php" class="text-white text-decoration-none">
                        <img src="../assets/admin-newspaper.png" alt="" class="admin-img img-fluid">
                        <h3 class="admin-title">Newspaper management</h3>
                    </a>
                </center>
            </div>
            <div class="col-2"></div>
            <div class="col-4 py-5 cust-card">
                <center>
                    <a href="./banner-pannel.php" class="text-white text-decoration-none">
                        <img src="../assets/admin-banner.png" alt="" class="admin-img img-fluid">
                        <h3 class="admin-title">Banner Management</h3>
                    </a>
                </center>
            </div>
            <div class="col-2"></div>
        </div>

    </div>    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>
