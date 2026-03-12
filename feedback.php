<?php
// feedback.php
$pageTitle = 'Feedback';
require_once 'includes/header.php';

$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = sanitize($_POST['name'] ?? '');
    $email   = sanitize($_POST['email'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    $rating  = intval($_POST['rating'] ?? 5);
    $uid     = isLoggedIn() ? $_SESSION['user_id'] : null;
    
    if ($message && $name && $email) {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO feedback (user_id, name, email, subject, message, rating) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("issssi", $uid, $name, $email, $subject, $message, $rating);
        $success = $stmt->execute();
        $db->close();
    }
}
?>
<meta name="site-url" content="<?= SITE_URL ?>">
<div class="container py-5" style="max-width:700px">
  <div class="section-title">
    <h2>💬 Your Feedback</h2>
    <p>We'd love to hear your thoughts and suggestions!</p>
    <div class="title-line"></div>
  </div>
  
  <?php if ($success): ?>
  <div class="card-fe p-5 text-center mt-4">
    <div style="font-size:5rem">🎉</div>
    <h4 class="mt-3">Thank You!</h4>
    <p class="text-muted">Your feedback has been submitted. We'll review it soon!</p>
    <a href="<?= SITE_URL ?>/index.php" class="btn-primary-fe mt-3">Back to Home</a>
  </div>
  <?php else: ?>
  <div class="card-fe p-4 mt-4">
    <form method="POST">
      <div class="row g-3">
        <div class="col-md-6">
          <div class="form-group">
            <label>Your Name *</label>
            <input type="text" name="name" class="form-control-fe" value="<?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>" required>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label>Email Address *</label>
            <input type="email" name="email" class="form-control-fe" value="<?= htmlspecialchars($_SESSION['user_email'] ?? '') ?>" required>
          </div>
        </div>
        <div class="col-12">
          <div class="form-group">
            <label>Subject</label>
            <input type="text" name="subject" class="form-control-fe" placeholder="e.g. App Suggestion, Delivery Issue...">
          </div>
        </div>
        <div class="col-12">
          <div class="form-group">
            <label>Overall Rating</label>
            <div class="d-flex gap-2 mt-1">
              <?php for ($i=1; $i<=5; $i++): ?>
              <label style="cursor:pointer;font-size:1.8rem">
                <input type="radio" name="rating" value="<?= $i ?>" style="display:none" <?= $i===5?'checked':'' ?>>
                <span class="star" style="transition:all 0.2s" 
                      onmouseover="highlightStars(<?= $i ?>)" 
                      onmouseout="resetStars()" 
                      onclick="selectStar(<?= $i ?>)">⭐</span>
              </label>
              <?php endfor; ?>
            </div>
          </div>
        </div>
        <div class="col-12">
          <div class="form-group">
            <label>Your Message *</label>
            <textarea name="message" class="form-control-fe" rows="5" placeholder="Tell us about your experience, suggestions or any issues..." required></textarea>
          </div>
        </div>
      </div>
      <button type="submit" class="btn-primary-fe w-100 justify-content-center mt-4">
        Send Feedback <i class="bi bi-send"></i>
      </button>
    </form>
  </div>
  <?php endif; ?>
</div>
<?php require_once 'includes/footer.php'; ?>
