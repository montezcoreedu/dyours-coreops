<?php
    include("../common/dbconnection.php");
    session_start();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $attendance_data = $_POST['attendance'];
        $date = $_POST['att_date'];

        $stmt = $conn->prepare("UPDATE attendance SET status = ?, updated_on = NOW() WHERE employee_id = ? AND date = ?");

        $success = true;
        
        foreach ($attendance_data as $employee_id => $status) {
            $employee_id = (int) $employee_id;
            $status = trim($status);

            $stmt->bind_param("sis", $status, $employee_id, $date);
            
            if (!$stmt->execute()) {
                $success = false;
                break;
            }
        }

        $stmt->close();

        $_SESSION['message'] = [
            'type' => $success ? 'success' : 'error',
            'text' => $success
                ? 'Meeting attendance updated successfully'
                : 'Failed to update meeting attendance'
        ];

        header("Location: view.php?date=" . $date);
        exit();
    }