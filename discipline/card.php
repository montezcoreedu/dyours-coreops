<div class="card">
    <div class="title">
        <h3>Discipline</h3>
    </div>
    <div class="content">
        <?php
        $stmt = $conn->prepare("SELECT date, reason
            FROM discipline
            WHERE employee_id = ?
            ORDER BY date desc");
        $stmt->bind_param("i", $session_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $discipline_count = $result->num_rows;

        if ($result->num_rows === 0) {
            echo "
            <div class='empty-state'>
                <img src='../images/empty-discipline.png' alt='No discipline records'>
                <p>No discipline records found</p>
            </div>
            ";
        } else {
            $discipline_percentage = min(100, ($discipline_count / 5) * 100);
            if ($discipline_percentage >= 80) {
                $progress_class = "unsatisfactory";
                $progress_color = "rgb(116, 6, 6)";
            } elseif ($discipline_percentage >= 60) {
                $progress_class = "satisfactory";
                $progress_color = "rgb(116, 94, 6)";
            } else {
                $progress_class = "on-track";
                $progress_color = "rgb(6, 116, 32)";
            }

            echo "<div class='progress-container'>";
                echo "<div class='progress'>";
                    echo "<div class='progress-bar " . $progress_class . "' style='width: $discipline_percentage%; background-color: $progress_color;'></div>";
                echo "</div>";
                echo "<div class='progress-label " . $progress_class . "'>$discipline_count / 5 Referrals</div>";
            echo "</div>";

            echo "<table>";
            echo "<thead>";
            echo "<tr>";
            echo "<th align='left'>DATE</th>";
            echo "<th align='left'>REASON</th>";
            echo "</tr>";
            echo "</thead>";
            echo "<tbody>";
            while ($row = $result->fetch_assoc()) {
                $date = date('F j, Y', strtotime($row['date']));
                $reason = htmlspecialchars($row['reason']);

                echo "<tr>";
                echo "<td align='left'>" . $date . "</td>";
                echo "<td align='left'>" . $reason . "</td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
        }

        $stmt->close();
    ?>
    </div>
</div>
