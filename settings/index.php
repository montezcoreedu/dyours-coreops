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
            <h3>Positions</h3>
            <a href="#" id="openAddPosition" class="btn" style="width: auto;"><i class="fa-solid fa-user-plus"></i> Add Position</a>
        </div>
        <?php
            $stmt = $conn->prepare("SELECT position_id, position_name, pay_rate,
                sales_adjustment, lookup_access, sales_access, attendance_access,
                timesheet_access, settings_access
                FROM positions
                ORDER BY position_name asc");
            $stmt->execute();
            $result = $stmt->get_result();

            echo "<table>";
            echo "<thead>";
            echo "<tr>";
            echo "<th align='left'>Position Name</th>";
            echo "<th align='left'>Pay Rate</th>";
            echo "</tr>";
            echo "</thead>";
            echo "<tbody>";
            while ($row = $result->fetch_assoc()) {
                $position_id = (int) $row['position_id'];
                $position_name = htmlspecialchars($row['position_name']);
                $pay_rate = (float) $row['pay_rate'];
                $sales_adjustment = (int) $row['sales_adjustment'];
                $lookup_access = (int) $row['lookup_access'];
                $sales_access = (int) $row['sales_access'];
                $attendance_access = (int) $row['attendance_access'];
                $timesheet_access = (int) $row['timesheet_access'];
                $settings_access = (int) $row['settings_access'];

                echo "<tr>";
                echo "<td><a href='#'
                        class='editPosition'
                        data-id='{$position_id}'
                        data-name='{$position_name}'
                        data-payrate='{$pay_rate}'
                        data-sales='{$sales_adjustment}'
                        data-lookup='{$lookup_access}'
                        data-salesaccess='{$sales_access}'
                        data-attendance='{$attendance_access}'
                        data-timesheet='{$timesheet_access}'
                        data-settings='{$settings_access}'
                    >" . $position_name . "</a></td>";
                echo "<td>$" . number_format($pay_rate, 2) . "</td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
        ?>
    </div>
    <div class="modal-backdrop" id="modalBackdrop"></div>
    <div id="addPositionModal" class="modal">
        <div class="header">
            <span id="modalTitle">Add Position</span>
            <span class="dialog-close"><i class="fa-solid fa-xmark"></i></span>
        </div>
        <form method="post" id="addPositionForm">
            <input type="hidden" name="position_id" id="positionId">
            <div class="content">
                <div class="input-group">
                    <input type="text" id="positionName" name="position_name" required>
                    <label for="positionName">Position Name</label>
                </div>
                <div class="input-group">
                    <input type="number" id="payRate" name="pay_rate" min="1" step="0.01" required>
                    <label for="payRate">Pay Rate</label>
                </div>
                <div class="input-group">
                    <select name="sales_adjustment" id="salesAdjustment">
                        <option value="1">No</option>
                        <option value="0">Yes</option>
                    </select>
                    <label for="salesAdjustment">Sales Adjustment</label>
                </div>
                <div class="input-group">
                    <select name="lookup_access" id="lookupAccess">
                        <option value="1">Access</option>
                        <option value="0">Restrict Access</option>
                    </select>
                    <label for="lookupAccess">Lookup</label>
                </div>
                <div class="input-group">
                    <select name="sales_access" id="salesAccess">
                        <option value="1">Access</option>
                        <option value="0">Restrict Access</option>
                    </select>
                    <label for="salesAccess">Sales</label>
                </div>
                <div class="input-group">
                    <select name="attendance_access" id="attendanceAccess">
                        <option value="1">Access</option>
                        <option value="0">Restrict Access</option>
                    </select>
                    <label for="attendanceAccess">Attendance</label>
                </div>
                <div class="input-group">
                    <select name="timesheet_access" id="timesheetAccess">
                        <option value="1">Access</option>
                        <option value="0">Restrict Access</option>
                    </select>
                    <label for="timesheetAccess">Timesheet</label>
                </div>
                <div class="input-group">
                    <select name="settings_access" id="settingsAccess">
                        <option value="1">Access</option>
                        <option value="0">Restrict Access</option>
                    </select>
                    <label for="settingsAccess">Settings</label>
                </div>
            </div>
            <div class="actions">
                <button type="submit" class="btn" id="submitPositionBtn">Add Position</button>
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
            const savedType = sessionStorage.getItem('messageType');
            const savedText = sessionStorage.getItem('messageText');

            if (savedType && savedText) {
                showMessage(savedType, savedText);
                sessionStorage.removeItem('messageType');
                sessionStorage.removeItem('messageText');
            }

            function openModal() {
                $('#addPositionModal').addClass('active');
                $('#modalBackdrop').addClass('active');
            }

            function closeModal() {
                $('#addPositionModal').removeClass('active');
                $('#modalBackdrop').removeClass('active');
            }

            let editMode = false;

            $('#openAddPosition').click(function(e) {
                e.preventDefault();

                editMode = false;

                $('#addPositionForm')[0].reset();

                $('#positionId').val('');

                $('#modalTitle').text('Add Position');

                $('#submitPositionBtn').text('Add Position');

                openModal();
            });

            $(document).on('click', '.editPosition', function(e) {
                e.preventDefault();

                editMode = true;

                $('#positionId').val($(this).data('id'));

                $('#positionName').val($(this).data('name'));

                $('#payRate').val($(this).data('payrate'));

                $('#salesAdjustment').val($(this).data('sales'));

                $('#lookupAccess').val($(this).data('lookup'));

                $('#salesAccess').val($(this).data('salesaccess'));

                $('#attendanceAccess').val($(this).data('attendance'));

                $('#timesheetAccess').val($(this).data('timesheet'));

                $('#settingsAccess').val($(this).data('settings'));

                $('#modalTitle').text('Edit Position');

                $('#submitPositionBtn').text('Save Changes');

                openModal();
            });

            $('#cancelAdd, .dialog-close').click(function() {
                closeModal();
            });

            $('#modalBackdrop').click(function() {
                closeModal();
            });

            $('#addPositionForm').submit(function(e) {
                e.preventDefault();

                let formData = $(this).serialize();

                let url = editMode
                    ? 'edit_position.php'
                    : 'add_position.php';
                
                $.post(url, formData, function(response) {
                    if (response.success) {
                        $('#addPositionForm')[0].reset();

                        closeModal();

                        sessionStorage.setItem('messageType', 'success');

                        sessionStorage.setItem('messageText', response.message);

                        setTimeout(() => {
                            location.reload();
                        }, 300);
                    } else {
                        showMessage('error', response.message || 'Failed.');
                    }
                }, 'json');
            });
        });
    </script>
</body>
</html>