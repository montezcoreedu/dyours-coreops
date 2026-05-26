<?php
    session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php
        if (isset($_SESSION['errorMessage'])) {
            echo $_SESSION['errorMessage'];
            unset($_SESSION['errorMessage']);
        }
    ?>
    <form action="authenticate.php" method="post">
        <input type="text" name="username" placeholder="Username" autofocus required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
</body>
</html>