<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$admin_check = $conn->query("SELECT role FROM students WHERE id='$user_id'")->fetch_assoc();
if ($admin_check['role'] != 'admin') {
    echo "<script>alert('Access Denied! You are not an admin.'); window.location.href='dashboard.php';</script>";
    exit();
}

$user_name = "Admin";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_rating'])) {
        $note_id = intval($_POST['note_id']);
        $rating = intval($_POST['rating']);
        $stmt = $conn->prepare("INSERT INTO note_ratings (note_id, student_id, rating) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE rating = ?");
        $stmt->bind_param("iiii", $note_id, $user_id, $rating, $rating);
        $stmt->execute();
        header("Location: admin_dashboard.php");
        exit();
    }
    
    if (isset($_POST['submit_comment'])) {
        $note_id = intval($_POST['note_id']);
        $comment = $conn->real_escape_string($_POST['comment']);
        $stmt = $conn->prepare("INSERT INTO note_comments (note_id, student_id, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $note_id, $user_id, $comment);
        $stmt->execute();
        header("Location: admin_dashboard.php");
        exit();
    }
}

if (isset($_GET['action']) && isset($_GET['id'])) {
    $note_id = intval($_GET['id']);
    $action = $_GET['action'];
    
    if ($action == 'approve') {
        $conn->query("UPDATE notes SET status = 'approved' WHERE id = $note_id");
    } elseif ($action == 'reject') {
        $conn->query("UPDATE notes SET status = 'rejected' WHERE id = $note_id");
    } elseif ($action == 'delete') {
        $conn->query("DELETE FROM notes WHERE id = $note_id");
    }
    header("Location: admin_dashboard.php");
    exit();
}

if (isset($_GET['delete_student'])) {
    $del_id = intval($_GET['delete_student']);
    $conn->query("DELETE FROM students WHERE id = $del_id");
    header("Location: admin_dashboard.php");
    exit();
}

if (isset($_GET['delete_comment'])) {
    $del_comment_id = intval($_GET['delete_comment']);
    $conn->query("DELETE FROM note_comments WHERE id = $del_comment_id");
    header("Location: admin_dashboard.php");
    exit();
}

$total_students = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'];
$pending_notes = $conn->query("SELECT COUNT(*) as count FROM notes WHERE status = 'pending'")->fetch_assoc()['count'];
$total_comments = $conn->query("SELECT COUNT(*) as count FROM note_comments")->fetch_assoc()['count'];

$notes_sql = "SELECT n.*, s.full_name, 
              (SELECT AVG(rating) FROM note_ratings WHERE note_id = n.id) as avg_rating,
              (SELECT COUNT(*) FROM note_ratings WHERE note_id = n.id) as total_votes
              FROM notes n 
              JOIN students s ON n.student_id = s.id 
              ORDER BY n.upload_date DESC";
$notes_result = $conn->query($notes_sql);

