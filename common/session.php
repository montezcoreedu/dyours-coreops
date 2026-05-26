<?php
    session_start();

    $session_id = $_SESSION['employee_id'];
    $employee_name = $_SESSION['first_name'] . " " . $_SESSION['last_name'];

    $account_sql = "SELECT p.sales_adjustment, p.lookup_access, p.sales_access,
        p.attendance_access, p.timesheet_access, p.settings_access
        FROM employees e
        INNER JOIN positions p ON e.position_id = p.position_id
        WHERE employee_id = '$session_id'";
    $account_result = mysqli_query($conn, $account_sql);
    $account = mysqli_fetch_assoc($account_result);

    $sales_adjustment = $account['sales_adjustment'];
    $lookup_access = $account['lookup_access'];
    $sales_access = $account['sales_access'];
    $attendance_access = $account['attendance_access'];
    $timesheet_access = $account['timesheet_access'];
    $settings_access = $account['settings_access'];

    if (!isset($_SESSION['employee_id'])) {
        header("Location: ../login.php");
        exit();
    }
