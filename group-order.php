<?php
$pageTitle = 'Group Order';
require_once 'includes/header.php';
if (!isLoggedIn()) {
    setFlash('info', 'Please login to use Group Ordering');
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

$db = getDB();
$uid = $_SESSION['user_id'];

// Handle create group
if (isset($_POST['create_group'])) {
    $code = strtoupper(substr(md5(uniqid()), 0, 6));
    $name = sanitize($_POST['group_name'] ?? 'My Group Order');
    $stmt = $db->prepare("INSERT INTO group_orders (group_code, creator_id, name) VALUES (?,?,?)");
    $stmt->bind_param("sis", $code, $uid, $name);
    $stmt->execute();
    // Add creator as member
    $uname = $_SESSION['user_name'];
    $stmt2 = $db->prepare("INSERT INTO group_order_members (group_code, user_id, user_name) VALUES (?,?,?)");
    $stmt2->bind_param("sis", $code, $uid, $uname);
    $stmt2->execute();
    setFlash('success', "Group created! Share code: $code");
    header("Location: " . SITE_URL . "/group-order.php?code=$code");
    exit;
}

// Handle join group
if (isset($_POST['join_group'])) {
    $code = strtoupper(sanitize($_POST['group_code']));
    $group = $db->query("SELECT * FROM group_orders WHERE group_code='$code' AND status='open'")->fetch_assoc();
    if ($group) {
        $exists = $db->query("SELECT id FROM group_order_members WHERE group_code='$code' AND user_id=$uid")->num_rows;
        if (!$exists) {
            $uname = $_SESSION['user_name'];
            $stmt = $db->prepare("INSERT INTO group_order_members (group_code, user_id, user_name) VALUES (?,?,?)");
            $stmt->bind_param("sis", $code, $uid, $uname);
            $stmt->execute();
        }
        header("Location: " . SITE_URL . "/group-order.php?code=$code");
        exit;
    } else {
        setFlash('error', 'Invalid or expired group code');
    }
}

// Load current group
$currentGroup = null;
$members = [];
$groupCode = sanitize($_GET['code'] ?? '');
if ($groupCode) {
    $currentGroup = $db->query("SELECT g.*, u.name as creator_name FROM group_orders g JOIN users u ON g.creator_id=u.id WHERE g.group_code='$groupCode'")->fetch_assoc();
    if ($currentGroup) {
        $members = $db->query("SELECT * FROM group_order_members WHERE group_code='$groupCode' ORDER BY joined_at")->fetch_all(MYSQLI_ASSOC);
    }
}

// My groups
$myGroups = $db->query("SELECT g.* FROM group_orders g JOIN group_order_members m ON g.group_code=m.group_code WHERE m.user_id=$uid ORDER BY g.created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
$db->close();
?>
<meta name="site-url" content="<?= SITE_URL ?>">

<div style="background:linear-gradient(135deg,#1a1a2e,#16213e);padding:4rem 0 3rem;text-align:center">
  <div class="container">
    <div style="font-size:4rem;margin-bottom:1rem">👥</div>
    <h1 style="color:white;font-family:'Playfair Display',serif;font-size:2.8rem">Group Order & Smart Bill Split</h1>
    <p style="color:rgba(255,255,255,0.7);font-size:1.05rem">Order together with friends. Everyone picks their items. Bill splits automatically.</p>
  </div>
</div>

<div class="container py-5">
  
  <?php if ($currentGroup): ?>
  <!-- Active Group View -->
  <div class="row g-4 justify-content-center">
    <div class="col-lg-8">
      <div class="card-fe p-4" style="background:linear-gradient(135deg,#1a1a2e,#16213e);color:white;text-align:center;margin-bottom:2rem">
        <div style="font-size:0.85rem;color:rgba(255,255,255,0.6);text-transform:uppercase;letter-spacing:1px;margin-bottom:0.5rem">Group Code</div>
        <div class="group-code-display"><?= htmlspecialchars($currentGroup['group_code']) ?></div>
        <div style="margin-top:0.75rem;color:rgba(255,255,255,0.7);font-size:0.9rem">
          📋 Share this code with your friends to join!
        </div>
        <div style="margin-top:0.5rem">
          <span style="background:rgba(46,196,182,0.2);border:1px solid rgba(46,196,182,0.4);color:#2ec4b6;padding:0.2rem 0.8rem;border-radius:50px;font-size:0.78rem;font-weight:700">
            ● <?= strtoupper($currentGroup['status']) ?>
          </span>
        </div>
      </div>
      
      <!-- Members -->
      <div class="card-fe p-4 mb-4">
        <h5 style="font-family:'Playfair Display',serif;margin-bottom:1.5rem">Group Members (<?= count($members) ?>)</h5>
        <?php foreach ($members as $m): ?>
        <div class="d-flex align-items-center justify-content-between p-3 rounded mb-2" style="background:var(--silver-bg)">
          <div class="d-flex align-items-center gap-3">
            <div style="width:40px;height:40px;border-radius:50%;background:var(--gradient);display:flex;align-items:center;justify-content:center;color:white;font-weight:700">
              <?= strtoupper($m['user_name'][0]) ?>
            </div>
            <div>
              <div style="font-weight:600"><?= htmlspecialchars($m['user_name']) ?></div>
              <?php if ($m['user_id'] == $currentGroup['creator_id']): ?>
              <div style="font-size:0.75rem;color:var(--primary)">👑 Creator</div>
              <?php endif; ?>
            </div>
          </div>
          <div class="text-end">
            <div style="font-weight:700">₹<?= number_format($m['amount_due'], 2) ?></div>
            <?php if ($m['amount_due'] > 0): ?>
            <div style="font-size:0.75rem;color:<?= $m['is_paid'] ? '#2e7d32' : '#c62828' ?>">
              <?= $m['is_paid'] ? '✅ Paid' : '⏳ Pending' ?>
            </div>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      
      <!-- Action Buttons -->
      <div class="d-flex gap-3 flex-wrap">
        <a href="<?= SITE_URL ?>/menu.php" class="btn-primary-fe">
          🍽️ Browse & Add Items
        </a>
        <?php if ($currentGroup['creator_id'] == $uid): ?>
        <button class="btn-outline-fe" style="color:var(--text);border-color:var(--silver-light)" onclick="alert('Bill split feature: Each member will receive a payment link with their share!')">
          💰 Split Bill
        </button>
        <?php endif; ?>
      </div>
    </div>
  </div>
  
  <?php else: ?>
  <!-- Default View -->
  <div class="row g-4">
    <!-- Create Group -->
    <div class="col-md-6">
      <div class="card-fe p-4">
        <div style="font-size:3rem;margin-bottom:1rem">🆕</div>
        <h5 style="font-family:'Playfair Display',serif;margin-bottom:0.5rem">Create a Group</h5>
        <p class="text-muted mb-3">Start a group order and invite your friends</p>
        <form method="POST">
          <div class="form-group mb-3">
            <label>Group Name</label>
            <input type="text" name="group_name" class="form-control-fe" placeholder="e.g. Friday Night Dinner" required>
          </div>
          <button type="submit" name="create_group" class="btn-primary-fe w-100 justify-content-center">
            Create Group Order
          </button>
        </form>
      </div>
    </div>
    <!-- Join Group -->
    <div class="col-md-6">
      <div class="card-fe p-4">
        <div style="font-size:3rem;margin-bottom:1rem">🔗</div>
        <h5 style="font-family:'Playfair Display',serif;margin-bottom:0.5rem">Join a Group</h5>
        <p class="text-muted mb-3">Enter the group code shared by your friend</p>
        <form method="POST">
          <div class="form-group mb-3">
            <label>Group Code</label>
            <input type="text" name="group_code" class="form-control-fe" placeholder="Enter 6-digit code" maxlength="6" style="text-transform:uppercase;letter-spacing:4px;font-size:1.1rem;text-align:center" required>
          </div>
          <button type="submit" name="join_group" class="btn-primary-fe w-100 justify-content-center">
            Join Group
          </button>
        </form>
      </div>
    </div>
    
    <!-- My Groups -->
    <?php if (!empty($myGroups)): ?>
    <div class="col-12">
      <div class="card-fe p-4">
        <h5 style="font-family:'Playfair Display',serif;margin-bottom:1.5rem">My Recent Groups</h5>
        <div class="row g-3">
          <?php foreach ($myGroups as $g): ?>
          <div class="col-md-4">
            <a href="?code=<?= $g['group_code'] ?>" class="d-block p-3 rounded text-decoration-none" style="background:var(--silver-bg);transition:all 0.2s" onmouseover="this.style.background='rgba(230,57,70,0.05)'" onmouseout="this.style.background='var(--silver-bg)'">
              <div style="font-weight:700;margin-bottom:0.3rem"><?= htmlspecialchars($g['name']) ?></div>
              <div style="font-size:0.8rem;color:var(--text-muted)">Code: <strong><?= $g['group_code'] ?></strong></div>
              <div class="mt-1">
                <span style="background:<?= $g['status'] === 'open' ? '#e8f5e9' : '#fce4ec' ?>;color:<?= $g['status'] === 'open' ? '#2e7d32' : '#c62828' ?>;padding:0.15rem 0.6rem;border-radius:50px;font-size:0.72rem;font-weight:700">
                  <?= ucfirst($g['status']) ?>
                </span>
              </div>
            </a>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>
    
    <!-- How It Works -->
    <div class="col-12">
      <div class="card-fe p-4">
        <h5 style="font-family:'Playfair Display',serif;margin-bottom:2rem;text-align:center">How Group Ordering Works</h5>
        <div class="row g-3 text-center">
          <?php foreach ([['1️⃣','Create Group','Start a group and get a unique 6-digit code'],['2️⃣','Invite Friends','Share the code with everyone who wants to join'],['3️⃣','Everyone Orders','Each person browses and adds their own items'],['4️⃣','Auto Bill Split','System calculates each person\'s share automatically']] as [$n,$t,$d]): ?>
          <div class="col-md-3">
            <div style="font-size:2.5rem;margin-bottom:0.75rem"><?= $n ?></div>
            <strong><?= $t ?></strong>
            <p class="text-muted small mt-1"><?= $d ?></p>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
