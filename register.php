<?php
// register.php
require_once 'includes/config.php';
if (isLoggedIn()) { header('Location: ' . SITE_URL . '/dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $conf  = $_POST['confirm_password'] ?? '';

    if (!$name || !$email || !$pass) {
        $error = 'Please fill all required fields.';
    } elseif ($pass !== $conf) {
        $error = 'Passwords do not match.';
    } elseif (strlen($pass) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $db = getDB();
        $check = $db->prepare("SELECT id FROM users WHERE email=?");
        $check->bind_param("s", $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = 'Email already registered. Please login.';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (name, email, phone, password) VALUES (?,?,?,?)");
            $stmt->bind_param("ssss", $name, $email, $phone, $hash);
            if ($stmt->execute()) {
                $uid = $db->insert_id;
                $_SESSION['user_id']    = $uid;
                $_SESSION['user_name']  = $name;
                $_SESSION['user_email'] = $email;
                $db->close();
                setFlash('success', "Welcome to FoodieExpress, $name! 🎉");
                header('Location: ' . SITE_URL . '/dashboard.php');
                exit;
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
        $db->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register | FoodieExpress</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="<?= SITE_URL ?>/css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-page">
  <div class="auth-card" style="max-width:520px">
    <div class="auth-logo">🍽️ FoodieExpress</div>
    <div class="auth-subtitle">Create your account and start ordering!</div>
    
    <?php if ($error): ?>
    <div class="alert alert-danger rounded-3 py-2 px-3 mb-3" style="font-size:0.9rem"><?= $error ?></div>
    <?php endif; ?>
    
    <form method="POST">
      <div class="row g-3">
        <div class="col-12">
          <div class="form-group">
            <label>Full Name *</label>
            <input type="text" name="name" class="form-control-fe" placeholder="Your full name" required>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label>Email Address *</label>
            <input type="email" name="email" class="form-control-fe" placeholder="you@example.com" required>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label>Phone Number</label>
            <input type="tel" name="phone" class="form-control-fe" placeholder="10-digit number">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label>Password *</label>
            <input type="password" name="password" class="form-control-fe" placeholder="Min 6 characters" required>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label>Confirm Password *</label>
            <input type="password" name="confirm_password" class="form-control-fe" placeholder="Repeat password" required>
          </div>
        </div>
        
        <!-- Health Profile (Optional) -->
        <div class="col-12">
          <div style="background:var(--silver-bg);border-radius:var(--radius-sm);padding:1.2rem;margin-top:0.5rem">
            <div style="font-weight:600;margin-bottom:1rem;font-size:0.9rem">🧠 Health Profile <span style="color:var(--text-muted);font-weight:400">(Optional)</span></div>
            <div class="row g-2">
              <div class="col-md-6">
                <label style="font-size:0.82rem;font-weight:600;display:block;margin-bottom:0.3rem">Weight Goal</label>
                <select name="weight_goal" class="form-control-fe" style="padding:0.6rem">
                  <option value="maintain">Maintain Weight</option>
                  <option value="lose">Lose Weight</option>
                  <option value="gain">Gain Weight</option>
                </select>
              </div>
              <div class="col-md-6">
                <label style="font-size:0.82rem;font-weight:600;display:block;margin-bottom:0.3rem">Health Condition</label>
                <select name="health_condition" class="form-control-fe" style="padding:0.6rem">
                  <option value="none">None</option>
                  <option value="pcos">PCOS</option>
                  <option value="diabetes">Diabetes</option>
                  <option value="fitness">Fitness Goal</option>
                  <option value="low_carb">Low Carb Diet</option>
                </select>
              </div>
              <div class="col-12">
                <label style="font-size:0.82rem;font-weight:600;display:block;margin-bottom:0.3rem">Daily Calorie Goal (kcal)</label>
                <input type="number" name="daily_calories" class="form-control-fe" placeholder="e.g. 1800" value="2000" style="padding:0.6rem">
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <button type="submit" class="btn-primary-fe w-100 justify-content-center mt-4">
        Create Account <i class="bi bi-arrow-right"></i>
      </button>
    </form>
    
    <div class="text-center mt-4" style="font-size:0.9rem;color:var(--text-muted)">
      Already have an account? <a href="<?= SITE_URL ?>/login.php" style="color:var(--primary);font-weight:600">Sign In</a>
    </div>
  </div>
</div>
</body></html>
