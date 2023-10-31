<?php

 if($_SERVER["REQUEST_METHOD"] == "POST"){
     match($_POST['action']){
         "Get File" => header("Location: http://localhost:8000/samples/getfile"),
         "Edit" => header("Location: http://localhost:8000/samples/edit"),
         "Add" => header("Location: http://localhost:8000/samples/add"),
     };
 }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sample Requests</title>
</head>
<body>
<form method="POST" action="samples.php">
    <input type="submit" value="Get File" name="action" id="action">
    <br><br>
    <input type="submit" value="Edit" name="action" id="action">
    <br><br>
    <input type="submit" value="Add" name="action" id="action">
</form>
</body>
</html>
