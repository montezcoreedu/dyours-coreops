<?php
    include("../common/dbconnection.php");
    include("../common/session.php");

    if (!empty($_GET['eid'])) {
        include("../common/employee_lookup.php");

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $first_name = trim($_POST['first_name']);
            $last_name = trim($_POST['last_name']);
            $email = trim($_POST['email']);
            $phone = trim($_POST['phone']);
            $position = (int) $_POST['position'];
            $hire_status = (int) $_POST['hire_status'];

            $stmt = $conn->prepare("UPDATE employees SET first_name = ?, last_name = ?, email = ?, phone = ?, position_id = ?, hire_status = ?, updated_on = NOW() WHERE employee_id = ?");
            $stmt->bind_param("ssssiii", $first_name, $last_name, $email, $phone, $position, $hire_status, $employee_id);
            $success = $stmt->execute();
            
            $stmt->close();

            $_SESSION['message'] = [
                'type' => $success ? 'success' : 'error',
                'text' => $success
                    ? 'Employee profile saved successfully'
                    : 'Failed to save employee changes'
            ];

            header("Location: profile.php?eid=$employee_id");
            exit();
        }
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
        <form method="post">
            <div class="input-group" style="width: 30%;">
                <input type="text" id="firstName" name="first_name" value="<?php echo $employeeFirst ?? ''; ?>" required>
                <label for="firstName">First Name</label>
            </div>
            <div class="input-group" style="width: 30%;">
                <input type="text" id="lastName" name="last_name" value="<?php echo $employeeLast ?? ''; ?>" required>
                <label for="lastName">Last Name</label>
            </div>
            <div class="input-group" style="width: 40%;">
                <input type="email" id="email" name="email" value="<?php echo $employeeEmail ?? ''; ?>" required>
                <label for="email">Email</label>
            </div>
            <div class="input-group" style="width: 30%;">
                <input type="text" id="phone" name="phone" value="<?php echo $employeePhone ?? ''; ?>" required>
                <label for="phone">Phone</label>
            </div>
            <div class="input-group" style="width: 30%;">
                <select name="position" id="position" required>
                <?php
                    $position_stmt = $conn->prepare("SELECT position_id, position_name
                        FROM positions
                        ORDER BY position_name asc");
                    $position_stmt->execute();
                    $positions = $position_stmt->get_result();

                    while ($position = mysqli_fetch_assoc($positions)) {
                        $position_id = (int) $position['position_id'];
                        $position_name = htmlspecialchars($position['position_name']);
                        $selected = ($position_id === (int) ($employeePosition ?? 0)) ? "selected" : "";
                        echo "<option value='" . $position_id . "' " . $selected . ">" . $position_name . "</option>";
                    }
                ?>
                </select>
                <label for="position">Position</label>
            </div>
            <div class="input-group" style="width: 30%;">
                <select name="hire_status" id="hireStatus">
                    <option value="1" <?php echo ((int) ($employeeStatus ?? 0) === 1) ? 'selected' : ''; ?>>Active</option>
                    <option value="2" <?php echo ((int) ($employeeStatus ?? 0) === 2) ? 'selected' : ''; ?>>Inactive</option>
                </select>
                <label for="hireStatus">Hire Status</label>
            </div>
            <button type="submit" class="btn" style="width: auto;">Save changes</button>
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
            if ($('#message').length) {
                setTimeout(() => {
                    $('#message').removeClass('show');
                }, 4800);
            }
        });
    </script>
</body>
</html>