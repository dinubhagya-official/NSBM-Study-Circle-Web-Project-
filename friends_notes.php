<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

if (isset($_GET['delete_comment'])) {
    $del_id = intval($_GET['delete_comment']);

    $conn->query("DELETE FROM note_comments WHERE id = $del_id AND student_id = $user_id");
    header("Location: friends_notes.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_comment'])) {
    $comment_id = intval($_POST['comment_id']);
    $new_comment = $conn->real_escape_string($_POST['new_comment']);
  
    $conn->query("UPDATE note_comments SET comment = '$new_comment', edited = 1 WHERE id = $comment_id AND student_id = $user_id");
    header("Location: friends_notes.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_rating'])) {
        $note_id = intval($_POST['note_id']);
        $rating = intval($_POST['rating']);
        $stmt = $conn->prepare("INSERT INTO note_ratings (note_id, student_id, rating) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE rating = ?");
        $stmt->bind_param("iiii", $note_id, $user_id, $rating, $rating);
        $stmt->execute();
        header("Location: friends_notes.php");
        exit();
    }
    
    if (isset($_POST['submit_comment'])) {
        $note_id = intval($_POST['note_id']);
        $comment = $conn->real_escape_string($_POST['comment']);
        $stmt = $conn->prepare("INSERT INTO note_comments (note_id, student_id, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $note_id, $user_id, $comment);
        $stmt->execute();
        header("Location: friends_notes.php");
        exit();
    }
}

$userQuery = "SELECT profile_pic FROM students WHERE id='$user_id'";
$userResult = $conn->query($userQuery);
$userData = $userResult->fetch_assoc();
$profile_pic = $userData['profile_pic'] ?? 'default.png';

$sql = "SELECT notes.*, students.full_name FROM notes 
        JOIN students ON notes.student_id = students.id 
        WHERE notes.status = 'approved' AND notes.student_id != '$user_id' 
        ORDER BY notes.upload_date DESC";
$result = $conn->query($sql);
?>
<html>
<head>
  <title>Friends Notes – NSBM Study Circle</title>
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
    .header-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; }
    .page-title { font-size: 32px; font-weight: 800; color: var(--primary-blue); margin-bottom: 5px; }
    .page-subtitle { color: var(--text-gray); font-size: 15px; }
    
    .search-box { 
        display: flex; align-items: center; background: #fff; border: 1px solid var(--border-color); 
        border-radius: 50px; padding: 10px 20px; width: 300px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); transition: 0.3s;
    }
    .search-box:focus-within { border-color: var(--primary-green); box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1); }
    .search-box svg { width: 18px; height: 18px; stroke: var(--text-gray); fill: none; stroke-width: 2; margin-right: 10px; }
    .search-box input { border: none; outline: none; width: 100%; font-size: 14px; color: var(--text-dark); background: transparent; }
    
    .notes-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; }
    .note-card { background: #fff; padding: 20px; border-radius: 12px; border: 1px solid var(--border-color); box-shadow: 0 4px 10px rgba(0,0,0,0.02); transition: 0.3s; display: flex; flex-direction: column; }
    .note-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.06); border-color: var(--primary-green); }
    .note-icon { width: 40px; height: 40px; background: #eff6ff; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 15px; }
    .note-icon svg { width: 20px; stroke: var(--primary-blue); fill: none; stroke-width: 2; }
    
    .note-card h4 { color: var(--primary-blue); font-size: 16px; margin-bottom: 8px; word-break: break-all; }
    .note-card p { font-size: 13px; color: var(--text-gray); margin-bottom: 5px; }
    .note-actions a { display: block; background: #eff6ff; color: var(--primary-blue); text-align: center; padding: 10px; border-radius: 8px; text-decoration: none; font-weight: 600; margin: 15px 0; transition: 0.2s; }
    .note-actions a:hover { background: #dbeafe; }

    .interaction-area { border-top: 1px dashed #e2e8f0; padding-top: 15px; margin-top: auto; }
    .rating-form { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
    .rating-form select { padding: 6px; border-radius: 6px; border: 1px solid #cbd5e1; outline: none; font-size: 13px; }
    .rating-form button { padding: 6px 12px; background: #eab308; color: white; border: none; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; }
    
    .comment-form { display: flex; gap: 8px; margin-top: 10px; }
    .comment-form input { flex: 1; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; font-size: 13px; }
    .comment-form button { padding: 8px 12px; background: var(--primary-blue); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 13px; }
    
    .comment-list { margin-top: 10px; max-height: 150px; overflow-y: auto; background: #f8fafc; padding: 10px; border-radius: 8px; font-size: 12px; border: 1px solid #e2e8f0; }
    .comment-item { margin-bottom: 8px; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; }
    .comment-item:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
    .comment-list strong { color: #1e293b; }
    .comment-actions { display: flex; gap: 10px; margin-top: 4px; }
    .comment-actions button, .comment-actions a { background: none; border: none; font-size: 11px; font-weight: 600; cursor: pointer; text-decoration: none; }
    .comment-actions .edit-btn { color: #eab308; }
    .comment-actions .delete-btn { color: #ef4444; }
    .edited-tag { font-size: 10px; color: #94a3b8; font-style: italic; margin-left: 5px; }

    .no-notes { text-align: center; padding: 50px; color: var(--text-gray); background: #fff; border-radius: 12px; border: 1px dashed var(--border-color); grid-column: 1 / -1; }
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
      <li><a href="friends_notes.php" class="nav-link active"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path><path d="M20 8h2v2h-2z"></path></svg>Friends Notes</a></li>
      <li><a href="study_groups.php" class="nav-link"><svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path></svg>Study Groups</a></li>
      <li><a href="qa_forum.php" class="nav-link"><svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>Q&A</a></li>
    </ul>
    <div class="nav-divider"></div>
    <ul class="nav-menu">
      <li><a href="profile.php" class="nav-link"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>Account Settings</a></li>
      <li><a href="logout.php" class="nav-link" style="color: red;"><svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>Logout</a></li>
    </ul>
  </aside>
  <div class="main-content">
    <div class="header-row">
      <div>
        <h1 class="page-title">Friends Notes</h1>
        <p class="page-subtitle">Explore study materials shared by your batchmates.</p>
      </div>
      <div class="search-box">
        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        <input type="text" id="searchInput" placeholder="Search friends notes..." onkeyup="filterNotes()">
      </div>
    </div>

    <div class="notes-grid" id="notesGrid">
      <?php
      if ($result->num_rows > 0) {
          while($row = $result->fetch_assoc()) {
              $file_link = $row['file_path']; 
              $note_id = $row['id'];
              
              $avg_rating_query = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_votes FROM note_ratings WHERE note_id = $note_id";
              $avg_result = $conn->query($avg_rating_query);
              $rating_data = $avg_result->fetch_assoc();
              $avg_rating = $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : 0;
              $total_votes = $rating_data['total_votes'];

              $comments_query = "SELECT c.*, s.full_name FROM note_comments c JOIN students s ON c.student_id = s.id WHERE c.note_id = $note_id ORDER BY c.created_at DESC LIMIT 5";
              $comments_result = $conn->query($comments_query);
              
              echo "<div class='note-card'>";
              
              echo "<div class='note-icon'><svg viewBox='0 0 24 24'><path d='M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z'></path><polyline points='14 2 14 8 20 8'></polyline><line x1='16' y1='13' x2='8' y2='13'></line><line x1='16' y1='17' x2='8' y2='17'></line></svg></div>";
              echo "<h4 class='note-title'>" . htmlspecialchars($row['title']) . "</h4>";
              echo "<p class='note-subject'><strong>Subject:</strong> " . htmlspecialchars($row['subject']) . "</p>";
              echo "<p><strong>Shared by:</strong> " . htmlspecialchars($row['full_name']) . "</p>";
              echo "<p><strong>Date:</strong> " . date('M d, Y', strtotime($row['upload_date'])) . "</p>";
              echo "<div class='note-actions'><a href='" . $file_link . "' target='_blank'>Click to View / Download</a></div>";
              
              echo "<div class='interaction-area'>";
              echo "<div style='font-size: 13px; font-weight: bold; color: #eab308; margin-bottom: 8px;'>⭐ Rating: " . $avg_rating . " / 5 <span style='color: #64748b; font-weight: normal;'>(" . $total_votes . " votes)</span></div>";

              echo "<form method='POST' class='rating-form'>";
              echo "<input type='hidden' name='note_id' value='$note_id'>";
              echo "<select name='rating' required>";
              echo "<option value=''>Rate</option>";
              for($i=1; $i<=5; $i++) { echo "<option value='$i'>$i Star</option>"; }
              echo "</select>";
              echo "<button type='submit' name='submit_rating'>Rate</button>";
              echo "</form>";

              if ($comments_result->num_rows > 0) {
                  echo "<div class='comment-list'>";
                  while($c_row = $comments_result->fetch_assoc()) {
                      $edited_tag = ($c_row['edited'] == 1) ? "<span class='edited-tag'>(edited)</span>" : "";
                      echo "<div class='comment-item'>";
                      echo "<div><strong>" . htmlspecialchars($c_row['full_name']) . ":</strong> " . htmlspecialchars($c_row['comment']) . " $edited_tag</div>";
                      
                      if ($c_row['student_id'] == $user_id) {
                          echo "<div class='comment-actions'>";

                          echo "<button type='button' class='edit-btn' onclick=\"editComment(" . $c_row['id'] . ", '" . addslashes(htmlspecialchars($c_row['comment'])) . "')\">Edit</button>";
                          echo "<a href='friends_notes.php?delete_comment=" . $c_row['id'] . "' class='delete-btn' onclick=\"return confirm('Are you sure you want to delete this comment?');\">Delete</a>";
                          echo "</div>";
                      }
                      echo "</div>";
                  }
                  echo "</div>";
              } else {
                  echo "<div style='font-size: 12px; color: #94a3b8; margin-top: 5px; font-style: italic;'>No comments yet.</div>";
              }

              echo "<form method='POST' class='comment-form'>";
              echo "<input type='hidden' name='note_id' value='$note_id'>";
              echo "<input type='text' name='comment' placeholder='Add a comment...' required>";
              echo "<button type='submit' name='submit_comment'>Send</button>";
              echo "</form>";

              echo "</div>"; 
              echo "</div>"; 
          }
      } else {
          echo "<div class='no-notes'>No notes have been shared by others yet. Check back later!</div>";
      }
      ?>
    </div>

    <form id="editForm" method="POST" style="display: none;">
        <input type="hidden" name="comment_id" id="edit_comment_id">
        <input type="hidden" name="new_comment" id="edit_new_comment">
        <input type="hidden" name="update_comment" value="1">
    </form>

    <footer class="footer">
      Designed by Faculty of Computing | Copyright © NSBM Green University
    </footer>
  </div>

  <script>
    function filterNotes() {
        let input = document.getElementById('searchInput').value.toLowerCase();
        let cards = document.querySelectorAll('.note-card');

        cards.forEach(card => {
            let title = card.querySelector('.note-title').innerText.toLowerCase();
            let subject = card.querySelector('.note-subject').innerText.toLowerCase();
            
            if (title.includes(input) || subject.includes(input)) {
                card.style.display = 'flex'; 
            } else {
                card.style.display = 'none';
            }
        });
    }

    function editComment(id, currentText) {
        var newText = prompt("Edit your comment:", currentText);
        if (newText !== null && newText.trim() !== "") {
            document.getElementById('edit_comment_id').value = id;
            document.getElementById('edit_new_comment').value = newText;
            document.getElementById('editForm').submit();
        }
    }
  </script>

</body>
</html>