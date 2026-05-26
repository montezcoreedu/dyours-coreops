<div class="card">
    <div class="title">
        <h3>Assigned Tasks</h3>
    </div>
    <div class="content">
    <?php
        $stmt = $conn->prepare("SELECT task_id, task_name, due_date
            FROM tasks
            WHERE assigned_to = ? AND completed = 2
            ORDER BY due_date asc");
        $stmt->bind_param("i", $session_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $task_counter = 0;
        $task_amount = $result->num_rows;

        if ($result->num_rows === 0) {
            echo "
            <div class='empty-state'>
                <img src='../images/empty-tasks.png' alt='No tasks available'>
                <p>No tasks assigned yet</p>
            </div>
            ";
        } else {
            echo "<div class='caption'>$task_amount assigned task(s) by executive board</div>";

            echo "<ul id='tasksList' class='tasks'>";
            while ($row = mysqli_fetch_assoc($result)) {
                $task_id = (int) $row['task_id'];
                $task_name = htmlspecialchars($row['task_name']);
                $due_date = date('Y-m-d', strtotime($row['due_date']));
                $today = date('Y-m-d');
                $diff = (strtotime($due_date) - strtotime($today)) / (60 * 60 * 24);
                if ($diff == 0) {
                    $due_indicator = "<span class='due-date' style='color: rgb(116, 94, 6);'>Due Today</span>";
                } elseif ($diff == 1) {
                    $due_indicator = "<span class='due-date'>Due Tomorrow</span>";
                } elseif ($diff > 1) {
                    $due_indicator = "<span class='due-date'>Due in $diff days</span>";
                } else {
                    $due_indicator = "<span class='due-date' style='color: rgb(116, 6, 6);'><b>Overdue</b></span>";
                }

                echo "<li>";
                echo "<input type='checkbox' class='task-checkbox' data-id='$task_id' id='task$task_counter'>";
                echo "<label for='task$task_counter'>";
                echo "<span class='task-name'>$task_name</span>";
                echo "$due_indicator";
                echo "</label>";
                echo "</li>";
                $task_counter++;
            }
            echo "</ul>";
        }
    ?>
    </div>
</div>
