<?php
    include("../common/dbconnection.php");

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $start_date_input = trim($_POST['start_date']);
        $end_date_input = trim($_POST['end_date']);
        $due_date_input = trim($_POST['due_date']);
        $start_date = date("Y-m-d", strtotime($start_date_input));
        $end_date = date("Y-m-d", strtotime($end_date_input));
        $due_date = date("Y-m-d", strtotime($due_date_input));

        $stmt = $conn->prepare("INSERT INTO pay_periods (start_date, end_date, due_date, created_on, updated_on) VALUES (?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("sss", $start_date, $end_date, $due_date);

        if ($stmt->execute()) {
            echo json_encode([
                "success" => true,
                "message" => "Timesheet added successfully"
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Error adding timesheet"
            ]);
        }

        $stmt->close();
    }