$students_sql = "SELECT * FROM students ORDER BY id DESC";
$students_result = $conn->query($students_sql);
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - NSBM Study Circle</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary-green: #22c55e; --primary-blue: #1d4ed8; --bg-color: #f8fafc; --text-dark: #1e293b; --text-gray: #64748b; --border-color: #e2e8f0; }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-color); display: flex; min-height: 100vh; }

        .sidebar { width: 270px; background: #fff; border-right: 1px solid var(--border-color); padding: 20px; display: flex; flex-direction: column; height: 100vh; position: fixed; left: 0; top: 0; z-index: 10; }
        .sidebar-brand { display: flex; align-items: center; padding-left: 0; margin-bottom: 25px; }
        .sidebar-brand img { width: 150px; object-fit: contain; }
        .user-info { background: #dcfce7; padding: 15px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; border: 1px solid #bbf7d0; }
        .user-info .avatar { width: 45px; height: 45px; background: #ffffff; color: var(--primary-green); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 18px; border: 2px solid var(--primary-green); }
        .user-info h4 { color: #1e293b; font-size: 15px; font-weight: 700; }
        .user-info p { color: #22c55e; font-size: 13px; font-weight: 600; }
        .nav-menu { list-style: none; padding: 0; display: flex; flex-direction: column; gap: 5px; }
        .nav-link { display: flex; align-items: center; gap: 12px; padding: 12px; text-decoration: none; color: var(--text-gray); border-radius: 8px; margin-bottom: 5px; font-weight: 500; transition: 0.2s;}
        .nav-link svg { width: 20px; height: 20px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;}
        .nav-link.active { background: var(--primary-green); color: white; }
        .nav-link:hover { background: #f1f5f9; color: var(--primary-green); }
        .nav-divider { border-top: 1px solid var(--border-color); margin: 15px 0; }

        .main-content { margin-left: 270px; flex: 1; padding: 40px; overflow-y: auto; display: flex; flex-direction: column; min-height: 100vh; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #fff; padding: 20px; border-radius: 12px; border: 1px solid var(--border-color); box-shadow: 0 4px 10px rgba(0,0,0,0.02); display: flex; justify-content: space-between; align-items: center; }
        .stat-info h3 { color: var(--text-gray); font-size: 13px; margin-bottom: 5px; font-weight: 600; }
        .stat-info h2 { color: var(--text-dark); font-size: 28px; font-weight: 800; }
        .stat-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
        .stat-icon.blue { background: #eff6ff; color: var(--primary-blue); }
        .stat-icon.green { background: #ecfdf5; color: var(--primary-green); }
        .stat-icon.purple { background: #f5f3ff; color: #8b5cf6; }
        .stat-icon.orange { background: #fff7ed; color: #f97316; }
        .stat-icon svg { width: 24px; height: 24px; fill: none; stroke: currentColor; stroke-width: 2; }

        .section-header { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; color: var(--primary-blue); font-size: 20px; font-weight: 800; }
        .section-header svg { width: 24px; height: 24px; stroke: currentColor; fill: none; stroke-width: 2; }
        
        .table-container { background: #fff; border-radius: 12px; border: 1px solid var(--border-color); box-shadow: 0 4px 10px rgba(0,0,0,0.02); overflow-x: auto; margin-bottom: 40px; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background: #f8fafc; color: var(--text-gray); font-size: 13px; font-weight: 600; padding: 15px 20px; border-bottom: 1px solid var(--border-color); }
        td { padding: 15px 20px; border-bottom: 1px solid var(--border-color); font-size: 14px; color: var(--text-dark); vertical-align: top; }
        tr:last-child td { border-bottom: none; }
        tr:hover { background: #f8fafc; }

        .note-link { display: flex; align-items: center; gap: 8px; color: var(--primary-blue); font-weight: 600; text-decoration: none; }
        .note-link svg { width: 18px; height: 18px; fill: none; stroke: currentColor; stroke-width: 2; }

        .badge { padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; display: inline-block; text-align: center; }
        .badge.approved { background: #dcfce7; color: #166534; }
        .badge.pending { background: #fef08a; color: #854d0e; }
        .badge.rejected { background: #fee2e2; color: #991b1b; }

        .btn { padding: 8px 12px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; transition: 0.2s; }
        .btn-approve { background: #dcfce7; color: #166534; }
        .btn-approve:hover { background: #bbf7d0; }
        .btn-reject { background: #fef3c7; color: #b45309; }
        .btn-reject:hover { background: #fde68a; }
        .btn-delete { background: #fee2e2; color: #ef4444; }
        .btn-delete:hover { background: #fecaca; }
        .btn svg { width: 14px; height: 14px; fill: none; stroke: currentColor; stroke-width: 2; }
        
        .action-buttons { display: flex; gap: 8px; flex-wrap: wrap;}

        .admin-forms { margin-top: 15px; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; }
        .admin-forms form { display: flex; gap: 10px; margin-bottom: 10px; align-items: center; }
        .admin-forms select, .admin-forms input[type="text"] { padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; outline: none; }
        .admin-forms select { width: 100px; }
        .admin-forms input[type="text"] { flex: 1; }
        .admin-forms button { padding: 8px 15px; background: var(--primary-blue); color: white; border: none; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; }
        .admin-forms button.rate-btn { background: #eab308; }
        
        .comment-list { margin-top: 15px; max-height: 150px; overflow-y: auto; background: #fff; padding: 10px; border-radius: 8px; font-size: 12px; border: 1px solid #e2e8f0;}
        .comment-item { margin-bottom: 8px; border-bottom: 1px dashed #cbd5e1; padding-bottom: 8px; display: flex; justify-content: space-between; align-items: flex-start; }
        .comment-item:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        .comment-item strong { color: #1e293b; }

        .footer { margin-top: auto; text-align: center; padding: 24px; font-size: 14px; color: var(--text-gray); border-top: 1px solid var(--border-color); }

        @media (max-width: 1200px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; }
            .stats-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

  <aside class="sidebar">
    <div class="sidebar-brand">
      <img src="images/nsbm-logo.png" alt="NSBM Logo">
    </div>
    <div class="user-info">
      <div class="avatar">AD</div>
      <div>
        <h4><?php echo $user_name; ?></h4>
        <p>Administrator</p>
      </div>
    </div>
    <ul class="nav-menu">
      <li><a href="admin_dashboard.php" class="nav-link active"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>Dashboard</a></li>
    </ul>
    <div class="nav-divider"></div>
    <ul class="nav-menu">
      <li><a href="logout.php" class="nav-link" style="color: red;"><svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>Logout</a></li>
    </ul>
  </aside>

  <div class="main-content">
    
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-info">
          <h3>Registered Students</h3>
          <h2><?php echo $total_students; ?></h2>
        </div>
        <div class="stat-icon blue">
          <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-info">
          <h3>Pending Notes</h3>
          <h2><?php echo $pending_notes; ?></h2>
        </div>
        <div class="stat-icon green">
          <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-info">
          <h3>Total Comments</h3>
          <h2><?php echo $total_comments; ?></h2>
        </div>
        <div class="stat-icon orange">
          <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-info">
          <h3>Q&A Interactions</h3>
          <h2>0</h2>
        </div>
        <div class="stat-icon purple">
          <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
        </div>
      </div>
    </div>

    <div class="section-header">
      <svg viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
      All Uploaded Notes
    </div>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Note Details</th>
            <th>Admin Actions (Rate & Comment)</th>
            <th>Manage Note</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($notes_result->num_rows > 0) {
              while($row = $notes_result->fetch_assoc()) {
                  $status_class = strtolower($row['status']);
                  $status_text = ucfirst($row['status']);
                  $avg_rating = $row['avg_rating'] ? round($row['avg_rating'], 1) : 0;
                  $total_votes = $row['total_votes'];
                  $note_id = $row['id'];
                  
                  $comments_query = "SELECT c.*, s.full_name FROM note_comments c JOIN students s ON c.student_id = s.id WHERE c.note_id = $note_id ORDER BY c.created_at DESC LIMIT 5";
                  $comments_result = $conn->query($comments_query);
                  
                  echo "<tr>";
                  
                  echo "<td style='width: 30%;'>
                          <a href='" . $row['file_path'] . "' target='_blank' class='note-link'><svg viewBox='0 0 24 24'><path d='M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z'></path><polyline points='14 2 14 8 20 8'></polyline></svg>" . htmlspecialchars($row['title']) . "</a>
                          <div style='font-size:12px; color:#64748b; margin-top:5px;'>By: " . htmlspecialchars($row['full_name']) . " | Subject: " . htmlspecialchars($row['subject']) . "</div>
                          <div style='margin-top:8px;'><span class='badge $status_class'>$status_text</span></div>
                          <div style='font-weight:700; color:#eab308; font-size:13px; margin-top:8px;'>⭐ Average: " . $avg_rating . " / 5 (" . $total_votes . " votes)</div>
                        </td>";
                  
                  echo "<td style='width: 35%;'>";
                  echo "<div class='admin-forms'>";
                  
                  echo "<form method='POST'>";
                  echo "<input type='hidden' name='note_id' value='$note_id'>";
                  echo "<select name='rating' required><option value=''>Rate</option>";
                  for($i=1; $i<=5; $i++) { echo "<option value='$i'>$i Star</option>"; }
                  echo "</select>";
                  echo "<button type='submit' name='submit_rating' class='rate-btn'>Rate Note</button>";
                  echo "</form>";

                  echo "<form method='POST'>";
                  echo "<input type='hidden' name='note_id' value='$note_id'>";
                  echo "<input type='text' name='comment' placeholder='Write an admin comment...' required>";
                  echo "<button type='submit' name='submit_comment'>Send</button>";
                  echo "</form>";
                  
                  echo "</div>";

                  if ($comments_result->num_rows > 0) {
                      echo "<div class='comment-list'>";
                      while($c_row = $comments_result->fetch_assoc()) {
                          echo "<div class='comment-item'>";
                          echo "<div><strong>" . htmlspecialchars($c_row['full_name']) . ":</strong> " . htmlspecialchars($c_row['comment']) . "</div>";
                          echo "<a href='admin_dashboard.php?delete_comment=" . $c_row['id'] . "' class='btn btn-delete' style='padding:2px 6px; font-size:10px;' onclick=\"return confirm('Delete this comment?');\">Delete</a>";
                          echo "</div>";
                      }
                      echo "</div>";
                  }
                  echo "</td>";

                  echo "<td style='width: 25%;'>
                          <div class='action-buttons'>
                            <a href='admin_dashboard.php?action=approve&id=" . $row['id'] . "' class='btn btn-approve' title='Approve'><svg viewBox='0 0 24 24'><polyline points='20 6 9 17 4 12'></polyline></svg>Approve</a>
                            <a href='admin_dashboard.php?action=reject&id=" . $row['id'] . "' class='btn btn-reject' title='Reject'><svg viewBox='0 0 24 24'><line x1='18' y1='6' x2='6' y2='18'></line><line x1='6' y1='6' x2='18' y2='18'></line></svg>Reject</a>
                            <a href='admin_dashboard.php?action=delete&id=" . $row['id'] . "' class='btn btn-delete' onclick=\"return confirm('Are you sure you want to delete this note?');\" title='Delete'><svg viewBox='0 0 24 24'><polyline points='3 6 5 6 21 6'></polyline><path d='M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2'></path></svg>Delete Note</a>
                          </div>
                        </td>";
                  echo "</tr>";
              }
          } else {
              echo "<tr><td colspan='3' style='text-align: center; color: #64748b;'>No notes found.</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>

    <div class="section-header">
      <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path><path d="M20 8h2v2h-2z"></path></svg>
      Manage Registered Students
    </div>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Student ID</th>
            <th>Profile</th>
            <th>Full Name</th>
            <th>Email Address</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($students_result->num_rows > 0) {
              while($student = $students_result->fetch_assoc()) {
                  $pic = !empty($student['profile_pic']) ? $student['profile_pic'] : 'default.png';
                  
                  echo "<tr>";
                  echo "<td>#" . $student['id'] . "</td>";
                  echo "<td><img src='uploads/" . $pic . "' alt='Profile' style='width:35px; height:35px; border-radius:50%; object-fit:cover; border:1px solid #e2e8f0;'></td>";
                  echo "<td>" . htmlspecialchars($student['full_name']) . "</td>";
                  echo "<td>" . htmlspecialchars($student['email']) . "</td>";
                  echo "<td>
                          <a href='admin_dashboard.php?delete_student=" . $student['id'] . "' class='btn btn-delete' onclick=\"return confirm('Are you sure you want to delete this student? This will also delete all their notes.');\">
                            <svg viewBox='0 0 24 24'><polyline points='3 6 5 6 21 6'></polyline><path d='M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2'></path></svg>
                            Delete
                          </a>
                        </td>";
                  echo "</tr>";
              }
          } else {
              echo "<tr><td colspan='5' style='text-align: center; color: #64748b;'>No students found.</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>

    <footer class="footer">
      Designed by Faculty of Computing | Copyright © NSBM Green University
    </footer>
  </div>

</body>
</html>