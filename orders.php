<?php
$pageTitle = 'My Orders';
require_once 'includes/header.php';
requireLogin();

$db  = getDB();
$uid = $_SESSION['user_id'];
$orders = $db->query("SELECT o.*, COUNT(oi.id) as item_count FROM orders o LEFT JOIN order_items oi ON o.id=oi.order_id WHERE o.user_id=$uid GROUP BY o.id ORDER BY o.created_at DESC")->fetch_all(MYSQLI_ASSOC);

$trackOrder = null;
if (isset($_GET['new']) || isset($_GET['track'])) {
    $oid = intval($_GET['new'] ?? $_GET['track']);
    $trackOrder = $db->query("SELECT o.*, GROUP_CONCAT(CONCAT(f.name, ' x', oi.quantity) SEPARATOR ', ') as items_list FROM orders o JOIN order_items oi ON o.id=oi.order_id JOIN food_items f ON oi.food_id=f.id WHERE o.id=$oid AND o.user_id=$uid GROUP BY o.id")->fetch_assoc();
}
$db->close();

$statusSteps = ['placed'=>0,'confirmed'=>1,'preparing'=>2,'out_for_delivery'=>3,'delivered'=>4];
?>
<meta name="site-url" content="<?= SITE_URL ?>">

<div class="container py-5">
  <h2 style="font-family:'Playfair Display',serif;margin-bottom:2rem">🛍️ My Orders</h2>
  
  <?php if ($trackOrder): ?>
  <!-- ORDER TRACKING -->
  <div class="card-fe p-4 mb-4" style="background:linear-gradient(135deg,#1a1a2e,#16213e);color:white">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
      <div>
        <div style="font-size:0.8rem;color:rgba(255,255,255,0.6);text-transform:uppercase;letter-spacing:1px">Order Placed</div>
        <h4 style="font-family:'Playfair Display',serif;margin:0.2rem 0">Order #<?= $trackOrder['id'] ?></h4>
        <div style="color:rgba(255,255,255,0.7);font-size:0.9rem"><?= $trackOrder['items_list'] ?></div>
      </div>
      <div class="text-end">
        <div style="font-family:'Playfair Display',serif;font-size:1.5rem;font-weight:800">₹<?= number_format($trackOrder['final_amount'], 2) ?></div>
        <div style="color:rgba(255,255,255,0.6);font-size:0.82rem"><?= date('d M Y, h:i A', strtotime($trackOrder['created_at'])) ?></div>
      </div>
    </div>
    
    <!-- Tracking Steps -->
    <div class="tracking-steps">
      <?php
      $currentStep = $statusSteps[$trackOrder['order_status']] ?? 0;
      $steps = [['placed','🍽️','Order Placed'],['confirmed','✅','Confirmed'],['preparing','👨‍🍳','Preparing'],['out_for_delivery','🛵','On the Way'],['delivered','🏠','Delivered']];
      foreach ($steps as [$key, $icon, $label]):
        $stepNum = $statusSteps[$key];
        $class = $stepNum < $currentStep ? 'done' : ($stepNum === $currentStep ? 'active' : '');
      ?>
      <div class="track-step <?= $class ?>">
        <div class="track-step-icon"><?= $icon ?></div>
        <p><?= $label ?></p>
      </div>
      <?php endforeach; ?>
    </div>
    
    <?php if ($trackOrder['order_status'] !== 'delivered' && $trackOrder['order_status'] !== 'cancelled'): ?>
    <div class="text-center mt-2" style="color:rgba(255,255,255,0.7);font-size:0.9rem">
      🕐 Estimated delivery in ~<?= $trackOrder['estimated_delivery'] ?> minutes
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>
  
  <!-- ALL ORDERS -->
  <?php if (empty($orders)): ?>
  <div class="card-fe p-5 text-center">
    <div style="font-size:5rem">🛍️</div>
    <h4 class="mt-3">No orders yet</h4>
    <p class="text-muted">You haven't placed any orders yet. Start exploring our menu!</p>
    <a href="<?= SITE_URL ?>/menu.php" class="btn-primary-fe mt-3">Browse Menu</a>
  </div>
  <?php else: ?>
  <div class="row g-3">
    <?php foreach ($orders as $order):
      $statusColors = ['placed'=>'#1565c0','confirmed'=>'#6a1b9a','preparing'=>'#e65100','out_for_delivery'=>'#2e7d32','delivered'=>'#2e7d32','cancelled'=>'#c62828'];
      $statusBg = ['placed'=>'#e3f2fd','confirmed'=>'#f3e5f5','preparing'=>'#fff3e0','out_for_delivery'=>'#e8f5e9','delivered'=>'#e8f5e9','cancelled'=>'#fce4ec'];
    ?>
    <div class="col-12">
      <div class="card-fe p-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
          <div>
            <div class="d-flex align-items-center gap-2 mb-1">
              <strong>Order #<?= $order['id'] ?></strong>
              <span style="background:<?= $statusBg[$order['order_status']] ?? '#f5f5f5' ?>;color:<?= $statusColors[$order['order_status']] ?? '#333' ?>;padding:0.15rem 0.7rem;border-radius:50px;font-size:0.75rem;font-weight:700">
                <?= ucwords(str_replace('_',' ',$order['order_status'])) ?>
              </span>
            </div>
            <div style="font-size:0.85rem;color:var(--text-muted)"><?= $order['item_count'] ?> item(s) · <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></div>
            <div style="font-size:0.82rem;color:var(--text-muted);margin-top:0.3rem">📍 <?= htmlspecialchars(substr($order['delivery_address'], 0, 60)) ?>...</div>
          </div>
          <div class="text-end">
            <div style="font-family:'Playfair Display',serif;font-size:1.3rem;font-weight:800">₹<?= number_format($order['final_amount'], 2) ?></div>
            <div style="font-size:0.8rem;color:var(--text-muted)"><?= ucfirst($order['payment_method']) ?></div>
            <a href="?track=<?= $order['id'] ?>" class="btn-add-cart mt-2">Track Order</a>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
