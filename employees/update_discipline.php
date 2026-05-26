<?php
    include("../common/dbconnection.php");

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $discipline_id = (int) $_POST['discipline_id'];
        $date_input = trim($_POST['date']);
        $date = date("Y-m-d", strtotime($date_input));
        $type = trim($_POST['type']);
        $reason = trim($_POST['reason']);
        $action_taken = trim($_POST['action_taken']);
        $acknowledged = isset($_POST['acknowledged']) ? 1 : 0;

        $stmt = $conn->prepare("UPDATE discipline SET date = ?, type = ?, reason = ?, action_taken = ?, acknowledged = ?, updated_on = NOW() WHERE discipline_id = ?");
        $stmt->bind_param("ssssii", $date, $type, $reason, $action_taken, $acknowledged, $discipline_id);

        if ($stmt->execute()) {
            echo json_encode([
                "success" => true,
                "message" => "Discipline record updated successfully"
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Error updating discipline record"
            ]);
        }

        $stmt->close();
    }