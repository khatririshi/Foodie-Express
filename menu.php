<?php
$pageTitle = 'Menu';
require_once 'includes/header.php';

$db = getDB();
$search     = sanitize($_GET['search'] ?? '');
$filter     = sanitize($_GET['filter'] ?? 'all');
$restaurant = intval($_GET['restaurant'] ?? 0);
$sortBy     = sanitize($_GET['sort'] ?? 'popular');

// Build query
$where = ["f.is_available=1"];
$params = [];
$types  = '';

if ($search) {
    $where[] = "(f.name LIKE ? OR f.description LIKE ? OR r.name LIKE ?)";
    $s = "%$search%";
    $params = array_merge($params, [$s, $s, $s]);
    $types .= 'sss';
}
if ($filter === 'veg')     { $where[] = "f.is_veg=1"; }
if ($filter === 'nonveg')  { $where[] = "f.is_veg=0"; }
if ($filter === 'healthy') { $where[] = "f.is_healthy=1"; }
if ($filter === 'under200'){ $where[] = "f.price <= 200"; }
if ($restaurant > 0) {
    $where[] = "f.restaurant_id=?";
    $params[] = $restaurant;
    $types .= 'i';
}

$orderClause = match($sortBy) {
    'price_low'  => 'f.price ASC',
    'price_high' => 'f.price DESC',
    'rating'     => 'f.rating DESC',
    default      => 'f.rating DESC'
};

$whereSQL = implode(' AND ', $where);
$sql = "SELECT f.*, r.name as restaurant_name, r.delivery_time, r.is_open FROM food_items f 
        JOIN restaurants r ON f.restaurant_id=r.id 
        WHERE $whereSQL ORDER BY $orderClause";

if ($params) {
    $stmt = $db->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $foods = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $foods = $db->query($sql)->fetch_all(MYSQLI_ASSOC);
}

