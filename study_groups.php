<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$message = "";

// 1. Group එකක් හදනකොට
if (isset($_POST['create_group'])) {
    $group_name = mysqli_real_escape_string($conn, $_POST['group_name']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    $sql = "INSERT INTO study_groups (group_name, subject, description, created_by) 
            VALUES ('$group_name', '$subject', '$description', '$user_id')";

    if ($conn->query($sql) === TRUE) {
        $message = "<div style='color: #166534; background: #dcfce7; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-weight: 600;'>Group created successfully!</div>";
    } else {
        $message = "<div style='color: #991b1b; background: #fee2e2; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-weight: 600;'>Error creating group.</div>";
    }
}

// 2. Group එකකට Join වෙනකොට
if (isset($_POST['join_group'])) {
    $group_id = $_POST['group_id'];
    
    $check = "SELECT * FROM group_members WHERE group_id='$group_id' AND student_id='$user_id'";
    $check_res = $conn->query($check);
    
    if($check_res->num_rows > 0){
        $message = "<div style='color: #991b1b; background: #fee2e2; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-weight: 600;'>You are already a member of this group!</div>";
    } else {
        $join_sql = "INSERT INTO group_members (group_id, student_id) VALUES ('$group_id', '$user_id')";
        if ($conn->query($join_sql) === TRUE) {
            $message = "<div style='color: #166534; background: #dcfce7; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-weight: 600;'>Successfully joined the group!</div>";
        }
    }
}

// 3. Chat Message එකක් එවනකොට
if (isset($_POST['send_message'])) {
    $group_id = $_POST['group_id'];
    $msg_text = mysqli_real_escape_string($conn, $_POST['message_text']);

    if (!empty($msg_text)) {
        $msg_sql = "INSERT INTO group_messages (group_id, user_id, message) VALUES ('$group_id', '$user_id', '$msg_text')";
        if ($conn->query($msg_sql) === TRUE) {
            echo "<script>window.location.href='study_groups.php?view_id=" . $group_id . "';</script>";
            exit();
        }
    }
}

$userQuery = "SELECT profile_pic FROM students WHERE id='$user_id'";
$userResult = $conn->query($userQuery);
$userData = $userResult->fetch_assoc();
$profile_pic = $userData['profile_pic'] ?? 'default.png';
?>

<html>
<head>
  <title>Study Groups – NSBM Study Circle</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
  <style>
    /* ... CSS කොටස කලින් වගේමයි ... */
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
    .header-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; }
    .page-title { font-size: 32px; font-weight: 800; color: var(--primary-blue); margin-bottom: 5px; }
    .page-subtitle { color: var(--text-gray); font-size: 15px; }
    .search-box { display: flex; align-items: center; background: #fff; border: 1px solid var(--border-color); border-radius: 50px; padding: 10px 20px; width: 300px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); transition: 0.3s; }
    .search-box:focus-within { border-color: var(--primary-green); box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1); }
    .search-box svg { width: 18px; height: 18px; stroke: var(--text-gray); fill: none; stroke-width: 2; margin-right: 10px; }
    .search-box input { border: none; outline: none; width: 100%; font-size: 14px; color: var(--text-dark); background: transparent; }
    .groups-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
    .group-card { background: #fff; padding: 25px; border-radius: 16px; border: 1px solid var(--border-color); box-shadow: 0 4px 15px rgba(0,0,0,0.02); transition: 0.3s; display: flex; flex-direction: column; }
    .group-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.06); border-color: var(--primary-green); }
    .group-icon { width: 50px; height: 50px; background: #dcfce7; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 15px; }
    .group-icon svg { width: 24px; stroke: var(--primary-green); fill: none; stroke-width: 2; }
    .group-card h4 { color: var(--text-dark); font-size: 18px; margin-bottom: 5px; }
    .group-card p { font-size: 13px; color: var(--text-gray); margin-bottom: 8px; }
    .btn-join { display: flex; align-items: center; justify-content: center; gap: 8px; background: var(--primary-blue); color: white; padding: 12px; border-radius: 10px; text-decoration: none; font-weight: 600; font-size: 14px; margin-top: auto; transition: 0.2s; box-shadow: 0 4px 10px rgba(29, 78, 216, 0.3); border: none; width: 100%; cursor: pointer; }
    .btn-join:hover { background: #1e40af; transform: translateY(-2px); }
    .btn-joined { display: flex; align-items: center; justify-content: center; background: #dcfce7; color: #166534; padding: 12px; border-radius: 10px; font-weight: 600; font-size: 14px; width: 100%; border: none;}
    .btn-view { display: flex; align-items: center; justify-content: center; background: #f1f5f9; color: var(--text-dark); padding: 12px; border-radius: 10px; font-weight: 600; font-size: 14px; text-decoration: none; width: 100%; transition: 0.2s;}
    .btn-view:hover { background: #e2e8f0; }
    .create-form-box { background: #fff; padding: 30px; border-radius: 16px; border: 1px solid var(--border-color); margin-bottom: 40px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); }
    .create-form-box h3 { font-size: 20px; color: var(--text-dark); margin-bottom: 20px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 14px; color: var(--text-dark); }
    .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 10px; font-size: 14px; outline: none; }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: var(--primary-green); }
    .btn-create { background: var(--primary-green); color: white; padding: 12px 30px; border: none; border-radius: 50px; font-weight: 600; cursor: pointer; transition: 0.2s; font-size: 15px; margin-top: 10px; }
    .btn-create:hover { background: #16a34a; }
    .no-groups { text-align: center; padding: 50px; color: var(--text-gray); background: #fff; border-radius: 12px; border: 1px dashed var(--border-color); grid-column: 1 / -1; }
    .back-btn { display: inline-flex; align-items: center; gap: 8px; color: var(--text-gray); text-decoration: none; font-weight: 600; margin-bottom: 20px; transition: 0.2s; }
    .back-btn:hover { color: var(--primary-green); }
    .group-header { background: #fff; padding: 30px; border-radius: 16px; border: 1px solid var(--border-color); margin-bottom: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); border-left: 6px solid var(--primary-green); }
    .group-header h1 { color: var(--primary-blue); font-size: 28px; margin-bottom: 10px; }
    .group-header p { color: var(--text-gray); margin-bottom: 5px; font-size: 15px; }
    .group-badge { background: #e0f2fe; color: #0369a1; padding: 5px 12px; border-radius: 50px; font-weight: 600; font-size: 13px; display: inline-block; margin-bottom: 15px; }
    .members-section h3 { color: var(--text-dark); font-size: 20px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
    .members-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; margin-bottom: 40px; }
    .member-card { background: #fff; padding: 20px; border-radius: 12px; border: 1px solid var(--border-color); display: flex; align-items: center; gap: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); }
    .member-card img { width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary-green); }
    .member-card h4 { color: var(--text-dark); font-size: 16px; margin-bottom: 3px; }
    .member-card p { color: var(--text-gray); font-size: 12px; }
    .chat-section { background: #fff; border-radius: 16px; border: 1px solid var(--border-color); box-shadow: 0 4px 15px rgba(0,0,0,0.02); display: flex; flex-direction: column; height: 400px; }
    .chat-header { padding: 15px 20px; border-bottom: 1px solid var(--border-color); font-weight: 700; color: var(--text-dark); background: #f8fafc; border-radius: 16px 16px 0 0; }
    .chat-messages { flex: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 15px; background: #fff;}
    .message-bubble { display: flex; flex-direction: column; max-width: 70%; }
    .message-bubble.mine { align-self: flex-end; align-items: flex-end; }
    .message-bubble.others { align-self: flex-start; align-items: flex-start; }
    .msg-sender { font-size: 12px; color: var(--text-gray); margin-bottom: 3px; margin-left: 5px;}
    .msg-text { padding: 10px 15px; border-radius: 15px; font-size: 14px; line-height: 1.4; }
    .mine .msg-text { background: var(--primary-green); color: white; border-bottom-right-radius: 2px; }
    .others .msg-text { background: #f1f5f9; color: var(--text-dark); border-bottom-left-radius: 2px; }
    .chat-input-area { display: flex; padding: 15px; border-top: 1px solid var(--border-color); gap: 10px; }
    .chat-input-area input { flex: 1; padding: 12px 15px; border: 1px solid var(--border-color); border-radius: 50px; outline: none; font-size: 14px;}
    .chat-input-area input:focus { border-color: var(--primary-green); }
    .btn-send { background: var(--primary-green); color: white; border: none; padding: 0 20px; border-radius: 50px; cursor: pointer; font-weight: 600; transition: 0.2s; }
    .btn-send:hover { background: #16a34a; }
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
          <h4><?php echo htmlspecialchars($user_name); ?></h4>
          <p>Student</p>
        </div>
      </div>
    </a>
    <ul class="nav-menu">
      <li><a href="dashboard.php" class="nav-link"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>Dashboard</a></li>
      <li><a href="my_notes.php" class="nav-link"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>My Notes</a></li>
      <li><a href="friends_notes.php" class="nav-link"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path><path d="M20 8h2v2h-2z"></path></svg>Friends Notes</a></li>
      <li><a href="study_groups.php" class="nav-link active"><svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path></svg>Study Groups</a></li>
      <li><a href="qa_forum.php" class="nav-link"><svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>Q&A</a></li>
    </ul>
    <div class="nav-divider"></div>
    <ul class="nav-menu">
      <li><a href="profile.php" class="nav-link"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>Account Settings</a></li>
      <li><a href="logout.php" class="nav-link" style="color: red;"><svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>Logout</a></li>
    </ul>
  </aside>

  <div class="main-content">
    
    <?php 
    if (isset($_GET['view_id'])) { 
        $view_id = $_GET['view_id'];
        
        $v_sql = "SELECT study_groups.*, students.full_name as creator_name 
                  FROM study_groups 
                  JOIN students ON study_groups.created_by = students.id 
                  WHERE study_groups.id = '$view_id'";
        $v_result = $conn->query($v_sql);
        $v_group = $v_result->fetch_assoc();

        $m_sql = "SELECT students.full_name, students.email, students.profile_pic 
                  FROM group_members 
                  JOIN students ON group_members.student_id = students.id 
                  WHERE group_members.group_id = '$view_id' 
                  ORDER BY group_members.joined_at ASC";
        $m_result = $conn->query($m_sql);

        // Chat messages ටික ගන්නවා
        $chat_sql = "SELECT group_messages.*, students.full_name 
                     FROM group_messages 
                     JOIN students ON group_messages.user_id = students.id 
                     WHERE group_messages.group_id = '$view_id' 
                     ORDER BY group_messages.created_at ASC";
        $chat_result = $conn->query($chat_sql);
    ?>
        <a href="study_groups.php" class="back-btn">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            Back to Groups
        </a>

        <div class="group-header">
            <div class="group-badge"><?php echo htmlspecialchars($v_group['subject']); ?></div>
            <h1><?php echo htmlspecialchars($v_group['group_name']); ?></h1>
            <p><?php echo htmlspecialchars($v_group['description'] ?: 'No description provided.'); ?></p>
            <hr style="border: none; border-top: 1px solid var(--border-color); margin: 15px 0;">
            <p style="font-size: 14px;">Created by: <strong><?php echo htmlspecialchars($v_group['creator_name']); ?></strong></p>
        </div>

        <div class="members-section">
            <h3>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--primary-green)" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                Group Members (<?php echo $m_result->num_rows; ?>)
            </h3>
            
            <div class="members-grid">
                <?php
                if ($m_result->num_rows > 0) {
                    while($mem = $m_result->fetch_assoc()) {
                        $mem_pic = $mem['profile_pic'] ? $mem['profile_pic'] : 'default.png';
                        echo "<div class='member-card'>";
                        echo "<img src='uploads/" . htmlspecialchars($mem_pic) . "' alt='Profile'>";
                        echo "<div>";
                        echo "<h4>" . htmlspecialchars($mem['full_name']) . "</h4>";
                        echo "<p>" . htmlspecialchars($mem['email']) . "</p>";
                        echo "</div>";
                        echo "</div>";
                    }
                } else {
                    echo "<p style='color: var(--text-gray);'>No members have joined this group yet.</p>";
                }
                ?>
            </div>
        </div>

        <!-- ================= CHAT SECTION ================= -->
        <div class="chat-section">
            <div class="chat-header">
                Group Chat
            </div>
            <div class="chat-messages" id="chatBox">
                <?php
                if ($chat_result->num_rows > 0) {
                    while($chat = $chat_result->fetch_assoc()) {
                        $is_mine = ($chat['user_id'] == $user_id);
                        $bubble_class = $is_mine ? 'mine' : 'others';
                        
                        echo "<div class='message-bubble $bubble_class'>";
                        // අනිත් කෙනා එවපු මැසේජ් එකක් නම් විතරක් නම පෙන්නනවා
                        if(!$is_mine) {
                            echo "<span class='msg-sender'>" . htmlspecialchars($chat['full_name']) . "</span>";
                        }
                        echo "<div class='msg-text'>" . htmlspecialchars($chat['message']) . "</div>";
                        echo "</div>";
                    }
                } else {
                    echo "<p style='color: var(--text-gray); text-align:center; margin-top:20px;'>No messages yet. Say Hi!</p>";
                }
                ?>
            </div>
            <form class="chat-input-area" method="POST" action="">
                <input type="hidden" name="group_id" value="<?php echo $view_id; ?>">
                <input type="text" name="message_text" placeholder="Type your message here..." required autocomplete="off">
                <button type="submit" name="send_message" class="btn-send">Send</button>
            </form>
        </div>
        <!-- ================= CHAT SECTION END ================= -->

        <!-- Auto scroll to bottom of chat -->
        <script>
            var chatBox = document.getElementById("chatBox");
            chatBox.scrollTop = chatBox.scrollHeight;
        </script>

    <?php 
    } else { 
        
        $sql = "SELECT study_groups.*, students.full_name, 
                (SELECT COUNT(*) FROM group_members gm WHERE gm.group_id = study_groups.id) as member_count 
                FROM study_groups 
                JOIN students ON study_groups.created_by = students.id 
                ORDER BY study_groups.created_at DESC";
        $result = $conn->query($sql);
    ?>
        <div class="header-row">
          <div>
            <h1 class="page-title">Study Groups</h1>
            <p class="page-subtitle">Create or join study groups to study together with your batchmates.</p>
          </div>
          <div class="search-box">
            <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <input type="text" id="searchInput" placeholder="Search groups..." onkeyup="filterGroups()">
          </div>
        </div>

        <?php echo $message; ?>

        <div class="create-form-box">
          <h3>Create a New Study Group</h3>
          <form action="study_groups.php" method="POST">
            <div class="form-row">
              <div class="form-group">
                <label>Group Name</label>
                <input type="text" name="group_name" placeholder="e.g., SE Batch 26 Group" required>
              </div>
              <div class="form-group">
                <label>Subject</label>
                <select name="subject" required>
                  <option value="">Select Subject</option>
                  <option value="Software Engineering">Software Engineering</option>
                  <option value="Data Structures">Data Structures</option>
                  <option value="Web Development">Web Development</option>
                  <option value="Database">Database</option>
                  <option value="Mathematics">Mathematics</option>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label>Description (Optional)</label>
              <textarea name="description" rows="2" placeholder="What is this group about?"></textarea>
            </div>
            <button type="submit" name="create_group" class="btn-create">Create Group</button>
          </form>
        </div>

        <h3 style="margin-bottom: 20px; color: var(--text-dark);">Available Groups</h3>
        <div class="groups-grid" id="groupsGrid">
          <?php
          if ($result->num_rows > 0) {
              while($row = $result->fetch_assoc()) {
                  $group_id = $row['id'];
                  $check_my = "SELECT * FROM group_members WHERE group_id='$group_id' AND student_id='$user_id'";
                  $is_joined = $conn->query($check_my)->num_rows > 0;

                  echo "<div class='group-card'>";
                  echo "<div class='group-icon'><svg viewBox='0 0 24 24'><path d='M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2'></path><circle cx='9' cy='7' r='4'></circle><path d='M23 21v-2a4 4 0 0 0-3-3.87'></path><path d='M16 3.13a4 4 0 0 1 0 7.75'></path></svg></div>";
                  echo "<h4 class='group-name'>" . htmlspecialchars($row['group_name']) . "</h4>";
                  echo "<p><strong>Subject:</strong> " . htmlspecialchars($row['subject']) . "</p>";
                  echo "<p>" . htmlspecialchars($row['description']) . "</p>";
                  echo "<p><strong>Created by:</strong> " . htmlspecialchars($row['full_name']) . "</p>";
                  echo "<p style='color: var(--primary-green); font-weight:700; font-size:13px; margin-bottom:15px;'>👥 " . $row['member_count'] . " members</p>";
                  
                  if($is_joined) {
                      echo "<div style='display:flex; gap:10px; margin-top:auto;'>";
                      echo "<div class='btn-joined' style='flex:1;'>✓ Joined</div>";
                      echo "<a href='study_groups.php?view_id=$group_id' class='btn-view' style='flex:1;'>View Group</a>";
                      echo "</div>";
                  } else {
                      echo "<div style='display:flex; flex-direction:column; gap:10px; margin-top:auto;'>";
                      echo "<a href='study_groups.php?view_id=$group_id' class='btn-view'>View Group Members</a>";
                      echo "<form method='POST' action='' style='margin:0;'>";
                      echo "<input type='hidden' name='group_id' value='$group_id'>";
                      echo "<button type='submit' name='join_group' class='btn-join' style='width:100%;'>Join Group</button>";
                      echo "</form>";
                      echo "</div>";
                  }
                  echo "</div>";
              }
          } else {
              echo "<div class='no-groups'>No study groups created yet. Be the first to create one!</div>";
          }
          ?>
        </div>

    <?php } ?>

    <footer class="footer">
      Designed by Faculty of Computing | Copyright © NSBM Green University
    </footer>
  </div>

  <script>
    function filterGroups() {
        let input = document.getElementById('searchInput').value.toLowerCase();
        let cards = document.querySelectorAll('.group-card');
        cards.forEach(card => {
            let groupName = card.querySelector('.group-name').innerText.toLowerCase();
            if (groupName.includes(input)) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }
  </script>

</body>
</html>