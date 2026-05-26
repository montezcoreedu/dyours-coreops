<?php
    include("../common/dbconnection.php");
    session_start();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $tid = (int) $_GET['tid'];
        $timesheets = explode(',', $_GET['employees']);

        $success = true;

        foreach ($timesheets as $timesheet_id) {
            $timesheet_id = (int) $timesheet_id;

            $stmt = $conn->prepare("UPDATE timesheets SET status = 'approved', approved_on = NOW(), updated_on = NOW() WHERE timesheet_id = ?");

            $stmt->bind_param("i", $timesheet_id);

            if (!$stmt->execute()) {
                $success = false;
                break;
            }

            $stmt->close();
        }

        if ($success) {
            $_SESSION['message'] = [
                'type' => 'success',
                'text' => 'Timesheets approved successfully'
            ];
        } else {
            $_SESSION['message'] = [
                'type' => 'error',
                'text' => 'Error approving timesheets'
            ];
        }

        header("Location: ../timesheets/view_sheet.php?tid=" . $tid);
        exit();
    }