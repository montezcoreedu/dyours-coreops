<?php
    include("../common/dbconnection.php");

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $employee_id = (int) $_POST['assigned_to'];
        $task_name = trim($_POST['task_name']);
        $due_date = date('Y-m-d', strtotime($_POST['due_date']));
        
        $stmt = $conn->prepare("INSERT INTO tasks (assigned_to, task_name, due_date, completed, created_on, updated_on) VALUES (?, ?, ?, 2, NOW(), NOW())");
        $stmt->bind_param("iss", $employee_id, $task_name, $due_date);

        if ($stmt->execute()) {
            echo json_encode([
                "success" => true,
                "message" => "Task added successfully"
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Error adding task"
            ]);
        }

        $stmt->close();
    }