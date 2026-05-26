<?php
    session_start();
    include("common/dbconnection.php");

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        $stmt = $conn->prepare("SELECT * FROM employees WHERE username = ? AND disable_login = 0");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $employeeData = $result->fetch_assoc();
            if (password_verify($password, $employeeData['password'])) {
                $_SESSION['employee_id'] = $employeeData['employee_id'];
                $_SESSION['first_name'] = $employeeData['first_name'];
                $_SESSION['last_name'] = $employeeData['last_name'];
                $_SESSION['position'] = $employeeData['position'];
                header("Location: home/index.php");
                exit();
            } else {
                $_SESSION['errorMessage'] = "<div class='feedback error'>Invalid username or password</div>";
                header("Location: login.php");
                exit();
            }
        } else {
            $_SESSION['errorMessage'] = "<div class='feedback error'>Invalid username or password</div>";
            header("Location: login.php");
            exit();
        }

        $stmt->close();
    }