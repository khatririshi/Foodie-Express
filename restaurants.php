<?php
$pageTitle = 'Restaurants';
require_once 'includes/header.php';

$db = getDB();
$filter   = sanitize($_GET['filter'] ?? 'all');
$search   = sanitize($_GET['search'] ?? '');

$where = ['1=1'];
if ($search) {
    $s = "%$search%";
    $where[] = "(name LIKE '$s' OR cuisine_type LIKE '$s' OR description LIKE '$s')";
}
if ($filter === 'open')      $where[] = "is_open=1";
if ($filter === 'latenight') $where[] = "is_late_night=1";
if ($filter === 'fast')      $where[] = "delivery_time <= 25";

$whereSQL = implode(' AND ', $where);
$restaurants = $db->query("SELECT r.*, COUNT(f.id) as food_count FROM restaurants r LEFT JOIN food_items f ON r.id=f.restaurant_id WHERE $whereSQL GROUP BY r.id ORDER BY r.rating DESC")->fetch_all(MYSQLI_ASSOC);
$db->close();
?>
<meta name="site-url" content="<?= SITE_URL ?>">

<div style="background:linear-gradient(135deg,#1a1a2e,#16213e);padding:3rem 0;text-align:center">
  <div class="container">
    <h1 style="color:white;font-family:'Playfair Display',serif;font-size:2.5rem">🏪 All Restaurants</h1>
    <p style="color:rgba(255,255,255,0.7)">Discover the best restaurants near you</p>
  </div>
</div>

<div class="search-section">
  <div class="container">
    <div class="search-wrap">
      <i class="bi bi-search search-icon"></i>
      <input type="text" id="globalSearch" value="<?= htmlspecialchars($search) ?>" placeholder="Search restaurants, cuisines..." oninput="filterRestaurants(this.value)">
      <button class="btn-search" onclick="performSearch()">Search</button>
    </div>
    <div class="filter-chips mt-3">
      <?php foreach (['all'=>'🍽️ All','open'=>'✅ Open Now','fast'=>'⚡ Fast Delivery','latenight'=>'🌙 Late Night'] as $k=>$v): ?>
      <a href="?filter=<?= $k ?>" class="chip <?= $filter===$k?'active':'' ?>"><?= $v ?></a>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<div class="container section-pad">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-700 mb-0"><?= count($restaurants) ?> Restaurants Found</h5>
  </div>
  
  <?php if (empty($restaurants)): ?>
  <div class="text-center py-5">
    <div style="font-size:5rem">🏪</div>
    <h4 class="mt-3">No restaurants found</h4>
    <a href="<?= SITE_URL ?>/restaurants.php" class="btn-primary-fe mt-3">View All</a>
  </div>
  <?php else: ?>
  <div class="row g-4" id="restaurantGrid">
    <?php foreach ($restaurants as $r): ?>
    <div class="col-md-6 col-lg-4">
      <div class="card-fe restaurant-card">
        <div style="position:relative">
          <div style="background:linear-gradient(135deg,#1a1a2e,#16213e);height:200px;display:flex;align-items:center;justify-content:center;font-size:6rem">
            <?= match($r['cuisine_type']) {
              'Italian' => '🍕', 'Fast Food' => '🍔',
              'Healthy' => '🥗', 'Multi-Cuisine' => '🍱', default => '🍛'
            } ?>
          </div>
          <span class="position-absolute top-0 start-0 m-2 <?= $r['is_open']?'open-badge':'closed-badge' ?>">
            <?= $r['is_open']?'● Open':'● Closed' ?>
          </span>
          <?php if ($r['is_late_night']): ?>
          <span class="position-absolute top-0 end-0 m-2" style="background:rgba(230,57,70,0.9);color:white;padding:0.2rem 0.6rem;border-radius:50px;font-size:0.72rem;font-weight:700">🌙 Late Night</span>
          <?php endif; ?>
          <?php if ($r['delivery_time'] <= 25): ?>
          <span class="position-absolute bottom-0 end-0 m-2" style="background:rgba(46,196,182,0.9);color:white;padding:0.2rem 0.6rem;border-radius:50px;font-size:0.72rem;font-weight:700">⚡ Fast Delivery</span>
          <?php endif; ?>
        </div>
        <div class="card-body">
          <div class="restaurant-name"><?= htmlspecialchars($r['name']) ?></div>
          <div class="cuisine-tag"><?= htmlspecialchars($r['cuisine_type']) ?> · <?= $r['food_count'] ?> items</div>
          <?php if ($r['description']): ?>
          <div style="font-size:0.82rem;color:var(--text-muted);margin-bottom:0.75rem;line-height:1.4"><?= htmlspecialchars(substr($r['description'],0,70)) ?>...</div>
          <?php endif; ?>
          <div class="meta-row mb-3">
            <span class="rating-badge">⭐ <?= $r['rating'] ?></span>
            <span class="meta-item"><i class="bi bi-clock"></i> <?= $r['delivery_time'] ?> mins</span>
            <span class="meta-item"><i class="bi bi-bag"></i> ₹<?= number_format($r['min_order'],0) ?> min</span>
          </div>
          <a href="<?= SITE_URL ?>/menu.php?restaurant=<?= $r['id'] ?>" class="btn-primary-fe w-100 justify-content-center">
            View Menu <i class="bi bi-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
