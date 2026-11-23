<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); 
    
    if (!preg_match('/@(students\.)?nsbm\.ac\.lk$/', $email)) {
        echo "<script>alert('Error: Please use a valid NSBM student email (e.g., @students.nsbm.ac.lk)'); window.location.href='register.php';</script>";
        exit();
    }
    $checkEmail = "SELECT * FROM students WHERE email='$email'";
    $result = $conn->query($checkEmail);

    if ($result->num_rows > 0) {
        echo "<script>alert('This email is already registered!'); window.location.href='register.php';</script>";
    } else {
        $sql = "INSERT INTO students (full_name, email, password) VALUES ('$name', '$email', '$password')";
        
        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Registration Successful! Please login.'); window.location.href='login.php';</script>";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}
?>