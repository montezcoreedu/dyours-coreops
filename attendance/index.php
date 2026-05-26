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
        <div class="hero">
            <h3>Attendance</h3>
            <a href="add.php" class="btn" style="width: auto;"><i class="fa-solid fa-calendar-plus"></i> Add Meeting</a>
        </div>
        <?php
            $stmt = $conn->prepare("SELECT date, COUNT(*) as total_employees,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as total_present
                FROM attendance
                GROUP BY date
                ORDER BY date desc");
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows) {
                echo "<table style='margin-bottom: 2rem;'>";
                echo "<thead>";
                echo "<tr>";
                echo "<th align='left'>Date</th>";
                echo "<th align='left'>Total Employees</th>";
                echo "<th align='left'>Total Present</th>";
                echo "</tr>";
                echo "</thead>";
                echo "<tbody>";
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td align='left'><a href='view.php?date=" . $row['date'] . "'>" . date('F j, Y', strtotime($row['date'])) . "</a></td>";
                    echo "<td align='left'>" . $row['total_employees'] . "</td>";
                    echo "<td align='left'>" . $row['total_present'] . "</td>";
                    echo "</tr>";
                }
                echo "</tbody>";
                echo "</table>";
            } else {
                echo "<p style='text-align: center;'>No attendance records found.</p>";
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
    </script>
</body>
</html>