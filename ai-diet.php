<?php
$pageTitle = 'AI Diet Assistant';
require_once 'includes/header.php';

$recommendations = [];
$analysisData = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['weight_goal'])) {
    $goal      = sanitize($_POST['weight_goal'] ?? $_GET['weight_goal'] ?? 'maintain');
    $condition = sanitize($_POST['health_condition'] ?? $_GET['condition'] ?? 'none');
    $calories  = intval($_POST['daily_calories'] ?? $_GET['calories'] ?? 2000);
    
    $db = getDB();
    
    // Build recommendation query based on goals/conditions
    $conditions = ["f.is_available=1"];
    
    if ($goal === 'lose') {
        $conditions[] = "f.calories < 450";
    } elseif ($goal === 'gain') {
        $conditions[] = "(f.calories > 400 OR f.protein > 20)";
    } else {
        $conditions[] = "f.calories < 600";
    }
    
    if ($condition === 'pcos') {
        $conditions[] = "f.health_tags LIKE '%pcos-friendly%'";
    } elseif ($condition === 'diabetes') {
        $conditions[] = "(f.health_tags LIKE '%diabetic-friendly%' OR f.health_tags LIKE '%low-sugar%')";
    } elseif ($condition === 'fitness') {
        $conditions[] = "f.protein > 15";
    } elseif ($condition === 'low_carb') {
        $conditions[] = "f.carbs < 30";
    }
    
    $whereSQL = implode(' AND ', $conditions);
    $recommendations = $db->query("SELECT f.*, r.name as restaurant_name, r.delivery_time FROM food_items f 
                                    JOIN restaurants r ON f.restaurant_id=r.id 
                                    WHERE $whereSQL ORDER BY f.rating DESC, f.protein DESC LIMIT 12")->fetch_all(MYSQLI_ASSOC);
    
    // Save profile if logged in
    if (isLoggedIn()) {
        $uid = $_SESSION['user_id'];
        $stmt = $db->prepare("UPDATE users SET weight_goal=?, health_condition=?, daily_calories=? WHERE id=?");
        $stmt->bind_param("ssii", $goal, $condition, $calories, $uid);
        $stmt->execute();
    }
    
    $analysisData = ['goal' => $goal, 'condition' => $condition, 'calories' => $calories];
    $db->close();
}

// Load saved profile if logged in
$userProfile = null;
if (isLoggedIn() && !$analysisData) {
    $db = getDB();
    $uid = $_SESSION['user_id'];
    $userProfile = $db->query("SELECT * FROM users WHERE id=$uid")->fetch_assoc();
    $db->close();
}
?>
<meta name="site-url" content="<?= SITE_URL ?>">

<!-- HERO BANNER -->
<div style="background:linear-gradient(135deg,#1a1a2e 0%,#16213e 60%,#0f3460 100%);padding:4rem 0 3rem">
  <div class="container text-center">
    <div class="ai-badge mb-3" style="display:inline-flex">✨ Powered by Smart Algorithms</div>
    <h1 style="color:white;font-family:'Playfair Display',serif;font-size:2.8rem;margin-bottom:1rem">🧠 AI Diet & Health Assistant</h1>
    <p style="color:rgba(255,255,255,0.7);font-size:1.1rem;max-width:600px;margin:0 auto">
      Get personalized meal recommendations based on your health goals, weight targets, and medical conditions.
    </p>
  </div>
</div>

<div class="container py-5">
  <div class="row g-4">
    
    <!-- INPUT FORM -->
    <div class="col-lg-4">
      <div class="card-fe p-4" style="position:sticky;top:80px">
        <h5 style="font-family:'Playfair Display',serif;margin-bottom:1.5rem">Tell Us About You</h5>
        
        <form method="POST">
          <div class="form-group">
            <label>Weight Goal</label>
            <select name="weight_goal" class="form-control-fe">
              <option value="lose" <?= ($analysisData['goal'] ?? $userProfile['weight_goal'] ?? '') === 'lose' ? 'selected' : '' ?>>🔻 Lose Weight</option>
              <option value="maintain" <?= ($analysisData['goal'] ?? $userProfile['weight_goal'] ?? 'maintain') === 'maintain' ? 'selected' : '' ?>>⚖️ Maintain Weight</option>
              <option value="gain" <?= ($analysisData['goal'] ?? $userProfile['weight_goal'] ?? '') === 'gain' ? 'selected' : '' ?>>📈 Gain Weight / Muscle</option>
            </select>
          </div>
          
          <div class="form-group mt-3">
            <label>Health Condition</label>
            <select name="health_condition" class="form-control-fe">
              <option value="none" <?= ($analysisData['condition'] ?? $userProfile['health_condition'] ?? 'none') === 'none' ? 'selected' : '' ?>>✅ None / General Health</option>
              <option value="pcos" <?= ($analysisData['condition'] ?? '') === 'pcos' ? 'selected' : '' ?>>🌸 PCOS</option>
              <option value="diabetes" <?= ($analysisData['condition'] ?? '') === 'diabetes' ? 'selected' : '' ?>>🩸 Diabetes / Blood Sugar</option>
              <option value="fitness" <?= ($analysisData['condition'] ?? '') === 'fitness' ? 'selected' : '' ?>>💪 Fitness / Bodybuilding</option>
              <option value="low_carb" <?= ($analysisData['condition'] ?? '') === 'low_carb' ? 'selected' : '' ?>>🥦 Low Carb Diet</option>
            </select>
          </div>
          
          <div class="form-group mt-3">
            <label>Daily Calorie Goal (kcal)</label>
            <input type="number" name="daily_calories" class="form-control-fe" 
                   value="<?= $analysisData['calories'] ?? $userProfile['daily_calories'] ?? 2000 ?>" 
                   placeholder="e.g. 1800" min="800" max="5000">
            <small class="text-muted">Average adult needs 1800-2500 kcal/day</small>
          </div>
          
          <button type="submit" class="btn-primary-fe w-100 justify-content-center mt-4">
            🧠 Get My Meal Plan
          </button>
        </form>
        
        <!-- DIET TIPS -->
        <div class="mt-4 p-3 rounded" style="background:rgba(46,196,182,0.08);border:1px solid rgba(46,196,182,0.2)">
          <div style="color:#2ec4b6;font-weight:700;font-size:0.85rem;margin-bottom:0.5rem">💡 Health Tips</div>
          <ul style="padding-left:1rem;margin:0;font-size:0.82rem;color:var(--text-muted)">
            <li>PCOS: Prefer low-GI foods</li>
            <li>Diabetes: Avoid high-sugar meals</li>
            <li>Fitness: Aim 1.6g protein/kg</li>
            <li>Hydrate: 8+ glasses daily</li>
          </ul>
        </div>
      </div>
    </div>
    
    <!-- RESULTS -->
    <div class="col-lg-8">
      <?php if ($analysisData): ?>
        <!-- ANALYSIS SUMMARY -->
        <div class="card-fe p-4 mb-4" style="background:linear-gradient(135deg,#1a1a2e,#16213e);color:white">
          <div class="ai-badge mb-2" style="display:inline-flex">Analysis Complete</div>
          <h4 style="font-family:'Playfair Display',serif;margin-bottom:1rem">Your Personalized Meal Plan</h4>
          <div class="row g-3">
            <div class="col-4 text-center">
              <div style="font-size:2rem"><?= $analysisData['goal'] === 'lose' ? '🔻' : ($analysisData['goal'] === 'gain' ? '📈' : '⚖️') ?></div>
              <div style="font-size:0.8rem;color:rgba(255,255,255,0.6);text-transform:uppercase;letter-spacing:0.5px">Goal</div>
              <div style="font-weight:700;font-size:0.9rem"><?= ucfirst($analysisData['goal']) ?> Weight</div>
            </div>
            <div class="col-4 text-center">
              <div style="font-size:2rem">🏥</div>
              <div style="font-size:0.8rem;color:rgba(255,255,255,0.6);text-transform:uppercase;letter-spacing:0.5px">Condition</div>
              <div style="font-weight:700;font-size:0.9rem"><?= ucfirst(str_replace('_', ' ', $analysisData['condition'])) ?></div>
            </div>
            <div class="col-4 text-center">
              <div style="font-size:2rem">🔥</div>
              <div style="font-size:0.8rem;color:rgba(255,255,255,0.6);text-transform:uppercase;letter-spacing:0.5px">Daily Target</div>
              <div style="font-weight:700;font-size:0.9rem"><?= $analysisData['calories'] ?> kcal</div>
            </div>
          </div>
        </div>
        
        <h5 class="fw-700 mb-3">
          Recommended Meals for You 
          <span class="text-muted fw-normal small">(<?= count($recommendations) ?> options)</span>
        </h5>
        
        <?php if (empty($recommendations)): ?>
        <div class="card-fe p-4 text-center">
          <div style="font-size:4rem">😕</div>
          <h5 class="mt-3">No perfect match yet</h5>
          <p class="text-muted">Our restaurants are expanding. Try adjusting your criteria.</p>
        </div>
        <?php else: ?>
        <div class="row g-3">
          <?php foreach ($recommendations as $food): ?>
          <div class="col-md-6">
            <div class="card-fe p-3">
              <div class="d-flex gap-3">
                <div style="background:linear-gradient(135deg,#e8f5e9,#c8e6c9);width:80px;height:75px;border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;font-size:2.2rem;flex-shrink:0">
                  <?= $food['category'] === 'Bowls' ? '🥗' : ($food['category'] === 'Salads' ? '🥙' : '🍛') ?>
                </div>
                <div class="flex-grow-1">
                  <div class="d-flex align-items-center gap-1 mb-1">
                    <div class="<?= $food['is_veg'] ? 'veg-dot' : 'nonveg-dot' ?>"></div>
                    <strong style="font-size:0.92rem"><?= htmlspecialchars($food['name']) ?></strong>
                  </div>
                  <div style="font-size:0.78rem;color:var(--text-muted)"><?= htmlspecialchars($food['restaurant_name']) ?></div>
                  <!-- Nutrition Bar -->
                  <div class="d-flex gap-2 mt-2">
                    <?php if ($food['calories']): ?>
                    <span style="background:#fff8e1;color:#e65100;padding:0.1rem 0.4rem;border-radius:4px;font-size:0.7rem;font-weight:700">🔥 <?= $food['calories'] ?>cal</span>
                    <?php endif; ?>
                    <?php if ($food['protein']): ?>
                    <span style="background:#e8eaf6;color:#1a237e;padding:0.1rem 0.4rem;border-radius:4px;font-size:0.7rem;font-weight:700">💪 <?= $food['protein'] ?>g protein</span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              <div class="d-flex align-items-center justify-content-between mt-3 pt-2" style="border-top:1px solid var(--silver-light)">
                <div>
                  <strong>₹<?= number_format($food['price'], 0) ?></strong>
                  <?php if ($food['health_tags']): ?>
                  <div style="font-size:0.7rem;color:#2e7d32;margin-top:0.2rem">✅ <?= implode(', ', array_slice(explode(',', $food['health_tags']), 0, 2)) ?></div>
                  <?php endif; ?>
                </div>
                <button class="btn-add-cart" onclick="addToCart(<?= $food['id'] ?>, '<?= addslashes($food['name']) ?>', <?= $food['price'] ?>)">+ Add to Cart</button>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
      <?php else: ?>
      <!-- Welcome State -->
      <div class="card-fe p-5 text-center">
        <div style="font-size:6rem;margin-bottom:1.5rem">🥗</div>
        <h3 style="font-family:'Playfair Display',serif">Your Personal Nutritionist</h3>
        <p class="text-muted mt-2" style="max-width:400px;margin:0 auto">Fill in your health profile on the left and we'll suggest the best meals matching your goals and dietary requirements.</p>
        <div class="row g-3 mt-4 text-start">
          <?php
          $examples = [
            ['🌸', 'PCOS-Friendly', 'Low-GI, anti-inflammatory meals to manage hormonal balance'],
            ['🩸', 'Diabetic-Safe', 'Low-sugar, high-fiber options to keep blood sugar stable'],
            ['💪', 'High Protein', 'Muscle-building meals with 20g+ protein per serving'],
            ['🔻', 'Weight Loss', 'Filling, low-calorie meals under 450 kcal'],
          ];
          foreach ($examples as [$icon, $title, $desc]):
          ?>
          <div class="col-md-6">
            <div style="background:var(--silver-bg);border-radius:var(--radius-sm);padding:1rem">
              <div style="font-size:1.5rem;margin-bottom:0.5rem"><?= $icon ?></div>
              <strong style="font-size:0.9rem"><?= $title ?></strong>
              <p style="font-size:0.82rem;color:var(--text-muted);margin:0.3rem 0 0"><?= $desc ?></p>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
