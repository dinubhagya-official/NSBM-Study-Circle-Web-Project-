<?php
session_start();
require 'db.php';

if (isset($_POST['submit'])) {
    $student_id = $_SESSION['user_id'];
    $subject = $_POST['subject'];
    
    
    $file_name = $_FILES['noteFile']['name'];
    $file_tmp = $_FILES['noteFile']['tmp_name'];
    
    
    $file_name = preg_replace("/[^a-zA-Z0-9.]/", "_", $file_name);
    $new_file_name = time() . "_" . $file_name;
    $upload_dir = "uploads/" . $new_file_name;

    if (move_uploaded_file($file_tmp, $upload_dir)) {
        $sql = "INSERT INTO notes (student_id, subject, title, file_path) VALUES ('$student_id', '$subject', '$file_name', '$upload_dir')";
        
        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Note uploaded successfully! Waiting for Admin approval.'); window.location.href='dashboard.php';</script>";
        } else {
            echo "Database Error: " . $conn->error;
        }
    } else {
        echo "<script>alert('Failed to upload file!'); window.location.href='dashboard.php';</script>";
    }
}
?>