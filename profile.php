<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";


if (isset($_POST['update_profile'])) {
    $new_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $new_degree = mysqli_real_escape_string($conn, $_POST['degree']);
    $new_student_id = mysqli_real_escape_string($conn, $_POST['student_id']); // අලුතින් එකතු කළා
    
    if (!empty($_FILES['profile_pic']['name'])) {
        $filename = time() . "_" . $_FILES['profile_pic']['name'];
        $target = "uploads/" . $filename;
        
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target)) {
            $sql = "UPDATE students SET full_name='$new_name', degree='$new_degree', student_id='$new_student_id', profile_pic='$filename' WHERE id='$user_id'";
        }
    } else {
        $sql = "UPDATE students SET full_name='$new_name', degree='$new_degree', student_id='$new_student_id' WHERE id='$user_id'";
    }

    if ($conn->query($sql) === TRUE) {
        $_SESSION['user_name'] = $new_name; 
        $message = "<div style='color: #166534; background: #dcfce7; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-weight: 600;'>Profile updated successfully!</div>";
    } else {
        $message = "<div style='color: #991b1b; background: #fee2e2; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-weight: 600;'>Error updating profile.</div>";
    }
}

$sql = "SELECT * FROM students WHERE id='$user_id'";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

$profile_pic = !empty($user['profile_pic']) ? $user['profile_pic'] : 'default.png';
$user_name = $_SESSION['user_name'];
?>

