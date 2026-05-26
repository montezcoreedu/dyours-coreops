<?php
    include("../common/dbconnection.php");

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $position_name = trim($_POST['position_name']);
        $pay_rate = (float) $_POST['pay_rate'];
        $sales_adjustment = (int) $_POST['sales_adjustment'];
        $lookup_access = (int) $_POST['lookup_access'];
        $sales_access = (int) $_POST['sales_access'];
        $attendance_access = (int) $_POST['attendance_access'];
        $timesheet_access = (int) $_POST['timesheet_access'];
        $settings_access = (int) $_POST['settings_access'];

        $stmt = $conn->prepare("INSERT INTO positions (position_name, pay_rate, sales_adjustment, lookup_access, sales_access, attendance_access, timesheet_access, settings_access, created_on, updated_on) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("sdiiiiii", $position_name, $pay_rate, $sales_adjustment, $lookup_access, $sales_access, $attendance_access, $timesheet_access, $settings_access);

        if ($stmt->execute()) {
            echo json_encode([
                "success" => true,
                "message" => "Position added successfully"
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Error adding position"
            ]);
        }

        $stmt->close();
    }