<?php
$pageTitle = 'Emergency Food Mode';
require_once 'includes/header.php';

$db = getDB();
$hour = (int)date('H');
// Late night = 10pm to 6am
$lateNight = ($hour >= 22 || $hour < 6);

// Get open restaurants
$lateRestaurants = $db->query("SELECT r.*, 
    COUNT(f.id) as item_count
    FROM restaurants r 
    LEFT JOIN food_items f ON r.id = f.restaurant_id AND f.is_available=1
    WHERE r.is_open=1 
    GROUP BY r.id 
    ORDER BY r.delivery_time ASC, r.rating DESC")->fetch_all(MYSQLI_ASSOC);

$db->close();
?>
<meta name="site-url" content="<?= SITE_URL ?>">

<div class="emergency-section" style="border-radius:0;padding:4rem 0;text-align:center">
  <div class="container">
    <div class="emergency-badge">🚨 EMERGENCY FOOD MODE</div>
    <h1 style="color:white;font-family:'Playfair Display',serif;font-size:3rem;margin:1rem 0 0.5rem">
      <?= $lateNight ? 'Late Night Hunger?' : 'Need Food Fast?' ?>
    </h1>
    <p style="color:rgba(255,255,255,0.7);font-size:1.1rem;margin-bottom:2rem">
      <?= $lateNight ? "It's <?= date('h:i A') ?>. These restaurants are open for you right now!" : "These restaurants have the fastest delivery times right now." ?>
    </p>
    <div class="d-flex justify-content-center gap-4">
      <div style="text-align:center">
        <div style="color:white;font-family:'Playfair Display',serif;font-size:2.5rem;font-weight:800"><?= count($lateRestaurants) ?></div>
        <div style="color:rgba(255,255,255,0.6);font-size:0.8rem;text-transform:uppercase">Open Now</div>
      </div>
      <div style="text-align:center">
        <div style="color:white;font-family:'Playfair Display',serif;font-size:2.5rem;font-weight:800">
          <?= $lateRestaurants ? min(array_column($lateRestaurants, 'delivery_time')) : '?' ?> min
        </div>
        <div style="color:rgba(255,255,255,0.6);font-size:0.8rem;text-transform:uppercase">Fastest Delivery</div>
      </div>
    </div>
  </div>
</div>

<div class="container py-5">
  <?php if (empty($lateRestaurants)): ?>
  <div class="text-center py-5">
    <div style="font-size:5rem">😴</div>
    <h4 class="mt-3">All restaurants are currently closed</h4>
    <p class="text-muted">Please check back during operating hours. Most restaurants open from 8 AM.</p>
  </div>
  <?php else: ?>
  <div class="row g-4">
    <?php foreach ($lateRestaurants as $r): ?>
    <div class="col-md-6 col-lg-4">
      <div class="card-fe" style="border:2px solid <?= $r['delivery_time'] <= 25 ? 'rgba(46,196,182,0.3)' : 'transparent' ?>">
        <div style="background:linear-gradient(135deg,#1a1a2e,#2d2d44);height:160px;display:flex;align-items:center;justify-content:center;font-size:5rem;position:relative">
          <?= in_array($r['cuisine_type'], ['Italian']) ? '🍕' : (in_array($r['cuisine_type'], ['Fast Food']) ? '🍔' : (in_array($r['cuisine_type'], ['Healthy']) ? '🥗' : '🍛')) ?>
          <?php if ($r['delivery_time'] <= 25): ?>
          <div style="position:absolute;top:10px;right:10px;background:rgba(46,196,182,0.9);color:white;padding:0.2rem 0.6rem;border-radius:50px;font-size:0.75rem;font-weight:700">
            ⚡ FAST
          </div>
          <?php endif; ?>
        </div>
        <div class="p-3">
          <div style="font-family:'Playfair Display',serif;font-size:1.1rem;font-weight:700;margin-bottom:0.3rem"><?= htmlspecialchars($r['name']) ?></div>
          <div style="font-size:0.82rem;color:var(--text-muted);margin-bottom:1rem"><?= htmlspecialchars($r['cuisine_type']) ?> · <?= $r['item_count'] ?> items</div>
          <div class="d-flex align-items-center gap-3 mb-3">
            <span class="rating-badge">⭐ <?= $r['rating'] ?></span>
            <span class="meta-item" style="font-size:0.85rem">
              <i class="bi bi-clock"></i> <?= $r['delivery_time'] ?> mins
            </span>
            <span class="meta-item" style="font-size:0.85rem">
              ₹<?= number_format($r['delivery_fee'], 0) ?> delivery
            </span>
          </div>
          <div class="d-flex align-items-center justify-content-between">
            <span class="open-badge">● Open Until <?= date('h:i A', strtotime($r['closes_at'])) ?></span>
            <a href="<?= SITE_URL ?>/menu.php?restaurant=<?= $r['id'] ?>" class="btn-primary-fe" style="padding:0.4rem 1rem;font-size:0.85rem">
              Order Now
            </a>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
