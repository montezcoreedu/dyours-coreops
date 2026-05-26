<?php
    include("../common/dbconnection.php");
    session_start();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $date = date('Y-m-d', strtotime($_POST['att_date']));
        $attendance_data = $_POST['attendance'];
        
        $stmt = $conn->prepare("INSERT INTO attendance (employee_id, date, status, created_on, updated_on) VALUES (?, ?, ?, NOW(), NOW())");
        
        $success = true;

        foreach ($attendance_data as $employee_id => $status) {
            $employee_id = (int) $employee_id;
            $status = trim($status);

            $stmt->bind_param("iss", $employee_id, $date, $status);

            if (!$stmt->execute()) {
                $success = false;
                break;
            }
        }

        $stmt->close();

        $_SESSION['message'] = [
            'type' => $success ? 'success' : 'error',
            'text' => $success
                ? 'Meeting attendance recorded successfully'
                : 'Failed to record meeting attendance'
        ];

        header("Location: index.php");
        exit();
    }