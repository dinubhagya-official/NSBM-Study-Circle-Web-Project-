<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

$userQuery = "SELECT profile_pic FROM students WHERE id='$user_id'";
$userResult = $conn->query($userQuery);
$userData = $userResult->fetch_assoc();
$profile_pic = $userData['profile_pic'] ?? 'default.png';

$countSql = "SELECT COUNT(*) AS total FROM notes WHERE student_id='$user_id'";
$countResult = $conn->query($countSql);
$totalNotes = $countResult->fetch_assoc()['total'];

$groupSql = "SELECT COUNT(*) AS total FROM study_groups";
$groupResult = $conn->query($groupSql);
$totalGroups = $groupResult->fetch_assoc()['total'];

$friendsSql = "SELECT COUNT(*) AS total FROM notes WHERE status = 'approved' AND student_id != '$user_id'";
$friendsResult = $conn->query($friendsSql);
$totalFriendsNotes = $friendsResult->fetch_assoc()['total'];

?>
<html>
<head>
  <title>Dashboard – NSBM Study Circle</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root { --bg-main: #f8fafc; --sidebar-bg: #ffffff; --primary-blue: #1d4ed8; --primary-green: #22c55e; --light-green: #dcfce7; --light-blue: #eff6ff; --text-dark: #1e293b; --text-gray: #64748b; --border-color: #e2e8f0; }
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
    body { display: flex; height: 100vh; background-color: var(--bg-main); color: var(--text-dark); overflow: hidden; }
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
    .main-wrapper { margin-left: 270px; flex: 1; display: flex; flex-direction: column; height: 100vh; overflow-y: auto; position: relative; }
    .topbar { display: flex; align-items: center; justify-content: space-between; padding: 20px 40px; background-color: var(--bg-main); z-index: 5; }
    .search-bar { display: flex; align-items: center; gap: 12px; background-color: #ffffff; border: 1px solid var(--border-color); border-radius: 50px; padding: 12px 24px; width: 500px; box-shadow: 0 2px 8px rgba(0,0,0,0.02); }
    .search-bar svg { width: 18px; stroke: var(--text-gray); fill: none; stroke-width: 2; }
    .search-bar input { border: none; outline: none; width: 100%; font-size: 14px; color: var(--text-dark); background: transparent; }
    .topbar-actions { display: flex; align-items: center; gap: 16px; }
    .notification-btn { position: relative; background: #ffffff; border: 1px solid var(--border-color); width: 44px; height: 44px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s; }
    .notification-btn:hover { background-color: var(--light-blue); border-color: var(--primary-blue); }
    .notification-btn svg { width: 20px; stroke: var(--text-gray); fill: none; stroke-width: 2; }
    .notification-btn:hover svg { stroke: var(--primary-blue); }
    .notification-dot { position: absolute; top: 10px; right: 12px; width: 8px; height: 8px; background-color: #ef4444; border-radius: 50%; border: 2px solid #ffffff; }
    .dashboard-content { padding: 10px 40px 40px; position: relative; z-index: 2; }
    .welcome-text h1 { font-size: 32px; font-weight: 800; margin-bottom: 8px; color: var(--primary-blue); }
    .welcome-text h1 span { color: var(--primary-green); }
    .welcome-text p { color: var(--text-gray); font-size: 15px; margin-bottom: 30px; }
    .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-bottom: 40px; }
    .stat-card { background-color: #ffffff; border-radius: 20px; padding: 24px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid var(--border-color); transition: transform 0.3s; }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
    .stat-info p { font-size: 14px; color: var(--text-gray); margin-bottom: 8px; font-weight: 600; }
    .stat-info h2 { font-size: 32px; font-weight: 800; color: var(--text-dark); }
    .stat-icon { width: 56px; height: 56px; border-radius: 16px; display: flex; align-items: center; justify-content: center; }
    .stat-card:nth-child(1) .stat-icon { background-color: var(--light-blue); }
    .stat-card:nth-child(1) .stat-icon svg { stroke: var(--primary-blue); }
    .stat-card:nth-child(2) .stat-icon { background-color: var(--light-green); }
    .stat-card:nth-child(2) .stat-icon svg { stroke: var(--primary-green); }
    .stat-card:nth-child(3) .stat-icon { background-color: var(--light-blue); }
    .stat-card:nth-child(3) .stat-icon svg { stroke: var(--primary-blue); }
    .stat-icon svg { width: 28px; height: 28px; fill: none; stroke-width: 2.5; }
    .action-area { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 70px 30px; background: linear-gradient(145deg, #ffffff, #f0fdf4); border-radius: 30px; border: 2px dashed #86efac; margin-bottom: 30px; position: relative; overflow: hidden; box-shadow: 0 10px 30px rgba(34, 197, 94, 0.05); }
    .action-area::before { content: ''; position: absolute; width: 200px; height: 200px; background: rgba(34, 197, 94, 0.1); border-radius: 50%; top: -50px; left: -50px; z-index: 0; }
    .action-area::after { content: ''; position: absolute; width: 150px; height: 150px; background: rgba(29, 78, 216, 0.05); border-radius: 50%; bottom: -50px; right: -50px; z-index: 0; }
    .upload-icon-wrapper { width: 80px; height: 80px; background-color: #ffffff; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; box-shadow: 0 8px 25px rgba(34, 197, 94, 0.15); border: 1px solid #bbf7d0; position: relative; z-index: 1; }
    .upload-icon-wrapper svg { width: 36px; height: 36px; stroke: var(--primary-green); fill: none; stroke-width: 2; }
    .action-area h3 { margin-bottom: 12px; font-size: 24px; color: var(--text-dark); font-weight: 800; position: relative; z-index: 1; }
    .action-area p { color: var(--text-gray); font-size: 16px; margin-bottom: 30px; max-width: 550px; line-height: 1.6; text-align: center; position: relative; z-index: 1; }
    .upload-form-box { display: flex; flex-direction: column; align-items: center; width: 100%; max-width: 400px; position: relative; z-index: 1; }
    .form-group { width: 100%; margin-bottom: 15px; text-align: left; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 14px; color: var(--text-dark); }
    .form-group select, .form-group input[type="file"] { width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 10px; background: #fff; font-size: 14px; outline: none; }
    .form-group select:focus { border-color: var(--primary-green); }
    .btn-upload-submit { background-color: var(--primary-green); color: #ffffff; border: none; padding: 14px 40px; border-radius: 50px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 6px 20px rgba(34, 197, 94, 0.4); display: flex; align-items: center; justify-content: center; gap: 10px; margin-top: 10px; width: 100%; }
    .btn-upload-submit svg { width: 20px; height: 20px; fill: none; stroke: currentColor; stroke-width: 2.5; }
    .btn-upload-submit:hover { background-color: #16a34a; transform: translateY(-3px); box-shadow: 0 8px 25px rgba(34, 197, 94, 0.5); }
    .footer { margin-top: auto; text-align: center; padding: 24px; font-size: 14px; color: var(--text-gray); border-top: 1px solid var(--border-color); }
    @media (max-width: 992px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } .topbar { flex-direction: column; gap: 15px; } .search-bar { width: 100%; } }
    @media (max-width: 768px) { .sidebar { display: none; } .main-wrapper { margin-left: 0; } .stats-grid { grid-template-columns: 1fr; } }
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
          <h4><?php echo htmlspecialchars($user_name); ?></h4>
          <p>Student</p>
        </div>
      </div>
    </a>
    <ul class="nav-menu">
      <li><a href="dashboard.php" class="nav-link active"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>Dashboard</a></li>
      <li><a href="my_notes.php" class="nav-link"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>My Notes</a></li>
      <li><a href="friends_notes.php" class="nav-link"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path><path d="M20 8h2v2h-2z"></path></svg>Friends Notes</a></li>
      <li><a href="study_groups.php" class="nav-link"><svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path></svg>Study Groups</a></li>
      <li><a href="qa_forum.php" class="nav-link"><svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>Q&A</a></li>
    </ul>
    <div class="nav-divider"></div>
    <ul class="nav-menu">
      <li><a href="profile.php" class="nav-link"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>Account Settings</a></li>
      <li><a href="logout.php" class="nav-link" style="color: red;"><svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>Logout</a></li>
    </ul>
  </aside>

  <div class="main-wrapper">
    <header class="topbar">
      <div class="search-bar">
        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        <input type="text" placeholder="Search Lecture Notes, Questions, Study Groups...">
      </div>
      <div class="topbar-actions">
        <button class="notification-btn">
          <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
          <span class="notification-dot"></span>
        </button>
      </div>
    </header>

    <div class="dashboard-content">
      <div class="welcome-text">
        <h1>Welcome Back, <span><?php echo htmlspecialchars($user_name); ?>!</span></h1>
        <p>Let's make today a great day of learning and sharing.</p>
      </div>

      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-info">
            <p>Total Notes Shared</p>
            <h2><?php echo $totalNotes; ?></h2> 
          </div>
          <div class="stat-icon"><svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg></div>
        </div>
        <div class="stat-card">
          <div class="stat-info">
            <p>Study Groups Joined</p>
            <h2><?php echo $totalGroups; ?></h2>
          </div>
          <div class="stat-icon"><svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path></svg></div>
        </div>
        <div class="stat-card">
          <div class="stat-info">
            <p>Friends Notes Available</p>
            <h2><?php echo $totalFriendsNotes; ?></h2>
          </div>
          <div class="stat-icon"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg></div>
        </div>
      </div>

      <div class="action-area">
        <div class="upload-icon-wrapper">
          <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
        </div>
        <h3>Help others learn better!</h3>
        <p>Share your lecture notes, past papers, and study guides. Your contribution can make a huge difference for your batchmates.</p>
        
        <form action="upload_note.php" method="POST" enctype="multipart/form-data" class="upload-form-box">
          <div class="form-group">
            <label>Select Subject</label>
            <select name="subject" required>
              <option value="">Choose a Subject...</option>
              <option value="Software Engineering">Software Engineering</option>
              <option value="Data Structures">Data Structures</option>
              <option value="Web Development">Web Development</option>
              <option value="Database">Database</option>
              <option value="Mathematics">Mathematics</option>
            </select>
          </div>
          <div class="form-group">
            <label>Choose File (PDF/Doc)</label>
            <input type="file" name="noteFile" required>
          </div>
          <button type="submit" name="submit" class="btn-upload-submit">
            <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
            Upload a File
          </button>
        </form>
      </div>
    </div>

    <footer class="footer">Designed by Faculty of Computing | Copyright © NSBM Green University</footer>
  </div>
</body>
</html>