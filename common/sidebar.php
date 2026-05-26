<div id="sidebar">
    <div>
        <a href="../home/" class="logo">
            <img src="../images/dyours-logo.png" alt="Delicious Yours Logo">
        </a>
        <ul class="nav-links">
            <li>
                <a href="../home/">
                    <div class="nav-link">
                        <i class="fa-solid fa-house"></i>
                        <span>Home</span>
                    </div>
                </a>
            </li>
            <?php if (isset($attendance_access) && $attendance_access == 1) { ?>
            <li>
                <a href="../attendance/">
                    <div class="nav-link">
                        <i class="fa-solid fa-calendar-check"></i>
                        <span>Attendance</span>
                    </div>
                </a>
            </li>
            <?php } ?>
            <?php if (isset($timesheet_access) && $timesheet_access == 1) { ?>
            <li>
                <a href="../timesheets/">
                    <div class="nav-link">
                        <i class="fa-solid fa-dollar-sign"></i>
                        <span>Timesheets</span>
                    </div>
                </a>
            </li>
            <?php } ?>
            <?php if (isset($settings_access) && $settings_access == 1) { ?>
            <li>
                <a href="../settings/">
                    <div class="nav-link">
                        <i class="fa-solid fa-user-gear"></i>
                        <span>Settings</span>
                    </div>
                </a>
            </li>
            <?php } ?>
        </ul>
    </div>
    <ul class="nav-support">
        <li>
            <a href="../logout.php">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</div>
<script>
    $(document).ready(function() {
        let currentUrl = window.location.pathname;

        $('#sidebar ul li a').each(function() {
            let linkPath = new URL(this.href).pathname;

            if (currentUrl === linkPath) {
                $(this).addClass('active');
            }
        });
    });
</script>
