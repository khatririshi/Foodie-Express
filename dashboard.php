<?php
// dashboard.php
$pageTitle = 'Dashboard';
require_once 'includes/header.php';
requireLogin();

$db  = getDB();
$uid = $_SESSION['user_id'];
$user   = $db->query("SELECT * FROM users WHERE id=$uid")->fetch_assoc();
$orders = $db->query("SELECT COUNT(*) FROM orders WHERE user_id=$uid")->fetch_row()[0];
$spent  = $db->query("SELECT COALESCE(SUM(final_amount),0) FROM orders WHERE user_id=$uid AND order_status='delivered'")->fetch_row()[0];
$recentOrders = $db->query("SELECT o.*, COUNT(oi.id) as items FROM orders o LEFT JOIN order_items oi ON o.id=oi.order_id WHERE o.user_id=$uid GROUP BY o.id ORDER BY o.created_at DESC LIMIT 3")->fetch_all(MYSQLI_ASSOC);
$db->close();
?>
<meta name="site-url" content="<?= SITE_URL ?>">
<div class="container py-5">
  <div class="row g-4">
    <!-- Profile Card -->
    <div class="col-lg-3">
      <div class="card-fe p-4 text-center">
        <div style="width:80px;height:80px;border-radius:50%;background:var(--gradient);display:flex;align-items:center;justify-content:center;font-size:2rem;color:white;font-weight:800;margin:0 auto 1rem">
          <?= strtoupper($user['name'][0]) ?>
        </div>
        <h5 style="font-family:'Playfair Display',serif;margin-bottom:0.3rem"><?= htmlspecialchars($user['name']) ?></h5>
        <div style="font-size:0.85rem;color:var(--text-muted);margin-bottom:1.5rem"><?= htmlspecialchars($user['email']) ?></div>
        <div class="row g-2 text-center mb-3">
          <div class="col-6">
            <div style="font-family:'Playfair Display',serif;font-size:1.5rem;font-weight:800"><?= $orders ?></div>
            <div style="font-size:0.75rem;color:var(--text-muted)">Orders</div>
          </div>
          <div class="col-6">
            <div style="font-family:'Playfair Display',serif;font-size:1.5rem;font-weight:800">₹<?= number_format($spent, 0) ?></div>
            <div style="font-size:0.75rem;color:var(--text-muted)">Spent</div>
          </div>
        </div>
        <a href="<?= SITE_URL ?>/profile.php" class="btn-add-cart w-100">Edit Profile</a>
      </div>
      
      <!-- Quick Links -->
      <div class="card-fe p-3 mt-3">
        <div class="d-flex flex-column gap-1">
          <?php foreach ([['orders.php','bi-bag','My Orders'],['ai-diet.php','bi-heart-pulse','AI Diet Plan'],['group-order.php','bi-people','Group Order'],['emergency.php','bi-lightning','Emergency Mode'],['feedback.php','bi-chat','Feedback']] as [$url,$icon,$label]): ?>
          <a href="<?= SITE_URL ?>/<?= $url ?>" class="sidebar-link rounded">
            <i class="bi <?= $icon ?>"></i> <?= $label ?>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    
    <!-- Main Content -->
    <div class="col-lg-9">
      <h3 style="font-family:'Playfair Display',serif;margin-bottom:1.5rem">Welcome back, <?= htmlspecialchars(explode(' ', $user['name'])[0]) ?>! 👋</h3>
      
      <!-- Quick Action Cards -->
      <div class="row g-3 mb-4">
        <?php foreach ([['🍽️','Browse Menu','Find your next meal','menu.php','var(--gradient)'],['🧠','AI Diet Plan','Smart meal suggestions','ai-diet.php','linear-gradient(135deg,#1a1a2e,#16213e)'],['🎲','Surprise Me!','Can\'t decide? Let us choose','surprise.php','linear-gradient(135deg,#ff9f1c,#ffbf69)'],['🚨','Emergency','Find open restaurants','emergency.php','linear-gradient(135deg,#1a1a2e,#2d2d44)']] as [$emoji,$title,$desc,$url,$bg]): ?>
        <div class="col-md-3">
          <a href="<?= SITE_URL ?>/<?= $url ?>" class="d-block text-decoration-none text-center p-3 rounded" style="background:<?= $bg ?>;color:white;transition:all 0.2s" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='none'">
            <div style="font-size:2.5rem;margin-bottom:0.5rem"><?= $emoji ?></div>
            <div style="font-weight:700;font-size:0.9rem"><?= $title ?></div>
            <div style="font-size:0.75rem;opacity:0.75;margin-top:0.2rem"><?= $desc ?></div>
          </a>
        </div>
        <?php endforeach; ?>
      </div>
      
      <!-- Recent Orders -->
      <div class="card-fe p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 style="font-family:'Playfair Display',serif;margin:0">Recent Orders</h5>
          <a href="<?= SITE_URL ?>/orders.php" style="color:var(--primary);font-size:0.85rem;font-weight:600;text-decoration:none">View All →</a>
        </div>
        <?php if (empty($recentOrders)): ?>
        <div class="text-center py-4">
          <div style="font-size:3rem">🛍️</div>
          <p class="text-muted mt-2">No orders yet. Start exploring!</p>
          <a href="<?= SITE_URL ?>/menu.php" class="btn-primary-fe mt-2">Order Now</a>
        </div>
        <?php else: ?>
        <?php foreach ($recentOrders as $o):
          $colors = ['placed'=>'#1565c0','confirmed'=>'#6a1b9a','preparing'=>'#e65100','out_for_delivery'=>'#2e7d32','delivered'=>'#2e7d32','cancelled'=>'#c62828'];
          $bgs    = ['placed'=>'#e3f2fd','confirmed'=>'#f3e5f5','preparing'=>'#fff3e0','out_for_delivery'=>'#e8f5e9','delivered'=>'#e8f5e9','cancelled'=>'#fce4ec'];
        ?>
        <div class="d-flex justify-content-between align-items-center p-3 rounded mb-2" style="background:var(--silver-bg)">
          <div>
            <strong style="font-size:0.92rem">Order #<?= $o['id'] ?></strong>
            <div style="font-size:0.78rem;color:var(--text-muted)"><?= $o['items'] ?> item(s) · <?= date('d M Y', strtotime($o['created_at'])) ?></div>
          </div>
          <div class="d-flex align-items-center gap-2">
            <span style="background:<?= $bgs[$o['order_status']] ?? '#f5f5f5' ?>;color:<?= $colors[$o['order_status']] ?? '#333' ?>;padding:0.15rem 0.7rem;border-radius:50px;font-size:0.72rem;font-weight:700">
              <?= ucwords(str_replace('_',' ',$o['order_status'])) ?>
            </span>
            <strong>₹<?= number_format($o['final_amount'],2) ?></strong>
          </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php require_once 'includes/footer.php'; ?>
