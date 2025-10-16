<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Records</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container my-5">
    <h2 class="text-center mb-4 text-primary fw-bold">Employee Records</h2>
    
    <?php
    $host = "localhost";
    $user = "root";
    $pass = "";
    $dbname = "demo_db";
    $port = 3307;

    $conn = mysqli_connect($host, $user, $pass, $dbname, $port);

    if (!$conn) {
        die("<div class='alert alert-danger'>Connection failed: " . mysqli_connect_error() . "</div>");
    }

    $sql = "SELECT * FROM emp";
    $record = $conn->query($sql);
    $n = mysqli_num_rows($record);

    if ($n > 0) {
        echo "<div class='table-responsive shadow-sm rounded'>";
        echo "<table class='table table-striped table-hover align-middle'>";
        echo "<thead class='table-dark'>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Salary</th>
                </tr>
              </thead>";
        echo "<tbody>";

        while ($row = mysqli_fetch_row($record)) {
            echo "<tr>";
            echo "<td>$row[0]</td>";
            echo "<td>$row[1]</td>";
            echo "<td>$row[2]</td>";
            echo "<td>$row[3]</td>";
            echo "</tr>";
        }

        echo "</tbody></table></div>";
    } else {
        echo "<div class='alert alert-warning text-center'>No records found.</div>";
    }
    ?>
</div>

</body>
</html>