<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Account Settings – NSBM Study Circle</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
  <style>
    :root { --primary-green: #22c55e; --primary-blue: #1d4ed8; --bg-color: #f8fafc; --text-dark: #1e293b; --text-gray: #64748b; --border-color: #e2e8f0; }
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
    body { background-color: var(--bg-color); display: flex; min-height: 100vh; }
    
    .sidebar { width: 270px; background: #fff; border-right: 1px solid var(--border-color); padding: 20px; display: flex; flex-direction: column; height: 100vh; position: fixed; left: 0; top: 0; z-index: 10; }
    .sidebar-brand { display: flex; align-items: center; padding-left: 0; margin-bottom: 25px; }
    .sidebar-brand img { width: 150px; object-fit: contain; }
    .user-info { background: #dcfce7; padding: 15px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; border: 1px solid #bbf7d0; transition: 0.2s; cursor: pointer; }
    .user-info:hover { background-color: #bbf7d0; }
    .user-info img { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary-green); }
    .user-info h4 { color: #1e293b; font-size: 15px; font-weight: 700; }
    .user-info p { color: #22c55e; font-size: 13px; font-weight: 600; }
    .nav-menu { list-style: none; padding: 0; display: flex; flex-direction: column; gap: 5px; }
    .nav-link { display: flex; align-items: center; gap: 12px; padding: 12px; text-decoration: none; color: var(--text-gray); border-radius: 8px; margin-bottom: 5px; font-weight: 500; transition: 0.2s;}
    .nav-link svg { width: 20px; height: 20px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;}
    .nav-link.active { background: var(--primary-green); color: white; }
    .nav-link:hover { background: #f1f5f9; color: var(--primary-green); }
    .nav-divider { border-top: 1px solid var(--border-color); margin: 15px 0; }

    .main-content { margin-left: 270px; flex: 1; padding: 40px; overflow-y: auto; display: flex; flex-direction: column; min-height: 100vh; }
    .page-title { font-size: 28px; font-weight: 800; color: var(--primary-blue); margin-bottom: 5px; }
    .page-subtitle { color: var(--text-gray); margin-bottom: 30px; font-size: 15px; }
    
    
    .profile-box { background: #fff; padding: 40px; border-radius: 20px; border: 1px solid var(--border-color); box-shadow: 0 4px 15px rgba(0,0,0,0.02); max-width: 850px; }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: var(--text-dark); }
    .form-group input { width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; outline: none; font-size: 14px; color: var(--text-dark); }
    .form-group input:focus { border-color: var(--primary-blue); box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.1); }
    .form-group input[readonly] { background: #f1f5f9; cursor: not-allowed; color: var(--text-gray); }
    
    
    .profile-upload-area { display: flex; align-items: center; gap: 25px; margin-bottom: 30px; padding-bottom: 25px; border-bottom: 1px solid var(--border-color); }
    .current-pic { width: 110px; height: 110px; border-radius: 50%; object-fit: cover; border: 4px solid var(--primary-green); box-shadow: 0 4px 10px rgba(34, 197, 94, 0.2); }
    .upload-btn-wrapper { position: relative; overflow: hidden; display: inline-block; }
    .btn-upload-pic { border: 1px solid var(--primary-blue); color: var(--primary-blue); background: #eff6ff; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: 0.2s; }
    .btn-upload-pic:hover { background: var(--primary-blue); color: white; }
    .upload-btn-wrapper input[type=file] { font-size: 100px; position: absolute; left: 0; top: 0; opacity: 0; cursor: pointer; }

    .btn-save { background: var(--primary-green); color: white; padding: 14px 35px; border: none; border-radius: 50px; font-weight: 600; cursor: pointer; transition: 0.3s; font-size: 16px; margin-top: 10px; box-shadow: 0 4px 15px rgba(34, 197, 94, 0.3); }
    .btn-save:hover { background: #16a34a; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(34, 197, 94, 0.4); }

    
    .footer { margin-top: auto; text-align: center; padding: 24px; font-size: 14px; color: var(--text-gray); border-top: 1px solid var(--border-color); }
  </style>
</head>
<body>

  
  <aside class="sidebar">
    <div class="sidebar-brand">
      <img src="images/nsbm-logo.png" alt="NSBM Logo">
    </div>
    <a href="profile.php" style="text-decoration: none;">
      <div class="user-info">
        <img src="uploads/<?php echo $profile_pic; ?>" alt="Profile">
        <div>
          <h4><?php echo htmlspecialchars($_SESSION['user_name']); ?></h4>
          <p>Student</p>
        </div>
      </div>
    </a>
    <ul class="nav-menu">
      <li><a href="dashboard.php" class="nav-link"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>Dashboard</a></li>
      <li><a href="my_notes.php" class="nav-link"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>My Notes</a></li>
      <li><a href="friends_notes.php" class="nav-link"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path><path d="M20 8h2v2h-2z"></path></svg>Friends Notes</a></li>
      <li><a href="study_groups.php" class="nav-link"><svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path></svg>Study Groups</a></li>
      <li><a href="qa_forum.php" class="nav-link"><svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>Q&A</a></li>
    </ul>
    <div class="nav-divider"></div>
    <ul class="nav-menu">
      <li><a href="profile.php" class="nav-link active"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>Account Settings</a></li>
      <li><a href="logout.php" class="nav-link" style="color: red;"><svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>Logout</a></li>
    </ul>
  </aside>

  
  <div class="main-content">
    <h1 class="page-title">Account Settings</h1>
    <p class="page-subtitle">Manage your profile information and account security.</p>
    
    <div class="profile-box">
        <?php echo $message; ?>
        
        <form action="profile.php" method="POST" enctype="multipart/form-data">
            
            
            <div class="profile-upload-area">
                <img src="uploads/<?php echo $profile_pic; ?>" alt="Profile" class="current-pic">
                <div>
                    <div class="upload-btn-wrapper">
                        <button type="button" class="btn-upload-pic">Change Photo</button>
                        <input type="file" name="profile_pic" accept="image/*" />
                    </div>
                    <p style="font-size: 13px; color: var(--text-gray); margin-top: 8px;">JPG, PNG or GIF. Max size 2MB.</p>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                </div>
                
                <!-- මෙතන තමයි Student ID එක type කරන්න පුළුවන් විදිහට වෙනස් කරලා තියෙන්නේ -->
                <div class="form-group">
                    <label>Student ID</label>
                    <input type="text" name="student_id" value="<?php echo htmlspecialchars($user['student_id'] ?? 'NSBM-2026-'.$user['id']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Campus Email</label>
                    <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                </div>

                <div class="form-group">
                    <label>Degree Programme</label>
                    <input type="text" name="degree" value="<?php echo htmlspecialchars($user['degree'] ?? ''); ?>" placeholder="e.g., BSc Software Engineering">
                </div>
            </div>

            <button type="submit" name="update_profile" class="btn-save">Save Changes</button>
        </form>
    </div>

    <footer class="footer">
      Designed by Faculty of Computing | Copyright © NSBM Green University
    </footer>
  </div>

</body>
</html>