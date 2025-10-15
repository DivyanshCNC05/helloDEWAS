<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <center>
        <?php
        $host = "localhost";
$user = "root";
$pass = "";
$dbname = "demo_db";
$port = 3307; // or 3307 if you changed it
       $conn = mysqli_connect($host, $user, $pass, $dbname, $port);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
} else {
    echo "Connected successfully!<br>";
}
        $sql="select * from emp";
        $record=$conn->query($sql);
        $n=mysqli_num_rows($record);
        if($n>0){
            while($row=mysqli_fetch_row($record))
            {
                echo $row[0]." ".$row[1]." ".$row[2]." ".$row[3]." ";
                echo "<br>";
            }
        }
        else
            echo "<font colo"
        ?>
    </center>
</body>
</html>