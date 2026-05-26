<?php
    include("../common/dbconnection.php");
    session_start();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $timesheet_id = (int) $_POST['timesheet_id'];
        $work_date_input = trim($_POST['work_date']);
        $clock_in = trim($_POST['clock_in']);
        $clock_out = trim($_POST['clock_out']);
        $work_date = date("Y-m-d", strtotime($work_date_input));
        $start = strtotime($work_date . ' ' . $clock_in);
        $end = strtotime($work_date . ' ' . $clock_out);
        if ($end < $start) {
            $end = strtotime('+1 day', $end);
        }
        $total_hours = round(($end - $start) / 3600, 2);

        $period_stmt = $conn->prepare("SELECT p.start_date, p.end_date
            FROM timesheets t
            INNER JOIN pay_periods p
                ON t.pay_period_id = p.pay_period_id
            WHERE t.timesheet_id = ?");
        $period_stmt->bind_param("i", $timesheet_id);
        $period_stmt->execute();
        $period_result = $period_stmt->get_result();
        $period = $period_result->fetch_assoc();

        $period_start = $period['start_date'];
        $period_end = $period['end_date'];

        if ($work_date < $period_start || $work_date > $period_end) {
            echo json_encode([
                "success" => false,
                "message" => "Work date must be within the pay period (" . date('M j, Y', strtotime($period_start)) . " - " . date('M j, Y', strtotime($period_end)) . ")."
            ]);
            exit();
        }

        $stmt = $conn->prepare("INSERT INTO timesheet_entries (timesheet_id, work_date, clock_in, clock_out, hours_worked, created_on) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("isssd", $timesheet_id, $work_date, $clock_in, $clock_out, $total_hours);

        if ($stmt->execute()) {
            $update = $conn->prepare("UPDATE timesheets SET total_hours = total_hours + ?, updated_on = NOW() WHERE timesheet_id = ?");
            $update->bind_param("di", $total_hours, $timesheet_id);
            $update->execute();

            $get_totals = $conn->prepare("SELECT total_hours
                FROM timesheets
                WHERE timesheet_id = ?");
            $get_totals->bind_param("i", $timesheet_id);
            $get_totals->execute();
            $result = $get_totals->get_result();
            $sheet = $result->fetch_assoc();

            $new_total_hours = (float) $sheet['total_hours'];

            $get_rate = $conn->prepare("SELECT p.pay_rate
                FROM timesheets t
                INNER JOIN employees e ON t.employee_id = e.employee_id
                INNER JOIN positions p ON e.position_id = p.position_id
                WHERE t.timesheet_id = ?");
            $get_rate->bind_param("i", $timesheet_id);
            $get_rate->execute();

            $rate_result = $get_rate->get_result();
            $rate_row = $rate_result->fetch_assoc();

            $pay_rate = (float) $rate_row['pay_rate'];

            $new_gross_pay = $new_total_hours * $pay_rate;

            echo json_encode([
                "success" => true,
                "message" => "Timesheet hours added successfully",
                "total_hours" => number_format($new_total_hours, 2),
                "gross_pay" => number_format($new_gross_pay, 2)
            ]);

            $update->close();
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Error adding timesheet hours"
            ]);
        }

        $stmt->close();
    }