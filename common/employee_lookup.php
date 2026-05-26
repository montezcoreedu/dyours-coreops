<?php
    $employee_id = (int) $_GET['eid'];
    $stmt = $conn->prepare("SELECT e.*, p.position_name AS position
        FROM employees e
        INNER JOIN positions p ON e.position_id = p.position_id
        WHERE employee_id = ?");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $employeeResult = $stmt->get_result();

    if ($employeeResult->num_rows > 0) {
        $employeeData = $employeeResult->fetch_assoc();
        $employeeName = "{$employeeData['first_name']} {$employeeData['last_name']}";
        $employeeFirst = $employeeData['first_name'];
        $employeeLast = $employeeData['last_name'];
        $employeeEmail = $employeeData['email'];
        $employeePhone = $employeeData['phone'];
        $employeePosition = $employeeData['position_id'];
        $employeeDisableLogin = $employeeData['disable_login'] ? 1 : 0;
        $employeeStatus = $employeeData['hire_status'];
        $employeeUsername = $employeeData['username'];
        $employeePassword = $employeeData['password'];
    } else {
        $_SESSION['errorMessage'] = "<div class='feedback error'>Error selecting employee</div>";
        header("Location: ../home/index.php");
        exit();
    }

    $stmt->close();
    