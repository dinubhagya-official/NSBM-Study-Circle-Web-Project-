<?php
require_once 'db.php'; 
session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

if(isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
  
    $check_admin = $conn->query("SELECT role FROM users WHERE id=$delete_id");
    $admin_data = $check_admin->fetch_assoc();
    
    if($admin_data && $admin_data['role'] == 'admin') {
        $error_msg = "Admin ගිණුමක් අයින් කළ නොහැක!";
    } else {
        $conn->query("DELETE FROM users WHERE id=$delete_id");
        header("Location: manage_users.php?msg=deleted");
        exit();
    }
}
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
       
        body { display: flex; margin: 0; background: #f8fafc; font-family: 'Segoe UI', Tahoma, sans-serif; height: 100vh; overflow: hidden;}
        .sidebar { width: 260px; background: #fff; height: 100vh; border-right: 1px solid #e2e8f0; padding-top: 20px;}
        .main-content { flex: 1; padding: 40px; overflow-y: auto; }
        .page-title { color: #1e3a8a; font-size: 32px; margin-bottom: 10px; }
        .page-subtitle { color: #64748b; font-size: 16px; margin-bottom: 30px; }
        
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #10b981; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #ef4444; }

      
        .table-container { background: white; border-radius: 12px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); overflow: hidden; border: 1px solid #e2e8f0;}
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background: #f1f5f9; color: #334155; padding: 15px; font-weight: 600; font-size: 14px; border-bottom: 1px solid #e2e8f0;}
        td { padding: 15px; border-bottom: 1px solid #e2e8f0; color: #475569; font-size: 14px; }
        tr:hover { background: #f8fafc; }
        
        .role-badge { padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .role-admin { background: #fce7f3; color: #db2777; }
        .role-student { background: #e0f2fe; color: #0284c7; }

        .btn-delete { background: #ef4444; color: white; padding: 8px 12px; text-decoration: none; border-radius: 6px; font-size: 13px; font-weight: bold; transition: 0.2s;}
        .btn-delete:hover { background: #dc2626; }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <div class="main-content">
        <h1 class="page-title">Manage Users</h1>
        <p class="page-subtitle">View all registered students and manage their accounts.</p>

        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> User account successfully deleted.</div>
        <?php endif; ?>

        <?php if(isset($error_msg)): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-triangle"></i> <?php echo $error_msg; ?></div>
        <?php endif; ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Full Name</th>
                        <th>University Email</th>
                        <th>Role</th>
                        <th>Registered Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                   
                    $sql = "SELECT * FROM users ORDER BY created_at DESC";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $role_class = ($row['role'] == 'admin') ? 'role-admin' : 'role-student';
                            $formatted_date = date('M d, Y', strtotime($row['created_at']));
                            
                            echo "<tr>";
                            echo "<td>" . $row['id'] . "</td>";
                            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                            echo "<td><span class='role-badge $role_class'>" . ucfirst($row['role']) . "</span></td>";
                            echo "<td>" . $formatted_date . "</td>";
                            
                            if($row['role'] != 'admin') {
                                echo "<td><a href='manage_users.php?delete_id=" . $row['id'] . "' class='btn-delete' onclick=\"return confirm('Are you sure you want to delete this user? This will also delete all their uploaded notes.');\"><i class='fas fa-trash'></i> Delete</a></td>";
                            } else {
                                echo "<td style='color:#94a3b8; font-size:12px;'>Protected</td>";
                            }
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align:center;'>No users found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>