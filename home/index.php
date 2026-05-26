<?php
    include("../common/dbconnection.php");
    include("../common/session.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Document</title>
    <?php include("../common/head.php"); ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function updateTimeOfDay() {
            const timeOfDayElement = document.getElementById('timeOfDay');
            const now = new Date();
            const hours = now.getHours();

            let timeOfDay;
            if (hours < 12) {
                timeOfDay = 'Good morning';
            } else if (hours < 18) {
                timeOfDay = 'Good afternoon';
            } else {
                timeOfDay = 'Good evening';
            }

            timeOfDayElement.textContent = timeOfDay;
        }
        window.onload = updateTimeOfDay;
    </script>
</head>
<body>
    <?php include("../common/sidebar.php"); ?>
    <div id="message"></div>
    <div id="content-wrapper">
        <div class="hero">
            <h3><span id="timeOfDay"></span>, <?php echo $_SESSION['first_name']; ?></h3>
            <p>Navigate and manage all your daily tasks in one place</p>
        </div>
        <div class="dashboard-cards">
            <?php include("../employees/card.php"); ?>
            <?php include("../sales/card.php"); ?>
            <?php include("../tasks/card.php"); ?>
            <?php include("../timesheets/card.php"); ?>
            <?php include("../attendance/card.php"); ?>
            <?php include("../discipline/card.php"); ?>
        </div>
    </div>
    <div class="modal-backdrop" id="modalBackdrop"></div>
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
    </script>
</body>
</html>