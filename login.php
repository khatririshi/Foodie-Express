<?php
// login.php
require_once 'includes/config.php';
if (isLoggedIn()) { header('Location: ' . SITE_URL . '/dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    
    if (!$email || !$pass) {
        $error = 'Please fill in all fields.';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
        if ($user && password_verify($pass, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email']= $user['email'];
            $db->close();
            $redirect = $_GET['redirect'] ?? SITE_URL . '/dashboard.php';
            header("Location: $redirect");
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
        $db->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login | FoodieExpress</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="<?= SITE_URL ?>/css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">🍽️ FoodieExpress</div>
    <div class="auth-subtitle">Welcome back! Sign in to continue</div>
    
    <?php if ($error): ?>
    <div class="alert alert-danger rounded-3 py-2 px-3 mb-3" style="font-size:0.9rem"><?= $error ?></div>
    <?php endif; ?>
    
    <form method="POST">
      <div class="form-group">
        <label>Email Address</label>
        <input type="email" name="email" class="form-control-fe" placeholder="you@example.com" required>
      </div>
      <div class="form-group">
        <label>Password</label>
        <div style="position:relative">
          <input type="password" name="password" id="loginPass" class="form-control-fe" placeholder="Enter password" required>
          <button type="button" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted)" onclick="togglePass('loginPass')">
            <i class="bi bi-eye"></i>
          </button>
        </div>
      </div>
      <button type="submit" class="btn-primary-fe w-100 justify-content-center mt-2">
        Sign In <i class="bi bi-arrow-right"></i>
      </button>
    </form>
    
    <div class="text-center mt-4" style="font-size:0.9rem;color:var(--text-muted)">
      Don't have an account? <a href="<?= SITE_URL ?>/register.php" style="color:var(--primary);font-weight:600">Sign Up</a>
    </div>
    <div class="text-center mt-2">
      <a href="<?= SITE_URL ?>/index.php" style="font-size:0.85rem;color:var(--text-muted)">← Back to Home</a>
    </div>
  </div>
</div>
<script>
function togglePass(id) {
    const el = document.getElementById(id);
    el.type = el.type === 'password' ? 'text' : 'password';
}
</script>
</body></html>
