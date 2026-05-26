<?php
    include("../common/dbconnection.php");
    include("../common/session.php");
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
        <h2>Add Attendance</h2>
        <?php
            $stmt = $conn->prepare("SELECT e.employee_id, e.first_name, e.last_name,
                p.position_name
                FROM employees e
                INNER JOIN positions p ON e.position_id = p.position_id
                WHERE e.hire_status = 1
                ORDER BY e.last_name asc, e.first_name asc");
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                echo "<p style='text-align: center;'>No employees found.</p>";
            } else {
                echo "<form method='post' action='submit_attendance.php'>";
                echo "
                <div class='input-group' style='width: 30%; padding-bottom: 1rem; margin: 1rem auto;'>
                    <input type='date' id='attDate' name='att_date' required>
                    <label for='attDate'>Meeting Date</label>
                </div>
                ";

                echo "<table>";
                echo "<colgroup>";
                echo "<col style='width: 40%;'>";
                echo "<col style='width: 30%;'>";
                echo "<col style='width: 30%;'>";
                echo "</colgroup>";
                echo "<thead>";
                echo "<tr>";
                echo "<th align='left'>Employees</th>";
                echo "<th align='left'>Position</th>";
                echo "<th>Attendance</th>";
                echo "</tr>";
                echo "</thead>";
                echo "<tbody>";
                while ($row = $result->fetch_assoc()) {
                    $employee_id = (int) $row['employee_id'];
                    $employee_name = $row['last_name'] . ", " . $row['first_name'];
                    $position = $row['position_name'];

                    echo "<tr>";
                    echo "<td align='left'><a href='../employee/attendance.php?eid=" . $employee_id . "' target='_blank'>" . $employee_name . "</a></td>";
                    echo "<td align='left'>" . $position . "</td>";
                    echo "
                    <td align='center'>
                        <button type='button' class='att-btn present' data-state='present'>
                        <i class='fa-solid fa-circle-check'></i>
                        </button>
                        <input type='hidden' name='attendance[" . $employee_id . "]' value='present'>
                    </td>";
                    echo "</tr>";
                }
                echo "</tbody>";
                echo "</table>";

                echo "<div style='display: flex; justify-content: center; gap: 1rem; margin: 2rem 0;'>";
                    echo "<button type='submit' class='btn'>Submit</button>";
                    echo "<button type='button' class='btn' id='cancel-btn'>Cancel</button>";
                echo "</div>";
                echo "</form>";
            }
        ?>
    </div>
    <script>
        $(document).ready(function() {
            $('.att-btn').click(function() {
                let state = $(this).attr('data-state');
                let icon = $(this).find('i');
                let hiddenInput = $(this).siblings('input[type="hidden"]');

                if (state === 'present') {
                    $(this)
                        .attr('data-state', 'tardy')
                        .removeClass('present absent')
                        .addClass('tardy');

                    icon.attr('class', 'fa-solid fa-clock');
                    hiddenInput.val('tardy');
                } else if (state === 'tardy') {
                    $(this)
                        .attr('data-state', 'absent')
                        .removeClass('present tardy')
                        .addClass('absent');
                    icon.attr('class', 'fa-solid fa-circle-xmark');
                    hiddenInput.val('absent');
                } else {
                    $(this)
                        .attr('data-state', 'present')
                        .removeClass('tardy absent')
                        .addClass('present');
                    icon.attr('class', 'fa-solid fa-circle-check');
                    hiddenInput.val('present');
                }
            });
        });

        $('#cancel-btn').click(function() {
            window.location.href = 'index.php';
        });
    </script>
</body>
</html>