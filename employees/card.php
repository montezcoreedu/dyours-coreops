<?php if (isset($lookup_access) && $lookup_access == 1) { ?>
<div class="card">
    <div class="title">
        <h3>Employee Lookup</h3>
    </div>
    <div class="content">
        <div class="search-box" style="margin-bottom: 0.65rem;">
            <i class="fa fa-search"></i>
            <input type="text" id="employee-search" placeholder="Search">
        </div>
        <a href="#" id="openAddEmployee" class="btn"><i class="fa-solid fa-user-plus"></i> Add Employee</a>
        <div id="search-results"></div>
    </div>
</div>
<div id="addEmployeeModal" class="modal">
    <div class="header">
        <span>Add Employee</span>
        <span class="dialog-close"><i class="fa-solid fa-xmark"></i></span>
    </div>
    <form method="post" id="addEmployeeForm">
        <div class="content">
            <div class="input-group">
                <input type="text" id="firstName" name="first_name" maxlength="100" required>
                <label for="firstName">First Name</label>
            </div>
            <div class="input-group">
                <input type="text" id="lastName" name="last_name" maxlength="100" required>
                <label for="lastName">Last Name</label>
            </div>
            <div class="input-group">
                <input type="email" id="email" name="email" maxlength="100" required>
                <label for="email">Email</label>
            </div>
            <div class="input-group">
                <input type="text" id="phone" name="phone" maxlength="100" required>
                <label for="phone">Phone</label>
            </div>
            <div class="input-group">
                <select name="position" id="position" required>
                <option value=""></option>
                <?php
                    $position_stmt = $conn->prepare("SELECT position_id, position_name
                        FROM positions
                        ORDER BY position_name asc");
                    $position_stmt->execute();
                    $positions = $position_stmt->get_result();

                    while ($position = mysqli_fetch_assoc($positions)) {
                        $position_id = (int) $position['position_id'];
                        $position_name = htmlspecialchars($position['position_name']);
                        echo "<option value='" . $position_id . "'>" . $position_name . "</option>";
                    }
                ?>
                </select>
                <label for="position">Position</label>
            </div>
        </div>
        <div class="actions">
            <button type="submit" class="btn">Submit</button>
            <button type="button" class="btn" id="cancelAdd">Cancel</button>
        </div>
    </form>
</div>
<script>
    document.getElementById("employee-search").addEventListener("keyup", function() {
        let query = this.value;

        if (query.length === 0) {
            document.getElementById("search-results").innerHTML = "";
            return;
        }

        fetch("../employees/search_employees.php?q=" + query)
        .then(response => response.text())
        .then(data => {
            document.getElementById("search-results").innerHTML = data;
        });
    });

    $(document).ready(function() {
        function openModal() {
            $('#addEmployeeModal').addClass('active');
            $('#modalBackdrop').addClass('active');
        }

        function closeModal() {
            $('#addEmployeeModal').removeClass('active');
            $('#modalBackdrop').removeClass('active');
        }

        $('#openAddEmployee').click(function(e) {
            e.preventDefault();

            $('#addEmployeeForm')[0].reset();

            openModal();
        });

        $('#cancelAdd, .dialog-close').click(function() {
            closeModal();
        });

        $('#modalBackdrop').click(function() {
            closeModal();
        });

        $('#addEmployeeForm').submit(function(e) {
            e.preventDefault();

            let formData = $(this).serialize();

            $.post('../employees/submit_employee.php', formData, function(response) {
                if (response.success) {
                    $('#addEmployeeForm')[0].reset();

                    closeModal();

                    showMessage('success', response.message);

                } else {
                    showMessage('error', response.message || 'Failed.');
                }
            }, 'json');
        });
    });
</script>
<?php } ?>
