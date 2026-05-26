<?php
    include("../common/dbconnection.php");

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $position = trim($_POST['position']);

        $stmt = $conn->prepare("INSERT INTO employees (first_name, last_name, email, phone, position_id, created_on, updated_on) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("ssssi", $first_name, $last_name, $email, $phone, $position);

        if ($stmt->execute()) {
            echo json_encode([
                "success" => true,
                "message" => "Employee added successfully"
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Error adding employee"
            ]);
        }

        $stmt->close();
    }