<?php
    include("../common/dbconnection.php");
    include("../common/session.php");

    if (isset($_GET['tid'])) {
        $timesheet_id = (int) $_GET['tid'];

        $stmt = $conn->prepare("SELECT start_date, end_date, due_date,
            SUM(total_hours) as hours_worked
            FROM pay_periods p
            INNER JOIN timesheets t ON p.pay_period_id = t.pay_period_id
            WHERE p.pay_period_id = ?
            GROUP BY p.pay_period_id");
        $stmt->bind_param("i", $timesheet_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows) {
            $timesheet = $result->fetch_assoc();

             $count_stmt = $conn->prepare("SELECT COUNT(*) AS total_employees
                FROM timesheets
                WHERE pay_period_id = ?");
            $count_stmt->bind_param("i", $timesheet_id);
            $count_stmt->execute();
            $count_result = $count_stmt->get_result();
            $count_data = $count_result->fetch_assoc();
            $employees_count = (int) $count_data['total_employees'];

            $start_date = date('D, M j', strtotime($timesheet['start_date']));
            $end_date = date('D, M j', strtotime($timesheet['end_date']));
            $due_date = date('Y-m-d', strtotime($timesheet['due_date']));
            $due_date_display = date('D, M j', strtotime($timesheet['due_date']));
            $hours_worked = (float) $timesheet['hours_worked'];

            $total_payroll = 0;
            $payroll_stmt = $conn->prepare("SELECT e.employee_id, p.pay_rate, p.sales_adjustment,
                t.total_hours
                FROM employees e
                INNER JOIN positions p ON e.position_id = p.position_id
                INNER JOIN timesheets t ON e.employee_id = t.employee_id
                WHERE t.pay_period_id = ?");
            $payroll_stmt->bind_param("i", $timesheet_id);
            $payroll_stmt->execute();
            $payroll_result = $payroll_stmt->get_result();

            while ($employee = $payroll_result->fetch_assoc()) {
                $employee_id = (int) $employee['employee_id'];
                $pay_rate = (float) $employee['pay_rate'];
                $total_hours = (float) $employee['total_hours'];
                $employee_pay = 0;

                if ($employee['sales_adjustment']) {
                    $sales_stmt = $conn->prepare("SELECT SUM(sales_amount) AS total_sales
                        FROM sales_entries
                        WHERE employee_id = ?");
                    $sales_stmt->bind_param("i", $employee_id);
                    $sales_stmt->execute();
                    $sales_result = $sales_stmt->get_result();
                    $sales = $sales_result->fetch_assoc();

                    $total_sales = (float) ($sales['total_sales'] ?? 0);

                    if ($total_sales <= 125) {
                        $employee_pay = $total_sales * 0.10;
                    } elseif ($total_sales <= 225) {
                        $employee_pay = $total_sales * 0.20;
                    } else {
                        $employee_pay = $total_sales * 0.25;
                    }
                } else {
                    $employee_pay = $total_hours * $pay_rate;
                }

                $total_payroll += $employee_pay;
            }
        } else {
            header("Location: index.php");
            exit();
        }

        $stmt->close();
    } else {
        header("Location: index.php");
        exit();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Document</title>
    <?php include("../common/head.php"); ?>
</head>
<body>
    <?php include("../common/sidebar.php"); ?>
    <?php if (isset($_SESSION['message'])): ?>
    <div id="message" class="<?php echo $_SESSION['message']['type']; ?> show">
        <i class="fa-solid <?php echo $_SESSION['message']['type'] === 'success'
            ? 'fa-circle-check'
            : 'fa-circle-exclamation'; ?>"></i>
        <span><?php echo htmlspecialchars($_SESSION['message']['text']); ?></span>
    </div>
    <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
    <div id="content-wrapper">
        <a href="../timesheets/" style="display: block;"><i class="fa-solid fa-angle-left"></i> Back to Timesheets</a>
        <h2>Payroll: <?php echo $start_date . " - " . $end_date; ?></h2>
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem;">
            <div class="sheet-info">
                <i class="fa-solid fa-calendar-day"></i>
                <div>
                    <span class="title">Due Date</span>
                    <span class="value"><?php echo $due_date_display; ?></span>
                </div>
            </div>
            <div class="sheet-grid">
                <div class="sheet-item">
                    <div class="item-value"><?php echo $employees_count; ?></div>
                    <div class="item-label">Employees</div>
                </div>
                <div style='width: 1px; height: 32px; background-color: rgb(208, 208, 208); margin: 0 0.92rem;'></div>
                <div class="sheet-item">
                    <div class="item-value"><?php echo $hours_worked ? number_format($hours_worked, 2) : "0.00"; ?></div>
                    <div class="item-label">Total Hours Worked</div>
                </div>
                <div style='width: 1px; height: 32px; background-color: rgb(208, 208, 208); margin: 0 0.92rem;'></div>
                <div class="sheet-item">
                    <div class="item-value">$<?php echo $total_payroll ? number_format($total_payroll, 2) : "0.00"; ?></div>
                    <div class="item-label">Total Earnings</div>
                </div>
            </div>
        </div>
        <div style="display: flex; align-items: center; margin-bottom: 2rem;">
            <a href="#" class="btn" onclick="approveSheets(<?php echo $timesheet_id; ?>)" style="margin-right: 10px;"><i class="fa-solid fa-circle-check"></i> Approve</a>
            <a href="#" class="btn" onclick="rejectSheets(<?php echo $timesheet_id; ?>)"><i class="fa-solid fa-circle-xmark"></i> Reject</a>
        </div>
        <?php
            $stmt = $conn->prepare("SELECT e.employee_id, e.first_name, e.last_name, p.position_name,
                p.pay_rate, p.sales_adjustment, t.timesheet_id, t.total_hours, t.status,
                t.submitted_on
                FROM employees e
                INNER JOIN positions p ON e.position_id = p.position_id
                LEFT JOIN timesheets t ON e.employee_id = t.employee_id
                WHERE t.pay_period_id = ?
                ORDER BY e.last_name asc, e.first_name asc");
            $stmt->bind_param("i", $timesheet_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $total_employees = 0;

            if ($result->num_rows) {
                echo "<table class='timesheet-table' id='timesheetTable' style='margin-bottom: 2rem;'>";
                echo "<thead>";
                echo "<tr>";
                echo "<th></th>";
                echo "<th class='sortable' align='left'>Employees <i class='fa fa-sort'></i></th>";
                echo "<th class='sortable' align='left'>Role <i class='fa fa-sort'></i></th>";
                echo "<th class='sortable' align='left'>Status <i class='fa fa-sort'></i></th>";
                echo "<th class='sortable' align='left'>Total pay <i class='fa fa-sort'></i></th>";
                echo "<th class='sortable' align='left'>Rate <i class='fa fa-sort'></i></th>";
                echo "<th class='sortable' align='left'>Cumulative hrs <i class='fa fa-sort'></i></th>";
                echo "</tr>";
                echo "</thead>";
                echo "<tbody>";
                while ($row = $result->fetch_assoc()) {
                    $employee_sheet = (int) $row['timesheet_id'];
                    $employee_id = (int) $row['employee_id'];
                    $employee_name = $row['last_name'] . ", " . $row['first_name'];
                    $position = htmlspecialchars($row['position_name']);
                    $status = htmlspecialchars($row['status']);
                    $cumulative_hours = (float) $row['total_hours'];
                    $pay_rate = (float) $row['pay_rate'];
                    $submitted_on = $row['submitted_on'];

                    if ($status == 'draft' && $submitted_on == null && $due_date < date('Y-m-d')) {
                        $due_status = "background-color: rgb(255, 235, 235);";
                        $overdue_icon = "<span title='Overdue' style='font-size: 1rem; color: rgb(116, 6, 6); margin-left: 4px;'><i class='fa-solid fa-clock'></i></span>";
                    } else {
                        $due_status = "background-color: transparent;";
                        $overdue_icon = "";
                    }

                    $total_sales = 0;
                    if ($row['sales_adjustment']) {
                        $stmt = $conn->prepare("SELECT SUM(sales_amount) AS total_sales
                            FROM sales_entries s
                            INNER JOIN timesheets t ON t.timesheet_id = s.timesheet_id
                            WHERE s.employee_id = ? AND t.pay_period_id = ?
                            LIMIT 1");
                        $stmt->bind_param("ii", $employee_id, $timesheet_id);
                        $stmt->execute();
                        $sales = $stmt->get_result();
                        $total_sales = (float) $sales->fetch_assoc()['total_sales'];

                        if ($total_sales <= 125) {
                            $total_pay = $total_sales * 0.10;
                        } elseif ($total_sales >= 126 && $total_sales <= 225) {
                            $total_pay = $total_sales * 0.20;
                        } else {
                            $total_pay = $total_sales * 0.25;
                        }
                    } else {
                        $total_pay = $cumulative_hours * $pay_rate;
                    }

                    if ($row['sales_adjustment']) {
                        if ($total_sales <= 125) {
                            $pay_rate_display = '10% of sales';
                        } elseif ($total_sales >= 126 && $total_sales <= 225) {
                            $pay_rate_display = '20% of sales';
                        } else {
                            $pay_rate_display = '25% of sales';
                        }
                    } else {
                        $pay_rate_display = "$" . number_format($pay_rate, 2) . "/hr";
                    }

                    echo "<tr style='" . $due_status . "'>";
                    echo "<td align='center'><input type='checkbox' class='employee-check' name='employees[]' value='" . $employee_sheet . "' style='margin-top: 5px;'></td>";
                    echo "<td>" . $employee_name . " " . $overdue_icon . "</td>";
                    echo "<td>" . $position . "</td>";
                    echo "<td><div class='status " . $status . "'>" . $status . "</div></td>";
                    echo "<td>$" . number_format($total_pay, 2) . "</td>";
                    echo "<td>" . $pay_rate_display . "</td>";
                    echo "<td>" . number_format($cumulative_hours, 2) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p style='text-align: center;'>No timesheets found for this pay period.</p>";
            }
        ?>
    </div>
    <script>
        function showMessage(type, text) {
            let icon = '';

            if (type === 'success') {
                icon = 'fa-circle-check';
            } else {
                icon = 'fa-circle-exclamation';
            }

            $('#message')
                .removeClass('success error')
                .addClass(type)
                .html(`
                    <i class="fa-solid ${icon}"></i>
                    <span>${text}</span>
                `)
                .addClass('show');
            setTimeout(() => {
                $('#message').removeClass('show');
            }, 4000);
        }

        $(document).ready(function() {
            if ($('#message').hasClass('show')) {
                setTimeout(() => {
                    $('#message').removeClass('show');
                }, 4000);
            }
        });

        $(document).ready(function () {
            let sortState = {};

            $("#timesheetTable tbody tr").each(function (index) {
                $(this).attr("data-original-index", index);
            });

            $(".sortable").click(function () {
                const table = $(this).closest("table");
                const tbody = table.find("tbody");
                const index = $(this).index();
                let rows = tbody.find("tr").toArray();

                sortState[index] = (sortState[index] || 0) + 1;
                if (sortState[index] > 2) sortState[index] = 0;

                if (sortState[index] === 0) {
                    rows.sort((a, b) => {
                        return $(a).data("original-index") - $(b).data("original-index");
                    });

                } else {
                    rows.sort(function (a, b) {
                        let valA = $(a).children("td").eq(index).text().trim();
                        let valB = $(b).children("td").eq(index).text().trim();

                        valA = valA.replace(/[$,]/g, "");
                        valB = valB.replace(/[$,]/g, "");

                        if (!isNaN(valA) && !isNaN(valB)) {
                            return sortState[index] === 1 ? valA - valB : valB - valA;
                        }

                        let dateA = new Date(valA);
                        let dateB = new Date(valB);
                        if (!isNaN(dateA) && !isNaN(dateB)) {
                            return sortState[index] === 1 ? dateA - dateB : dateB - dateA;
                        }

                        return sortState[index] === 1
                            ? valA.localeCompare(valB)
                            : valB.localeCompare(valA);
                    });
                }

                tbody.empty().append(rows);

                $(".sortable i")
                    .removeClass("fa-sort-up fa-sort-down")
                    .addClass("fa-sort");

                if (sortState[index] === 1) {
                    $(this).find("i")
                        .removeClass("fa-sort")
                        .addClass("fa-sort-up");
                } else if (sortState[index] === 2) {
                    $(this).find("i")
                        .removeClass("fa-sort")
                        .addClass("fa-sort-down");
                }
            });
        });

        function approveSheets(timesheetId) {
            let employees = [];
            $('.employee-check:checked').each(function() {
                employees.push($(this).val());
            });

            if (employees.length === 0) {
                alert("Please select at least one employee.");
                return;
            }

            let confirmSubmit = confirm(
                "Are you sure you want to approve these timesheets?"
            );

            if (confirmSubmit) {
                window.location.href =
                    "../timesheets/approve_sheets.php?tid="
                        + <?php echo $timesheet_id; ?>
                        + "&employees="
                        + employees.join(',');
            }
        }

        function rejectSheets(timesheetId) {
            let employees = [];
            $('.employee-check:checked').each(function() {
                employees.push($(this).val());
            });

            if (employees.length === 0) {
                alert("Please select at least one employee.");
                return;
            }

            let confirmSubmit = confirm(
                "Are you sure you want to reject these timesheets?"
            );

            if (confirmSubmit) {
                window.location.href =
                    "../timesheets/reject_sheets.php?tid="
                        + <?php echo $timesheet_id; ?>
                        + "&employees="
                        + employees.join(',');
            }
        }
    </script>
</body>
</html>