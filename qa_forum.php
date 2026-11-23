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

// ---------------- Q&A LOGIC START ----------------

// 1. ප්‍රශ්නයක් අහනකොට (Create)
if(isset($_POST['ask_question'])){
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $question_text = mysqli_real_escape_string($conn, $_POST['question_text']);

    $insert_q = "INSERT INTO questions (user_id, subject, question_text) VALUES ('$user_id', '$subject', '$question_text')";
    if(mysqli_query($conn, $insert_q)){
        echo "<script>alert('Question posted successfully!'); window.location.href='qa_forum.php';</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
    }
}

// 2. ප්‍රශ්නයකට උත්තර දෙනකොට (Update)
if(isset($_POST['submit_answer'])){
    $q_id = $_POST['q_id'];
    $answer_text = mysqli_real_escape_string($conn, $_POST['answer_text']);
    
    $update_q = "UPDATE questions SET answer_text = '$answer_text', answered_by = '$user_id' WHERE id = '$q_id'";
    if(mysqli_query($conn, $update_q)){
        echo "<script>alert('Answer submitted!'); window.location.href='qa_forum.php';</script>";
    }
}

// ---------------- Q&A LOGIC END ----------------
?>

<html>
<head>
  <title>Q&A – NSBM Study Circle</title>
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
    .page-title { font-size: 32px; font-weight: 800; color: var(--primary-blue); margin-bottom: 5px; }
    .page-subtitle { color: var(--text-gray); margin-bottom: 30px; font-size: 15px; }

    /* Q&A STYLES */
    .qa-container { max-width: 900px; display: flex; flex-direction: column; gap: 25px; }
    .form-card, .question-card { background: #fff; padding: 25px; border-radius: 16px; border: 1px solid var(--border-color); box-shadow: 0 4px 15px rgba(0,0,0,0.02); }
    .form-card h3 { margin-bottom: 15px; color: var(--text-dark); font-size: 18px;}
    
    .form-control { width: 100%; padding: 12px 15px; border: 1px solid #cbd5e1; border-radius: 8px; margin-bottom: 15px; font-size: 14px; font-family: 'Inter', sans-serif; outline: none; transition: 0.2s; }
    .form-control:focus { border-color: var(--primary-green); box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2); }
    textarea.form-control { resize: vertical; min-height: 100px; }
    
    .btn-submit { background: var(--primary-green); color: white; border: none; padding: 12px 25px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s; font-size: 15px;}
    .btn-submit:hover { background: #16a34a; }
    
    .question-card h4 { color: var(--primary-blue); font-size: 18px; margin-bottom: 8px; }
    .q-meta { font-size: 13px; color: var(--text-gray); margin-bottom: 15px; display: flex; gap: 15px; align-items: center;}
    .q-meta span { background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 50px; font-weight: 600; font-size: 12px;}
    
    .answer-box { background: #f8fafc; border-left: 4px solid var(--primary-green); padding: 15px; border-radius: 0 8px 8px 0; margin-top: 15px; font-size: 14px;}
    .answer-box strong { color: var(--primary-green); display: block; margin-bottom: 5px; font-size: 13px; text-transform: uppercase;}
    
    .reply-form { display: flex; gap: 10px; margin-top: 15px; }
    .reply-form input { margin-bottom: 0; }
    .reply-form button { padding: 0 20px; white-space: nowrap;}

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
      <li><a href="study_groups.php" class="nav-link"><svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path></svg>Study Groups</a></li>
      
      <!-- මෙතන තමයි Sidebar එකේ Q&A කියලා වෙනස් කරලා තියෙන්නේ -->
      <li><a href="qa_forum.php" class="nav-link active"><svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>Q&A</a></li>
    </ul>
    <div class="nav-divider"></div>
    <ul class="nav-menu">
      <li><a href="profile.php" class="nav-link"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>Account Settings</a></li>
      <li><a href="logout.php" class="nav-link" style="color: red;"><svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>Logout</a></li>
    </ul>
  </aside>

  <div class="main-content">
    <!-- මෙතන තමයි පිටුවේ උඩ තියෙන Heading එක Q&A Discussion කියලා තියෙන්නේ -->
    <h1 class="page-title">Q&A Discussion</h1>
    <p class="page-subtitle">Ask your academic questions and help your batchmates.</p>

    <div class="qa-container">
      
      <!-- ප්‍රශ්නයක් අහන Form එක -->
      <div class="form-card">
        <h3>Ask a New Question</h3>
        <form method="POST" action="">
            <select name="subject" class="form-control" required>
                <option value="">Select Subject</option>
                <option value="Database">Database</option>
                <option value="Software Engineering">Software Engineering</option>
                <option value="Data Structures">Data Structures</option>
                <option value="Web Development">Web Development</option>
            </select>
            <textarea name="question_text" class="form-control" placeholder="Type your question in detail here..." required></textarea>
            <button type="submit" name="ask_question" class="btn-submit">Post Question</button>
        </form>
      </div>

      <h3 style="color: var(--text-dark); margin-top: 10px;">Recent Questions</h3>

      <!-- ප්‍රශ්න ටික පෙන්නන තැන -->
      <?php
      $fetch_q = "SELECT * FROM questions ORDER BY created_at DESC";
      $result = mysqli_query($conn, $fetch_q);
      
      if(mysqli_num_rows($result) > 0) {
          while($row = mysqli_fetch_assoc($result)){
      ?>
      
      <div class="question-card">
          <h4><?php echo htmlspecialchars($row['question_text']); ?></h4>
          <div class="q-meta">
              <span><?php echo htmlspecialchars($row['subject']); ?></span>
          </div>
          
          <?php if(!empty($row['answer_text'])) { ?>
              <!-- උත්තරේ තියෙනවා නම් මේක පෙන්නනවා -->
              <div class="answer-box">
                  <strong>Answer:</strong>
                  <?php echo htmlspecialchars($row['answer_text']); ?>
              </div>
          <?php } else { ?>
              <!-- උත්තරේ නැත්නම් උත්තර දෙන්න Form එක පෙන්නනවා -->
              <form class="reply-form" method="POST" action="">
                  <input type="hidden" name="q_id" value="<?php echo $row['id']; ?>">
                  <input type="text" name="answer_text" class="form-control" placeholder="Write your answer..." required>
                  <button type="submit" name="submit_answer" class="btn-submit">Reply</button>
              </form>
          <?php } ?>
      </div>

      <?php 
          } 
      } else {
          echo "<p style='color: var(--text-gray);'>No questions posted yet. Be the first to ask!</p>";
      }
      ?>

    </div>

    <footer class="footer">
      Designed by Faculty of Computing | Copyright © NSBM Green University
    </footer>
  </div>

</body>
</html>