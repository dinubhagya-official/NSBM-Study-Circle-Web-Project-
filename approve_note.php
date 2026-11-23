<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $note_id = $_GET['id'];
    $sql = "UPDATE notes SET status = 'approved' WHERE id = '$note_id'";
    
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Note Approved Successfully!'); window.location.href='admin_dashboard.php';</script>";
    } else {
        echo "Error updating record: " . $conn->error;
    }
}
?>