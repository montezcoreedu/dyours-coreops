<?php
    include("../common/dbconnection.php");
    include("../common/session.php");

    if (!empty($_GET['eid'])) {
        include("../common/employee_lookup.php");
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
            $start_date = date("Y-m-d", strtotime("-3 months"));
            $end_date = date("Y-m-d");

            $stmt = $conn->prepare("SELECT date, status
                FROM attendance
                WHERE employee_id = ? AND date BETWEEN ? AND ?
                ORDER BY date desc");
            $stmt->bind_param("iss", $employee_id, $start_date, $end_date);
            $stmt->execute();
            $result = $stmt->get_result();

            $percent_stmt = $conn->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count
                FROM attendance
                WHERE employee_id = ? AND date BETWEEN ? AND ?");
            $percent_stmt->bind_param("iss", $employee_id, $start_date, $end_date);
            $percent_stmt->execute();
            $percent_result = $percent_stmt->get_result();
            $percent = $percent_result->fetch_assoc();

            $total = (int) $percent['total'];
            $present = (int) $percent['present_count'];
            $percentage = ($total > 0) ? round(($present / $total) * 100) : 0;

            if ($result->num_rows === 0) {
                echo "
                <div class='empty-state'>
                    <img src='../images/empty-attendance.png' alt='No attendance records'>
                    <p>No meeting attendance recorded yet</p>
                </div>
                ";
            } else {
                if ($percentage >= 75) {
                    $percentage_color = "rgb(6, 116, 32)";
                } elseif ($percentage >= 50) {
                    $percentage_color = "rgb(116, 94, 6)";
                } else {
                    $percentage_color = "rgb(116, 6, 6)";
                }

                $start_label = date("F", strtotime($start_date));
                $end_label = date("F", strtotime($end_date));
                echo "<div class='caption'>Meeting Attendance: $start_label - $end_label</div>";

                echo "
                <div class='progress'>
                    <div class='progress-bar' style='width: " . $percentage . "%; background-color:  " . $percentage_color . "'></div>
                </div>
                <div style='font-size: 0.85rem; color: " . $percentage_color . "; text-align: center; margin-bottom: 1rem;'>
                    " . $percentage . "% Meetings Attended
                </div>
                ";

                echo "<table>";
                echo "<thead>";
                echo "<tr>";
                echo "<th align='left'>Date</th>";
                echo "<th align='left'>Attendance</th>";
                echo "</tr>";
                echo "</thead>";
                while ($row = mysqli_fetch_assoc($result)) {
                    $date = date('F j, Y', strtotime($row['date']));
                    $status = htmlspecialchars($row['status']);

                    if ($status == "present") {
                        $icon = "fa-circle-check";
                    } elseif ($status == "tardy") {
                        $icon = "fa-clock";
                    } else {
                        $icon = "fa-circle-xmark";
                    }

                    echo "<tr>";
                    echo "<td>" . $date . "</td>";
                    echo "<td class='att-" . $status . "'><i class='fa-solid " . $icon . "'></i> " . ucfirst($status) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }

            $percent_stmt->close();
            $stmt->close();
        ?>
    </div>
</body>
</html>