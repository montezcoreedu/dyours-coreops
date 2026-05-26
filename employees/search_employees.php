<?php
    include("../common/dbconnection.php");
    include("../common/session.php");

    if (!isset($_GET['q'])) {
        echo "";
        exit;
    }

    $query = $_GET['q'];
    $stmt = $conn->prepare("SELECT e.employee_id, e.first_name, e.last_name,
        p.position_name AS position
        FROM employees e
        INNER JOIN positions p ON e.position_id = p.position_id
        WHERE first_name LIKE ? OR last_name LIKE ?
        LIMIT 5");
    $likeQuery = "$query%";
    $stmt->bind_param("ss", $likeQuery, $likeQuery);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "<p>No results found.</p>";
        exit;
    }

    while ($row = $result->fetch_assoc()) {
        echo "<div class='search-result-item'>
                <a href='../employees/profile.php?eid={$row['employee_id']}'>
                <img src='https://ui-avatars.com/api/?name={$row['first_name']}+{$row['last_name']}&background=random&size=34' alt='{$row['first_name']} {$row['last_name']} Avatar' class='avatar'>
                <div>
                    <span class='name'>{$row['first_name']} {$row['last_name']}</span>
                    <span class='position'>{$row['position']}</span>
                </div>
                </a>
            </div>";
    }