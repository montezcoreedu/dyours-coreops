<?php
    include("../common/dbconnection.php");
    include("../common/session.php");

    if (!empty($_GET['eid'])) {
        include("../common/employee_lookup.php");
    } else {
        header("Location: ../home/index.php");
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
        <a href="../home/" style="display: block;"><i class="fa-solid fa-angle-left"></i> Back to Search</a>
        <?php include("../common/emp_header.php"); ?>
        <div style="text-align: center; margin-bottom: 1rem;">
            <a href="#" id="openAddDiscipline" class="btn" style="width: auto;"><i class="fa-solid fa-file-circle-plus"></i> Add Discipline</a>
        </div>
        <?php
            $stmt = $conn->prepare("SELECT discipline_id, date, type, reason, 
                action_taken, acknowledged
                FROM discipline
                WHERE employee_id = ?
                ORDER BY date desc");
            $stmt->bind_param("i", $employee_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                echo "
                <div class='empty-state'>
                    <img src='../images/empty-discipline.png' alt='No discipline entries'>
                    <p>No discipline entries yet</p>
                </div>
                ";
            } else {
                echo "<table style='margin-bottom: 2rem;'>";
                echo "<colgroup>";
                echo "<col style='width: 10%;'>";
                echo "<col style='width: 15%;'>";
                echo "<col style='width: 30%;'>";
                echo "<col style='width: 30%;'>";
                echo "<col style='width: 15%;'>";
                echo "</colgroup>";
                echo "<thead>";
                echo "<tr>";
                echo "<th class='sortable' align='left'>Date <i class='fa fa-sort'></i></th>";
                echo "<th class='sortable' align='left'>Type <i class='fa fa-sort'></i></th>";
                echo "<th class='sortable' align='left'>Reason <i class='fa fa-sort'></i></th>";
                echo "<th class='sortable' align='left'>Action Taken <i class='fa fa-sort'></i></th>";
                echo "<th class='sortable' align='left'>Acknowledged <i class='fa fa-sort'></i></th>";
                echo "</tr>";
                echo "</thead>";
                echo "<tbody>";
                while ($row = mysqli_fetch_assoc($result)) {
                    $discipline_id = (int) $row['discipline_id'];
                    $date = date('Y-m-d', strtotime($row['date']));
                    $date_display = date('n/j/Y', strtotime($row['date']));
                    $type = htmlspecialchars($row['type']);
                    $reason = htmlspecialchars($row['reason']);
                    $action_taken = htmlspecialchars($row['action_taken']);
                    $acknowledged = $row['acknowledged'] ? 'Yes' : 'No';
                    echo "<tr>";
                    echo "<td valign='top'><a href='#'
                        class='editDiscipline'
                        data-id='{$discipline_id}'
                        data-date='{$date}'
                        data-type='{$type}'
                        data-reason='{$reason}'
                        data-action='{$action_taken}'
                        data-acknowledged='{$acknowledged}'
                        >" . $date_display . "</td>";
                    echo "<td valign='top'>" . $type . "</td>";
                    echo "<td valign='top'>" . $reason . "</td>";
                    echo "<td valign='top'>" . $action_taken . "</td>";
                    echo "<td valign='top'>" . $acknowledged . "</td>";
                    echo "</tr>";
                }
                echo "</tbody>";
                echo "</table>";
            }
        ?>
    </div>
    <div class="modal-backdrop" id="modalBackdrop"></div>
    <div id="addDisciplineModal" class="modal">
        <div class="header">
            <span id="modalTitle">Add Discipline</span>
            <span class="dialog-close"><i class="fa-solid fa-xmark"></i></span>
        </div>
        <form method="post" id="addDisciplineForm">
            <input type="hidden" name="discipline_id" id="disciplineId">
            <input type="hidden" name="employee_id" value="<?php echo $employee_id; ?>">
            <div class="content">
                <div class="input-group">
                    <input type="date" id="date" name="date" required>
                    <label for="date">Date</label>
                </div>
                <div class="input-group">
                    <select name="type" id="type" required>
                        <option value=""></option>
                        <option value="Verbal Warning">Verbal Warning</option>
                        <option value="Written Warning">Written Warning</option>
                        <option value="Final Warning">Final Warning</option>
                        <option value="Suspension">Suspension</option>
                        <option value="Termination">Termination</option>
                    </select>
                    <label for="type">Type</label>
                </div>
                <div class="input-group">
                    <textarea name="reason" id="reason" rows="2" maxlength="250" required></textarea>
                    <label for="reason">Reason</label>
                    <br>
                    <small id="reasonMax">0 / 250 max characters</small>
                </div>
                <div class="input-group">
                    <textarea name="action_taken" id="action_taken" rows="2" maxlength="250" required></textarea>
                    <label for="action_taken">Action Taken</label>
                    <br>
                    <small id="actionMax">0 / 250 max characters</small>
                </div>
                <div class="input-group">
                    <select name="acknowledged" id="acknowledged" required>
                        <option value=""></option>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                    <label for="acknowledged">Acknowledged</label>
                </div>
            </div>
            <div class="actions">
                <button type="submit" class="btn" id="submitDisciplineBtn">Submit</button>
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

        $(document).ready(function () {
            let sortState = {};

            $("#disciplineTable tbody tr").each(function (index) {
                $(this).attr("data-original-index", index);
            });

            $(".sortable").click(function () {
                const table = $(this).closest("table");
                const tbody = table.find("tbody");
                const index = $(this).index();
                let rows = tbody.find("tr").toArray();

                sortState[index] = (sortState[index] || 0) + 1;
                if (sortState[index] > 2) sortState[index] = 0;

                if (sortState[index] === 0) {
                    rows.sort((a, b) => {
                        return $(a).data("original-index") - $(b).data("original-index");
                    });

                } else {
                    rows.sort(function (a, b) {
                        let valA = $(a).children("td").eq(index).text().trim();
                        let valB = $(b).children("td").eq(index).text().trim();

                        valA = valA.replace(/[$,]/g, "");
                        valB = valB.replace(/[$,]/g, "");

                        if (!isNaN(valA) && !isNaN(valB)) {
                            return sortState[index] === 1 ? valA - valB : valB - valA;
                        }

                        let dateA = new Date(valA);
                        let dateB = new Date(valB);
                        if (!isNaN(dateA) && !isNaN(dateB)) {
                            return sortState[index] === 1 ? dateA - dateB : dateB - dateA;
                        }

                        return sortState[index] === 1
                            ? valA.localeCompare(valB)
                            : valB.localeCompare(valA);
                    });
                }

                tbody.empty().append(rows);

                $(".sortable i")
                    .removeClass("fa-sort-up fa-sort-down")
                    .addClass("fa-sort");

                if (sortState[index] === 1) {
                    $(this).find("i")
                        .removeClass("fa-sort")
                        .addClass("fa-sort-up");
                } else if (sortState[index] === 2) {
                    $(this).find("i")
                        .removeClass("fa-sort")
                        .addClass("fa-sort-down");
                }
            });
        });

        $(document).ready(function() {
            function updateCharacterCount(textareaId, counterId, maxLength) {
                let currentLength = $(textareaId).val().length;

                $(counterId).text(currentLength + ' / ' + maxLength + ' max characters');

                if (currentLength >= maxLength) {
                    $(counterId).css('color', 'rgb(116, 6, 6)');
                } else {
                    $(counterId).css('color', '');
                }
            }

            $('#reason').on('input', function() {
                updateCharacterCount('#reason', '#reasonMax', 250);
            });

            $('#action_taken').on('input', function() {
                updateCharacterCount('#action_taken', '#actionMax', 250);
            });

            updateCharacterCount('#reason', '#reasonMax', 250);
            updateCharacterCount('#action_taken', '#actionMax', 250);
        });

        $(document).ready(function() {
            const savedType = sessionStorage.getItem('messageType');
            const savedText = sessionStorage.getItem('messageText');

            if (savedType && savedText) {
                showMessage(savedType, savedText);
                sessionStorage.removeItem('messageType');
                sessionStorage.removeItem('messageText');
            }

            function openModal() {
                $('#addDisciplineModal').addClass('active');
                $('#modalBackdrop').addClass('active');
            }

            function closeModal() {
                $('#addDisciplineModal').removeClass('active');
                $('#modalBackdrop').removeClass('active');
            }

            let editMode = false;

            $('#openAddDiscipline').click(function(e) {
                e.preventDefault();

                editMode = false;

                $('#addDisciplineForm')[0].reset();

                $('#disciplineId').val('');

                $('#modalTitle').text('Add Discipline');

                $('#submitDisciplineBtn').text('Add Discipline');

                openModal();
            });

            $(document).on('click', '.editDiscipline', function(e) {
                e.preventDefault();

                editMode = true;

                $('#disciplineId').val($(this).data('id'));

                $('#date').val($(this).data('date'));

                $('#type').val($(this).data('type'));

                $('#reason').val($(this).data('reason'));

                $('#action_taken').val($(this).data('action'));

                $('#acknowledged').val($(this).data('acknowledged'));

                $('#modalTitle').text('Edit Discipline');

                $('#submitDisciplineBtn').text('Save Changes');

                openModal();
            });

            $('#cancelAdd, .dialog-close').click(function() {
                closeModal();
            });

            $('#modalBackdrop').click(function() {
                closeModal();
            });

            $('#addDisciplineForm').submit(function(e) {
                e.preventDefault();

                let formData = $(this).serialize();

                let url = editMode
                    ? 'update_discipline.php'
                    : 'add_discipline.php';
                
                $.post(url, formData, function(response) {
                    if (response.success) {
                        $('#addDisciplineForm')[0].reset();

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