<?php
    include("../common/dbconnection.php");
    include("../common/session.php");

    if (!empty($_GET['eid'])) {
        include("../common/employee_lookup.php");

        $rate_stmt = $conn->prepare("SELECT p.pay_rate, p.sales_adjustment
            FROM employees e
            INNER JOIN positions p ON e.position_id = p.position_id
            LEFT JOIN timesheets t ON e.employee_id = t.employee_id
            WHERE e.employee_id = ?");
        $rate_stmt->bind_param("i", $employee_id);
        $rate_stmt->execute();
        $rate_result = $rate_stmt->get_result();
        $rate = $rate_result->fetch_assoc();
    } else {
        header("Location: ../home/index.php");
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
    <div id="content-wrapper">
        <a href="../home/" style="display: block;"><i class="fa-solid fa-angle-left"></i> Back to Search</a>
        <?php include("../common/emp_header.php"); ?>
        <?php
            $stmt = $conn->prepare("SELECT t.timesheet_id, t.total_hours, t.status, 
                t.submitted_on, p.start_date, p.end_date, p.due_date
                FROM timesheets t
                INNER JOIN pay_periods p ON t.pay_period_id = p.pay_period_id
                WHERE t.employee_id = ?
                ORDER BY p.end_date desc");
            $stmt->bind_param("i", $employee_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                echo "
                <div class='empty-state'>
                    <img src='../images/empty-timesheet.png' alt='No timesheet records'>
                    <p>No timesheets recorded yet</p>
                </div>
                ";
            } else {
                echo "<table style='margin-bottom: 2rem;'>";
                echo "<thead>";
                echo "<tr>";
                echo "<th class='sortable' align='left'>Date <i class='fa fa-sort'></i></th>";
                echo "<th class='sortable' align='left'>Total pay <i class='fa fa-sort'></i></th>";
                echo "<th class='sortable' align='left'>Cumulative hrs <i class='fa fa-sort'></i></th>";
                echo "<th class='sortable' align='left'>Status <i class='fa fa-sort'></i></th>";
                echo "</tr>";
                echo "</thead>";
                echo "<tbody>";
                while ($row = $result->fetch_assoc()) {
                    $timesheet_id = (int) $row['timesheet_id'];
                    $start_date = date('n/j/Y', strtotime($row['start_date']));
                    $end_date = date('n/j/Y', strtotime($row['end_date']));
                    $cumulative_hours = (float) $row['total_hours'];
                    $status = htmlspecialchars($row['status']);
                    $submitted_on = date('Y-m-d', strtotime($row['submitted_on'] ?? ''));
                    $due_date = date('Y-m-d', strtotime($row['due_date']));

                    if ($status == 'draft' && $submitted_on == null && $due_date < date('Y-m-d')) {
                        $due_status = "background-color: rgb(255, 235, 235);";
                        $overdue_icon = "<span title='Overdue' style='font-size: 1rem; color: rgb(116, 6, 6); margin-left: 4px;'><i class='fa-solid fa-clock'></i></span>";
                    } else {
                        $due_status = "background-color: transparent;";
                        $overdue_icon = "";
                    }

                    $total_sales = 0;
                    if ($rate['sales_adjustment']) {
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
                        $total_pay = $cumulative_hours * $rate['pay_rate'];
                    }

                    echo "<tr>";
                    echo "<td>" . $start_date . " - " . $end_date . "</td>";
                    echo "<td>$" . number_format($total_pay, 2) . "</td>";
                    echo "<td>" . number_format($cumulative_hours, 2) . "</td>";
                    echo "<td><div class='status " . $status . "'>" . $status . "</div></td>";
                    echo "</tr>";
                }
                echo "</tbody>";
                echo "</table>";
            }
        ?>
    </div>
    <script>
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
    </script>
</body>
</html>