<?php
    include("../common/dbconnection.php");

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $position_id = (int) $_POST['position_id'];
        $position_name = trim($_POST['position_name']);
        $pay_rate = (float) $_POST['pay_rate'];
        $sales_adjustment = (int) $_POST['sales_adjustment'];
        $lookup_access = (int) $_POST['lookup_access'];
        $sales_access = (int) $_POST['sales_access'];
        $attendance_access = (int) $_POST['attendance_access'];
        $timesheet_access = (int) $_POST['timesheet_access'];
        $settings_access = (int) $_POST['settings_access'];

        $stmt = $conn->prepare("UPDATE positions SET position_name = ?, pay_rate = ?, sales_adjustment = ?, lookup_access = ?, sales_access = ?, attendance_access = ?, timesheet_access = ?, settings_access = ? WHERE position_id = ?");
        $stmt->bind_param("sdiiiiiii", $position_name, $pay_rate, $sales_adjustment, $lookup_access, $sales_access, $attendance_access, $timesheet_access, $settings_access, $position_id);

        if ($stmt->execute()) {
            echo json_encode([
                "success" => true,
                "message" => "Position updated successfully"
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Error updating position"
            ]);
        }

        $stmt->close();
    }