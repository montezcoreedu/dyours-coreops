<?php
    include("../common/dbconnection.php");

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $session_id = $_POST['session_id'];
        $pay_period_id = $_POST['pay_period_id'];

        $stmt = $conn->prepare("INSERT INTO timesheets (employee_id, pay_period_id, status, created_on, updated_on) VALUES (?, ?, 'draft', NOW(), NOW())");
        $stmt->bind_param("ii", $session_id, $pay_period_id);

        if ($stmt->execute()) {
            header("Location: ../home/");
            exit();
        } else {
            echo "Error adding timesheet";
        }

        $stmt->close();
    }