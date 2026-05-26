<?php
    include("../common/dbconnection.php");

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $employee_id = (int) $_POST['employee_id'];
        $date_input = trim($_POST['date']);
        $date = date("Y-m-d", strtotime($date_input));
        $type = trim($_POST['type']);
        $reason = trim($_POST['reason']);
        $action_taken = trim($_POST['action_taken']);
        $acknowledged = (int) $_POST['acknowledged'];

        $stmt = $conn->prepare("INSERT INTO discipline (employee_id, date, type, reason, action_taken, acknowledged, created_on, updated_on) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("issssi", $employee_id, $date, $type, $reason, $action_taken, $acknowledged);

        if ($stmt->execute()) {
            echo json_encode([
                "success" => true,
                "message" => "Discipline record added successfully"
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Error adding discipline record"
            ]);
        }

        $stmt->close();
    }