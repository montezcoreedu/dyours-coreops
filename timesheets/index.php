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
    <div id="message"></div>
    <div id="content-wrapper">
        <div class="hero">
            <h3>Timesheets</h3>
            <a href="#" id='openAddPeriod' class="btn" style="width: auto;"><i class="fa-solid fa-calendar-plus"></i> Add Pay Period</a>
        </div>
        <?php
            $stmt = $conn->prepare("SELECT pay_period_id, start_date, end_date, due_date
                FROM pay_periods
                ORDER BY start_date desc");
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows) {
                echo "<table style='margin-bottom: 2rem;'>";
                echo "<thead>";
                echo "<tr>";
                echo "<th align='left'>Start Date</th>";
                echo "<th align='left'>End Date</th>";
                echo "<th align='left'>Due Date</th>";
                echo "<th align='left'>Status</th>";
                echo "<th align='left'>Submitted</th>";
                echo "</tr>";
                echo "</thead>";
                echo "<tbody>";
                while ($row = $result->fetch_assoc()) {
                    $pay_period_id = (int) $row['pay_period_id'];
                    $start_date = date('n/j/Y', strtotime($row['start_date']));
                    $end_date = date('Y-m-d', strtotime($row['end_date']));
                    $end_date_display = date('n/j/Y', strtotime($row['end_date']));
                    $due_date = date('n/j/Y', strtotime($row['due_date']));

                    $count = $conn->prepare("SELECT COUNT(*) AS total_timesheets,
                        SUM(CASE WHEN NOT status = 'draft' THEN 1 ELSE 0 END) AS submitted_timesheets
                        FROM timesheets
                        WHERE pay_period_id = ?");
                    $count->bind_param("i", $pay_period_id);
                    $count->execute();
                    $count_result = $count->get_result();
                    $data = $count_result->fetch_assoc();
                    $total_timesheets = (int) $data['total_timesheets'] ?? 0;
                    $submitted_timesheets = (int) $data['submitted_timesheets'] ?? 0;

                    echo "<tr>";
                    echo "<td><a href='view_sheet.php?tid=" . $pay_period_id . "'>" . $start_date . "</a></td>";
                    echo "<td>" . $end_date_display . "</td>";
                    echo "<td>" . $due_date . "</td>";
                    if ($end_date >= date('Y-m-d')) {
                        echo "<td><div class='status open'><i class='fa-solid fa-lock-open'></i> Open</div></td>";
                    } else {
                        echo "<td><div class='status closed'><i class='fa-solid fa-lock'></i> Closed</div></td>";
                    }
                    if ($submitted_timesheets == $total_timesheets) {
                        echo "<td><div class='status submitted'><i class='fa-solid fa-check'></i> All Submitted</div></td>";
                    } else {
                        echo "<td><div class='status pending'>" . $submitted_timesheets . " / " . $total_timesheets . "</div></td>";
                    }
                    echo "</tr>";
                }
                echo "</tbody>";
                echo "</table>";
            } else {
                echo "<p style='text-align: center;'>No pay periods found.</p>";
            }
        ?>
    </div>
    <div class="modal-backdrop" id="modalBackdrop"></div>
    <div id="addPeriodModal" class="modal">
        <div class="header">
            <span>Add Pay Period</span>
            <span class="dialog-close"><i class="fa-solid fa-xmark"></i></span>
        </div>
        <form method="post" id="addPeriodForm">
            <div class="content">
                <div class="input-group">
                    <input type="date" id="startDate" name="start_date" required>
                    <label for="startDate">Start Date</label>
                </div>
                <div class="input-group">
                    <input type="date" id="endDate" name="end_date" required>
                    <label for="endDate">End Date</label>
                </div>
                <div class="input-group">
                    <input type="date" id="dueDate" name="due_date" required>
                    <label for="dueDate">Due Date</label>
                </div>
            </div>
            <div class="actions">
                <button type="submit" class="btn">Add Pay Period</button>
                <button type="button" class="btn" id="cancelAdd">Cancel</button>
            </div>
        </form>
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
            }, 4800);
        }

        $(document).ready(function() {
            function openModal() {
                $('#addPeriodModal').addClass('active');
                $('#modalBackdrop').addClass('active');
            }

            function closeModal() {
                $('#addPeriodModal').removeClass('active');
                $('#modalBackdrop').removeClass('active');
            }

            $('#openAddPeriod').click(function(e) {
                e.preventDefault();

                $('#addPeriodForm')[0].reset();

                openModal();
            });

            $('#cancelAdd, .dialog-close').click(function() {
                closeModal();
            });

            $('#modalBackdrop').click(function() {
                closeModal();
            });

            $('#addPeriodForm').submit(function(e) {
                e.preventDefault();

                let formData = $(this).serialize();

                $.post('add_period.php', formData, function(response) {
                    if (response.success) {
                        $('#addPeriodForm')[0].reset();

                        closeModal();

                        showMessage('success', response.message);
                    } else {
                        showMessage('error', response.message || 'Failed.');
                    }
                }, 'json');
            });
        });
    </script>
</body>
</html>