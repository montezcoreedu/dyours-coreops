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
    <div id="message"></div>
    <div id="content-wrapper">
        <a href="../home/" style="display: block;"><i class="fa-solid fa-angle-left"></i> Back to Search</a>
        <?php include("../common/emp_header.php"); ?>
        <div style="text-align: center; margin-bottom: 1rem;">
            <a href="#" id="openAddTask" class="btn" style="width: auto;"><i class="fa-solid fa-calendar-plus"></i> Add Task</a>
        </div>
        <?php
            $stmt = $conn->prepare("SELECT task_id, task_name, due_date
                FROM tasks
                WHERE assigned_to = ? AND completed = 2
                ORDER BY due_date asc");
            $stmt->bind_param("i", $employee_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $task_counter = 1;

            if ($result->num_rows === 0) {
                echo "
                <div class='empty-state'>
                    <img src='../images/empty-tasks.png' alt='No tasks assigned'>
                    <p>No tasks assigned yet</p>
                </div>
                ";
            } else {
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
                    echo "<span style='margin-right: 1rem;'>$task_counter.</span>";
                    echo "<label style='cursor: default;'>";
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
    <div class="modal-backdrop" id="modalBackdrop"></div>
    <div id="addTaskModal" class="modal">
        <div class="header">
            <span>Add Task</span>
            <span class="dialog-close"><i class="fa-solid fa-xmark"></i></span>
        </div>
        <form method="post" id="addTaskForm">
            <input type="hidden" name="assigned_to" value="<?php echo $employee_id; ?>">
            <div class="content">
                <div class="input-group">
                    <input type="text" id="taskName" name="task_name" maxlength="100" required>
                    <label for="taskName">Task Name</label>
                </div>
                <div class="input-group">
                    <input type="date" id="dueDate" name="due_date" required>
                    <label for="dueDate">Due Date</label>
                </div>
            </div>
            <div class="actions">
                <button type="submit" class="btn">Submit</button>
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
                $('#addTaskModal').addClass('active');
                $('#modalBackdrop').addClass('active');
            }

            function closeModal() {
                $('#addTaskModal').removeClass('active');
                $('#modalBackdrop').removeClass('active');
            }

            $('#openAddTask').click(function(e) {
                e.preventDefault();

                $('#addTaskForm')[0].reset();

                openModal();
            });

            $('#cancelAdd, .dialog-close').click(function() {
                closeModal();
            });

            $('#modalBackdrop').click(function() {
                closeModal();
            });

            $('#addTaskForm').submit(function(e) {
                e.preventDefault();

                let formData = $(this).serialize();

                $.post('submit_task.php', formData, function(response) {
                    if (response.success) {
                        $('#addTaskForm')[0].reset();

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