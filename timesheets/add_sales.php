<?php
    include("../common/dbconnection.php");

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $timesheet_id = (int) $_POST['timesheet_id'];
        $employee_id = (int) $_POST['employee_id'];
        $work_date_input = trim($_POST['work_date']);
        $sales_amount = (float) $_POST['sales_amount'];
        $work_date = date("Y-m-d", strtotime($work_date_input));

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

        $stmt = $conn->prepare("INSERT INTO sales_entries (timesheet_id, employee_id, work_date, sales_amount, created_on) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("iisd", $timesheet_id, $employee_id, $work_date, $sales_amount);

        if ($stmt->execute()) {
            $get_sales = $conn->prepare("SELECT SUM(sales_amount) AS total_sales
                FROM sales_entries
                WHERE timesheet_id = ?");
            $get_sales->bind_param("i", $timesheet_id);
            $get_sales->execute();
            $sales_result = $get_sales->get_result();
            $sales = $sales_result ? $sales_result->fetch_assoc() : null;

            $total_sales = isset($sales['total_sales']) ? (float) $sales['total_sales']  : 0;

            if ($total_sales <= 125) {
                $gross_pay = $total_sales * 0.10;
            } elseif ($total_sales <= 225) {
                $gross_pay = $total_sales * 0.20;
            } else {
                $gross_pay = $total_sales * 0.25;
            }

            echo json_encode([
                "success" => true,
                "message" => "Sales data added successfully",
                "total_sales" => $total_sales,
                "gross_pay" => $gross_pay
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Error adding sales data"
            ]);
        }

        $stmt->close();
    }