// Get all restaurants for sidebar
$restaurants = $db->query("SELECT id, name, cuisine_type FROM restaurants ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$currentRestaurant = $restaurant > 0 ? $db->query("SELECT * FROM restaurants WHERE id=$restaurant")->fetch_assoc() : null;
$db->close();
?>
<meta name="site-url" content="<?= SITE_URL ?>">

<div class="container-fluid py-4" style="max-width:1300px">
  <div class="row g-4">
    
    <!-- SIDEBAR FILTERS -->
    <div class="col-lg-3">
      <div style="position:sticky;top:80px">
        <!-- Search Box -->
        <div class="card-fe p-3 mb-3">
          <div class="search-wrap" style="max-width:100%">
            <i class="bi bi-search search-icon"></i>
            <input type="text" id="globalSearch" value="<?= htmlspecialchars($search) ?>" placeholder="Search dishes..." style="padding-left:3rem">
            <button class="btn-search" onclick="performSearch()">Go</button>
          </div>
        </div>
        
        <!-- Filters -->
        <div class="card-fe p-3 mb-3">
          <h6 class="fw-700 mb-3">Filter</h6>
          <?php
          $filters = [
            'all'     => ['🍽️', 'All Items'],
            'veg'     => ['🟢', 'Veg Only'],
            'nonveg'  => ['🔴', 'Non-Veg Only'],
            'healthy' => ['🥗', 'Healthy'],
            'under200'=> ['💰', 'Under ₹200'],
          ];
          foreach ($filters as $key => [$icon, $label]):
          ?>
          <a href="?filter=<?= $key ?><?= $restaurant ? '&restaurant='.$restaurant : '' ?>" 
             class="d-flex align-items-center gap-2 p-2 rounded mb-1 text-decoration-none <?= $filter===$key ? 'bg-danger text-white' : 'text-muted' ?>"
             style="transition:all 0.2s">
            <span><?= $icon ?></span> <?= $label ?>
          </a>
          <?php endforeach; ?>
        </div>
        
        <!-- Restaurants -->
        <div class="card-fe p-3 mb-3">
          <h6 class="fw-700 mb-3">Restaurants</h6>
          <a href="?filter=<?= $filter ?>" class="d-flex align-items-center gap-2 p-2 rounded mb-1 text-decoration-none <?= !$restaurant ? 'bg-danger text-white' : 'text-muted' ?>" style="transition:all 0.2s">
            🏪 All Restaurants
          </a>
          <?php foreach ($restaurants as $rest): ?>
          <a href="?restaurant=<?= $rest['id'] ?>&filter=<?= $filter ?>" 
             class="d-flex align-items-center gap-2 p-2 rounded mb-1 text-decoration-none <?= $restaurant===$rest['id'] ? 'bg-danger text-white' : 'text-muted' ?>"
             style="transition:all 0.2s;font-size:0.88rem">
            🍴 <?= htmlspecialchars($rest['name']) ?>
          </a>
          <?php endforeach; ?>
        </div>
        
        <!-- Sort -->
        <div class="card-fe p-3">
          <h6 class="fw-700 mb-3">Sort By</h6>
          <?php foreach (['popular'=>'⭐ Most Popular','price_low'=>'💰 Price: Low to High','price_high'=>'💎 Price: High to Low','rating'=>'🏆 Top Rated'] as $val => $label): ?>
          <a href="?sort=<?= $val ?>&filter=<?= $filter ?><?= $search ? '&search='.$search : '' ?><?= $restaurant ? '&restaurant='.$restaurant : '' ?>"
             class="d-flex align-items-center gap-2 p-2 rounded mb-1 text-decoration-none <?= $sortBy===$val ? 'bg-danger text-white' : 'text-muted' ?>"
             style="transition:all 0.2s;font-size:0.88rem">
            <?= $label ?>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    
    <!-- FOOD ITEMS -->
    <div class="col-lg-9">
      <?php if ($currentRestaurant): ?>
      <div class="card-fe p-4 mb-4" style="background:linear-gradient(135deg,#1a1a2e,#16213e);color:white">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h2 style="font-family:'Playfair Display',serif;margin-bottom:0.3rem"><?= htmlspecialchars($currentRestaurant['name']) ?></h2>
            <p style="color:rgba(255,255,255,0.7);margin:0"><?= htmlspecialchars($currentRestaurant['cuisine_type']) ?> · ⭐ <?= $currentRestaurant['rating'] ?> · 🕐 <?= $currentRestaurant['delivery_time'] ?> mins</p>
          </div>
          <div class="<?= $currentRestaurant['is_open'] ? 'open-badge' : 'closed-badge' ?>"><?= $currentRestaurant['is_open'] ? '● Open Now' : '● Closed' ?></div>
        </div>
      </div>
      <?php endif; ?>
      
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-700 mb-0">
          <?= $search ? "Results for \"$search\"" : ($filter !== 'all' ? ucwords(str_replace('_', ' ', $filter)) . ' Items' : 'All Dishes') ?>
          <span class="text-muted fw-normal small ms-2">(<?= count($foods) ?> items)</span>
        </h5>
      </div>
      
      <?php if (empty($foods)): ?>
      <div class="text-center py-5">
        <div style="font-size:5rem">🍽️</div>
        <h4 class="mt-3">No dishes found</h4>
        <p class="text-muted">Try adjusting your filters or search term</p>
        <a href="<?= SITE_URL ?>/menu.php" class="btn-primary-fe mt-2">View All Dishes</a>
      </div>
      <?php else: ?>
      <div class="row g-3" id="foodGrid">
        <?php foreach ($foods as $food): ?>
        <div class="col-12 col-md-6">
          <div class="food-card card-fe" style="flex-direction:row;margin-bottom:0">
            <div style="background:linear-gradient(135deg,<?= $food['is_veg'] ? '#e8f5e9,#c8e6c9' : '#fce4ec,#f8bbd0' ?>);width:110px;height:110px;border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;font-size:3rem;flex-shrink:0">
              <?= match($food['category']) {
                'Pizza' => '🍕', 'Burgers' => '🍔', 'Bowls' => '🥗',
                'Salads' => '🥙', 'Snacks' => '🥪', default => '🍛'
              } ?>
            </div>
            <div class="food-info" style="flex:1;padding:0 1rem">
              <div class="d-flex align-items-start justify-content-between">
                <div>
                  <div class="d-flex align-items-center gap-2 mb-1">
                    <div class="<?= $food['is_veg'] ? 'veg-dot' : 'nonveg-dot' ?>"></div>
                    <div class="food-name"><?= htmlspecialchars($food['name']) ?></div>
                  </div>
                  <div class="food-desc" style="font-size:0.8rem"><?= htmlspecialchars(substr($food['description'], 0, 60)) ?>...</div>
                  <div class="d-flex align-items-center gap-2 mt-1">
                    <span style="background:#fff8e1;color:#f57f17;padding:0.15rem 0.5rem;border-radius:4px;font-size:0.75rem;font-weight:700">⭐ <?= $food['rating'] ?></span>
                    <span class="text-muted" style="font-size:0.75rem"><?= $food['restaurant_name'] ?></span>
                    <?php if ($food['is_healthy']): ?>
                    <span style="background:#e8f5e9;color:#2e7d32;padding:0.15rem 0.5rem;border-radius:4px;font-size:0.72rem;font-weight:700">🌿 Healthy</span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              <div class="d-flex align-items-center justify-content-between mt-2">
                <div>
                  <span class="food-price">₹<?= number_format($food['price'], 0) ?></span>
                  <?php if ($food['calories']): ?>
                  <span class="text-muted ms-2" style="font-size:0.75rem"><?= $food['calories'] ?> cal</span>
                  <?php endif; ?>
                </div>
                <button class="btn-add-cart" onclick="addToCart(<?= $food['id'] ?>, '<?= addslashes($food['name']) ?>', <?= $food['price'] ?>)">+ Add</button>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
