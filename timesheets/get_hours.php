<?php
    include("../common/dbconnection.php");

    $timesheet_id = (int) $_POST['timesheet_id'];

    $stmt = $conn->prepare("SELECT work_date, clock_in, clock_out, hours_worked
        FROM timesheet_entries
        WHERE timesheet_id = ?
        ORDER BY work_date desc");
    $stmt->bind_param("i", $timesheet_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo '<p style="text-align: center;">No hours found for this timesheet.</p>';
    } else {
        echo "<table class='card-table'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th align='left'>Date</th>";
        echo "<th align='left'>Clock In</th>";
        echo "<th align='left'>Clock Out</th>";
        echo "<th align='left'>Hours Worked</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        while ($row = $result->fetch_assoc()) {
            $work_date = date('F j, Y', strtotime($row['work_date']));
            $clock_in = date('g:i A', strtotime($row['clock_in']));
            $clock_out = date('g:i A', strtotime($row['clock_out']));
            $hours_worked = number_format((float) $row['hours_worked'], 2, '.', '');

            echo "<tr>";
            echo "<td>" . $work_date . "</td>";
            echo "<td>" . $clock_in . "</td>";
            echo "<td>" . $clock_out . "</td>";
            echo "<td>" . $hours_worked . "</td>";
            echo "</tr>";
        }
        echo '</tbody>';
        echo '</table>';
    }