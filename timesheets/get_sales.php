<?php
    include("../common/dbconnection.php");

    $timesheet_id = (int) $_POST['timesheet_id'];

    $stmt = $conn->prepare("SELECT work_date, sales_amount
        FROM sales_entries
        WHERE timesheet_id = ?
        ORDER BY work_date desc");
    $stmt->bind_param("i", $timesheet_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo '<p style="text-align: center;">No sales found for this timesheet.</p>';
    } else {
        echo "<table class='card-table'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th align='left'>Date</th>";
        echo "<th align='left'>Sales Amount</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        while ($row = $result->fetch_assoc()) {
            $work_date = date('F j, Y', strtotime($row['work_date']));
            $sales_amount = number_format((float) $row['sales_amount'], 2, '.', '');

            echo "<tr>";
            echo "<td>" . $work_date . "</td>";
            echo "<td>$" . $sales_amount . "</td>";
            echo "</tr>";
        }
        echo '</tbody>';
        echo '</table>';
    }