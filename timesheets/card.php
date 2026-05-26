<?php
    $sales_adjustment = $sales_adjustment ?? 0;
    $timesheet = null;
    $today = new DateTime(date('Y-m-d'));
?>
<div class="card">
    <div class="title">
        <h3>Timesheet</h3>
    </div>
    <div class="content">
    <?php
        $stmt = $conn->prepare("SELECT pay_period_id, start_date, end_date, due_date,
            CONCAT(MONTHNAME(start_date), ' ', YEAR(start_date)) AS period_name
            FROM pay_periods
            WHERE CURDATE() BETWEEN start_date AND end_date
            LIMIT 1");
        $stmt->execute();
        $result = $stmt->get_result();
        $period = $result->fetch_assoc();

        if (!$period) {
            echo "
            <div class='empty-state'>
                <img src='../images/empty-timesheet.png' alt='No active timesheet'>
                <p>No timesheet available to log</p>
            </div>
            ";
        } else {
            $period_id = (int) $period['pay_period_id'];
            $period_start = date('M j', strtotime($period['start_date']));
            $period_end = date('M j', strtotime($period['end_date']));
            $due_date = new DateTime($period['due_date']);
            $days_remaining = (int) $today->diff($due_date)->format('%r%a');

            $stmt = $conn->prepare("SELECT t.timesheet_id, t.status, t.total_hours,
                p.pay_rate
                FROM timesheets t
                INNER JOIN employees e ON t.employee_id = e.employee_id
                INNER JOIN positions p ON e.position_id = p.position_id
                WHERE t.employee_id = ?
                AND pay_period_id = (SELECT pay_period_id FROM pay_periods
                WHERE start_date <= CURDATE() AND end_date >= CURDATE())");
            $stmt->bind_param("i", $session_id);
            $stmt->execute();
            $timesheet_result = $stmt->get_result();
            $timesheet = $timesheet_result->fetch_assoc();

            if (!$timesheet) {
                echo "
                <div class='empty-state'>
                    <img src='../images/empty-timesheet.png' alt='No active timesheet'>
                    <p style='margin-bottom: 1rem;'><b>Ready to start logging hours?</b> Start newest timesheet from " . $period_start . " to " . $period_end . "</p>
                    <form action='../timesheets/add_sheet.php' method='post'>
                        <input type='hidden' name='session_id' value='" . $session_id . "'>
                        <input type='hidden' name='pay_period_id' value='" . $period_id . "'>
                        <button type='submit' class='btn'>
                            <i class='fa-solid fa-dollar-sign'></i> Start Timesheet
                        </button>
                    </form>
                </div>
                ";
            } else {
                $timesheet_id = (int) $timesheet['timesheet_id'];
                $sheet_status = htmlspecialchars($timesheet['status']);

                $status_map = [
                    'draft' => "<i class='fa-solid fa-file-pen'></i> &nbsp;Draft",
                    'submitted' => "<i class='fa-solid fa-circle-check'></i> &nbsp;Submitted",
                    'approved' => "<i class='fa-solid fa-check-to-slot'></i> &nbsp;Approved",
                    'rejected' => "<i class='fa-solid fa-circle-xmark'></i> &nbsp;Rejected"
                ];
                $status = $status_map[$sheet_status] ?? $status_map['draft'];

                echo "<div class='caption'>Period of " . $period_start . " to " . $period_end . "</div>";

                if ($sheet_status == "draft") {
                    $due_text = "";
                    $due_class = "";

                    if ($days_remaining == 0) {
                        $due_text = "Due today";
                        $due_class = "due-today";
                    } elseif ($days_remaining == 1) {
                        $due_text = "Due tomorrow";
                        $due_class = "due-soon";
                    } elseif ($days_remaining > 1 && $days_remaining <= 3) {
                        $due_text = "Due in {$days_remaining} days";
                        $due_class = "due-soon";
                    }

                    if ($due_text !== "") {
                        echo "<div class='due-label'><span class='{$due_class}'>{$due_text}</span></div>";
                    }
                }

                echo "
                <div class='timesheet-status'>
                    <div class='status-label'>Status:</div>
                    <div class='status-value " . $sheet_status . "'>" . $status . "</div>
                </div>
                ";
                                
                if ($sales_adjustment == 1) {
                    $stmt = $conn->prepare("SELECT SUM(sales_amount) AS total_sales
                        FROM sales_entries
                        WHERE timesheet_id = $timesheet_id");
                    $stmt->execute();
                    $sales_result = $stmt->get_result();
                    $total_sales = $sales_result->fetch_assoc()['total_sales'];

                    if ($total_sales <= 125) {
                        $gross_pay = $total_sales * 0.10;
                    } elseif ($total_sales <= 225) {
                        $gross_pay = $total_sales * 0.20;
                    } else {
                        $gross_pay = $total_sales * 0.25;
                    }

                    echo "
                    <div class='sheet-grid'>
                        <div class='sheet-item'>
                            <div class='item-value' id='salesDisplay'>$" . ($total_sales ? number_format($total_sales, 2) : "0.00") . "</div>
                            <div class='item-label'>Sales</div>
                        </div>
                        <div style='width: 1px; height: 32px; background-color: rgb(208, 208, 208); margin: 0 0.92rem;'></div>
                        <div class='sheet-item'>
                            <div class='item-value' id='grossPayDisplay'>$" . ($gross_pay ? number_format($gross_pay, 2) : "0.00") . "</div>
                            <div class='item-label'>Gross Pay</div>
                        </div>
                    </div>
                    ";

                    if ($sheet_status == "draft") {
                        echo "<a href='#' id='openAddSales' class='btn' style='margin-bottom: 0.62rem;'><i class='fa-solid fa-dollar-sign'></i> Add Sales</a>";
                        echo "<a href='#' onclick='submitTimesheet($timesheet_id)' class='btn' style='margin-bottom: 0.62rem;'><i class='fa-solid fa-hourglass-end'></i> Submit Timesheet</a>";
                    }

                    echo "<a href='#' id='openViewSales' class='btn' data-id='$timesheet_id'><i class='fa-solid fa-clock-rotate-left'></i> View Sales</a>";
                } else {
                    $total_hours = (float) $timesheet['total_hours'];
                    $pay_rate = (float) $timesheet['pay_rate'];
                    $gross_pay = $total_hours * $pay_rate;

                    echo "
                    <div class='sheet-grid'>
                        <div class='sheet-item'>
                            <div class='item-value'>$" . ($pay_rate ? number_format($pay_rate, 2) : "0.00") . "</div>
                            <div class='item-label'>Hourly Rate</div>
                        </div>
                        <div style='width: 1px; height: 32px; background-color: rgb(208, 208, 208); margin: 0 0.92rem;'></div>
                        <div class='sheet-item'>
                            <div class='item-value' id='totalHoursDisplay'>" . ($total_hours ? number_format($total_hours, 2) : "0.00") . "</div>
                            <div class='item-label'>Hours Worked</div>
                        </div>
                        <div style='width: 1px; height: 32px; background-color: rgb(208, 208, 208); margin: 0 0.92rem;'></div>
                        <div class='sheet-item'>
                            <div class='item-value' id='grossPayDisplay'>$" . ($gross_pay ? number_format($gross_pay, 2) : "0.00") . "</div>
                            <div class='item-label'>Gross Pay</div>
                        </div>
                    </div>
                    ";

                    if ($sheet_status == "draft") {
                        echo "<a href='#' id='openAddHours' class='btn' style='margin-bottom: 0.62rem;'><i class='fa-solid fa-dollar-sign'></i> Add Hours</a>";
                        echo "<a href='#' onclick='submitTimesheet($timesheet_id)' class='btn' style='margin-bottom: 0.62rem;'><i class='fa-solid fa-hourglass-end'></i> Submit Timesheet</a>";
                    }

                    echo "<a href='#' id='openViewHours' class='btn' data-id='$timesheet_id'><i class='fa-solid fa-clock-rotate-left'></i> View Timesheet</a>";
                }
            }
        }

        $stmt->close();
    ?>
    </div>
</div>
<div id="addHoursModal" class="modal">
    <div class="header">
        <span>Add Timesheet Hours</span>
        <span class="dialog-close"><i class="fa-solid fa-xmark"></i></span>
    </div>
    <form method="post" id="addHoursForm">
            <input type="hidden" name="timesheet_id" id="hours_id">
        <div class="content">
            <div class="input-group">
                <input type="date" id="workDate" name="work_date" required>
                <label for="workDate">Work Date</label>
            </div>
            <div class="input-group">
                <input type="time" id="clockIn" name="clock_in" required>
                <label for="clockIn">Clock In</label>
            </div>
            <div class="input-group">
                <input type="time" id="clockOut" name="clock_out" required>
                <label for="clockOut">Clock Out</label>
            </div>
        </div>
        <div class="actions">
            <button type="submit" class="btn">Add Hours</button>
            <button type="button" class="btn" id="cancelAdd">Cancel</button>
        </div>
    </form>
</div>
<div id="addSalesModal" class="modal">
    <div class="header">
        <span>Add Sales</span>
        <span class="dialog-close"><i class="fa-solid fa-xmark"></i></span>
    </div>
    <form method="post" id="addSalesForm">
        <input type="hidden" name="timesheet_id" id="sales_id">
        <input type="hidden" name="employee_id" value="<?php echo $session_id; ?>">
        <div class="content">
            <div class="input-group">
                <input type="date" id="workDate" name="work_date" required>
                <label for="workDate">Sales Date</label>
            </div>
            <div class="input-group">
                <input type="number" id="salesAmount" name="sales_amount" step="0.01" required>
                <label for="salesAmount">Sales Amount</label>
            </div>
        </div>
        <div class="actions">
            <button type="submit" class="btn">Add Sales</button>
            <button type="button" class="btn" id="cancelAdd">Cancel</button>
        </div>
    </form>
</div>
<div id="viewHoursModal" class="modal">
    <div class="header">
        <span>View Timesheet</span>
        <span class="dialog-close"><i class="fa-solid fa-xmark"></i></span>
    </div>
    <div class="content" id="viewHoursContent">
        <!-- Hour entries will be loaded here -->
    </div>
    <div class="actions">
        <button type="button" class="btn closeView">Close</button>
    </div>
</div>
<div id="viewSalesModal" class="modal">
    <div class="header">
        <span>View Sales</span>
        <span class="dialog-close"><i class="fa-solid fa-xmark"></i></span>
    </div>
    <div class="content" id="viewSalesContent">
        <!-- Sales entries will be loaded here -->
    </div>
    <div class="actions">
        <button type="button" class="btn closeView">Close</button>
    </div>
</div>
<script>
    <?php if ($timesheet) { ?>
    $(document).ready(function() {
        function openModal() {
            $('#addHoursModal').addClass('active');
            $('#modalBackdrop').addClass('active');
        }

        function closeModal() {
            $('#addHoursModal').removeClass('active');
            $('#modalBackdrop').removeClass('active');
        }

        $('#openAddHours').click(function(e) {
            e.preventDefault();

            $('#addHoursForm')[0].reset();

            $('#hours_id').val('<?php echo $timesheet_id; ?>');

            openModal();
        });

        $('#cancelAdd, .dialog-close').click(function() {
            closeModal();
        });

        $('#modalBackdrop').click(function() {
            closeModal();
        });

        $('#addHoursForm').submit(function(e) {
            e.preventDefault();

            let formData = $(this).serialize();

            $.post('../timesheets/add_hours.php', formData, function(response) {
                if (response.success) {
                    $('#addHoursForm')[0].reset();

                    $('#hours_id').val('');

                    closeModal();

                    $('#totalHoursDisplay').text(response.total_hours);
                    $('#grossPayDisplay').text('$' + response.gross_pay);

                    showMessage('success', response.message);
                } else {
                    showMessage('error', response.message || 'Failed.');
                }
            }, 'json');
        });
    });

    $(document).ready(function() {
        function openModal() {
            $('#addSalesModal').addClass('active');
            $('#modalBackdrop').addClass('active');
        }

        function closeModal() {
            $('#addSalesModal').removeClass('active');
            $('#modalBackdrop').removeClass('active');
        }

        $('#openAddSales').click(function(e) {
            e.preventDefault();

            $('#addSalesForm')[0].reset();

            $('#sales_id').val('<?php echo $timesheet_id; ?>');

            openModal();
        });

        $('#cancelAdd, .dialog-close').click(function() {
            closeModal();
        });

        $('#modalBackdrop').click(function() {
            closeModal();
        });

        $('#addSalesForm').submit(function(e) {
            e.preventDefault();

            let formData = $(this).serialize();

            $.post('../timesheets/add_sales.php', formData, function(response) {
                if (response.success) {
                    $('#addSalesForm')[0].reset();

                    $('#sales_id').val('');

                    closeModal();

                    $('#salesDisplay').text(
                        '$' + parseFloat(response.total_sales).toFixed(2)
                    );

                    $('#grossPayDisplay').text(
                        '$' + parseFloat(response.gross_pay).toFixed(2)
                    );

                    showMessage('success', response.message);
                } else {
                    showMessage('error', response.message || 'Failed.');
                }
            }, 'json');
        });
    });

    $('#openViewHours').click(function(e) {
        e.preventDefault();

        $('#viewHoursModal').addClass('active');
        $('#modalBackdrop').addClass('active');

        let timesheet_id = $(this).data('id');

        $.ajax({
            url: '../timesheets/get_hours.php',
            type: 'POST',
            data: {
                timesheet_id: timesheet_id
            },

            success: function(response) {
                $('#viewHoursContent').html(response);
            }
        });
    });

    $('#openViewSales').click(function(e) {
        e.preventDefault();

        $('#viewSalesModal').addClass('active');
        $('#modalBackdrop').addClass('active');

        let timesheet_id = $(this).data('id');

        $.ajax({
            url: '../timesheets/get_sales.php',
            type: 'POST',
            data: {
                timesheet_id: timesheet_id
            },

            success: function(response) {
                $('#viewSalesContent').html(response);
            }
        });
    });

    function closeViewModal() {
        $('#viewHoursModal').removeClass('active');
        $('#viewSalesModal').removeClass('active');
        $('#modalBackdrop').removeClass('active');
    }

    $('.closeView, .dialog-close').click(function() {
        closeViewModal();
    });

    $('#modalBackdrop').click(function() {
        closeViewModal();
    });

    function submitTimesheet(timesheetId) {
        let confirmSubmit = confirm(
            "Are you sure you want to submit this timesheet? Once submitted, you will not be able to make changes until it is reviewed by management."
        );

        if (!confirmSubmit) return;

        $.ajax({
            url: '../timesheets/submit_timesheet.php',
            type: 'GET',
            data: { timesheet_id: timesheetId },
            dataType: 'json',

            success: function(response) {
                if (response.success) {
                    showMessage('success', response.message);

                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showMessage('error', response.message);
                }
            },

            error: function(xhr) {
                console.error(xhr.responseText);
                showMessage('error', 'Server error');
            }
        });
    }
    <?php } ?>
</script>
