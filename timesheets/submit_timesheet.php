<?php
    include("../common/dbconnection.php");
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $timesheet_id = (int) $_GET['timesheet_id'];

        $stmt = $conn->prepare("UPDATE timesheets SET status = 'submitted', submitted_on = NOW(), updated_on = NOW() WHERE timesheet_id = ?");
        $stmt->bind_param("i", $timesheet_id);

        if ($stmt->execute()) {
            echo json_encode([
                "success" => true,
                "message" => "Timesheet submitted successfully"
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Error submitting timesheet"
            ]);
        }

        exit();
    }