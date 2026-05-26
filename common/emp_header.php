<div class="emp-name">
    <?php
        $first_name = $employeeData['first_name'] ?? 'User';
        $last_name = $employeeData['last_name'] ?? 'Avatar';
        echo "<img src='https://ui-avatars.com/api/?name={$first_name}+{$last_name}&background=random&size=42' alt='{$first_name} {$last_name} Avatar' class='avatar' style='width: 38px; height: 38px; border-radius: 100px;'>";
    ?>
    <?php echo $employeeName ?? ''; ?>
</div>
<div class="employee-tabs">
    <a href="../employees/attendance.php?eid=<?php echo $employee_id; ?>">Attendance</a>
    <a href="../employees/discipline.php?eid=<?php echo $employee_id; ?>">Discipline</a>
    <a href="../employees/profile.php?eid=<?php echo $employee_id; ?>">Profile</a>
    <a href="../employees/tasks.php?eid=<?php echo $employee_id; ?>">Tasks</a>
    <a href="../employees/timesheets.php?eid=<?php echo $employee_id; ?>">Timesheets</a>
</div>
<script>
    $(document).ready(function() {
        let currentUrl = window.location.pathname;

        $('.employee-tabs a').each(function() {
            let linkPath = new URL(this.href).pathname;

            if (currentUrl === linkPath) {
                $(this).addClass('active');
            }
        });
    });
</script>
