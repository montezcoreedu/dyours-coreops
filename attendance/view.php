<?php
    include("../common/dbconnection.php");
    include("../common/session.php");

    if (isset($_GET['date'])) {
        $att_date = $_GET['date'];
        $att_date_display = date('F j, Y', strtotime($att_date));

        $stmt = $conn->prepare("SELECT a.date, a.status, e.employee_id, e.first_name, e.last_name,
            p.position_name
            FROM attendance a
            INNER JOIN employees e ON a.employee_id = e.employee_id
            INNER JOIN positions p ON e.position_id = p.position_id
            WHERE date = ?
            ORDER BY e.last_name asc, e.first_name asc");
        $stmt->bind_param("s", $att_date);
        $stmt->execute();
        $result = $stmt->get_result();

        $stmt->close();
    } else {
        header("Location: ../attendance/index.php");
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
        <a href="../attendance/" style="display: block;"><i class="fa-solid fa-angle-left"></i> Back to Attendance</a>
        <h2>Meeting Attendance for <?php echo $att_date_display; ?></h2>
        <?php
            if ($result->num_rows) {
                echo "<form method='post' action='update_attendance.php'>";
                echo "<input type='hidden' name='att_date' value='" . htmlspecialchars($att_date) . "'>";
                echo "<table>";
                echo "<thead>";
                echo "<tr>";
                echo "<th align='left'>Employee</th>";
                echo "<th align='left'>Position</th>";
                echo "<th>Attendance</th>";
                echo "</tr>";
                echo "</thead>";
                echo "<tbody>";
                while ($row = $result->fetch_assoc()) {
                    $employee_id = (int) $row['employee_id'];
                    $employee_name = $row['last_name'] . ", " . $row['first_name'];
                    $position = $row['position_name'];
                    $status = htmlspecialchars($row['status']);

                    if ($status == "present") {
                        $icon = "fa-circle-check";
                    } elseif ($status == "tardy") {
                        $icon = "fa-clock";
                    } else {
                        $icon = "fa-circle-xmark";
                    }

                    echo "<tr>";
                    echo "<td align='left'>" . $employee_name . "</td>";
                    echo "<td align='left'>" . $position . "</td>";
                    echo "
                    <td align='center'>
                        <button type='button' class='att-btn " . $status . "' data-state='" . $status . "'>
                        <i class='fa-solid " . $icon . "'></i>
                        </button>
                        <input type='hidden' name='attendance[" . $employee_id . "]' value='" . $status . "'>
                    </td>";
                    echo "</tr>";
                }
                echo "</tbody>";
                echo "</table>";
                
                echo "<div style='display: flex; justify-content: center; gap: 1rem; margin: 2rem 0;'>";
                    echo "<button type='submit' class='btn'>Save changes</button>";
                    echo "<button type='button' class='btn' id='cancel-btn'>Cancel</button>";
                echo "</div>";
                echo "</form>";
            } else {
                echo "<p style='text-align: center;'>No attendance records found for this date.</p>";
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