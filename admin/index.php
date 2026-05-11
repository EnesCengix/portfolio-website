<?php
// ─── admin/index.php — Admin Dashboard ───────────────────────
session_start();

// Auth guard
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

require_once '../db.php';

$toast = ['msg' => '', 'type' => ''];

// ── Handle ADD ────────────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $title       = trim(strip_tags($_POST['title'] ?? ''));
    $description = trim(strip_tags($_POST['description'] ?? ''));
    $github      = trim($_POST['github_link'] ?? '');

    if ($title && $description) {
        $stmt = $pdo->prepare(
            'INSERT INTO projects (title, description, github_link) VALUES (:t, :d, :g)'
        );
        $stmt->execute([':t' => $title, ':d' => $description, ':g' => $github]);
        $toast = ['msg' => 'Project added successfully!', 'type' => 'success'];
    } else {
        $toast = ['msg' => 'Title and description are required.', 'type' => 'error'];
    }
}

// ── Handle EDIT ───────────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id          = (int)($_POST['id'] ?? 0);
    $title       = trim(strip_tags($_POST['title'] ?? ''));
    $description = trim(strip_tags($_POST['description'] ?? ''));
    $github      = trim($_POST['github_link'] ?? '');

    if ($id && $title && $description) {
        $stmt = $pdo->prepare(
            'UPDATE projects SET title=:t, description=:d, github_link=:g WHERE id=:id'
        );
        $stmt->execute([':t' => $title, ':d' => $description, ':g' => $github, ':id' => $id]);
        $toast = ['msg' => 'Project updated!', 'type' => 'success'];
    } else {
        $toast = ['msg' => 'Invalid data for edit.', 'type' => 'error'];
    }
}

// ── Handle DELETE ─────────────────────────────────────────────
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id) {
        $pdo->prepare('DELETE FROM projects WHERE id = :id')->execute([':id' => $id]);
        $toast = ['msg' => 'Project deleted.', 'type' => 'success'];
    }
    // PRG pattern — redirect to avoid re-submission on refresh
    header('Location: index.php?msg=deleted');
    exit;
}

// ── Fetch project for editing ─────────────────────────────────
$editProject = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM projects WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => (int)$_GET['edit']]);
    $editProject = $stmt->fetch();
}

// ── Fetch all projects ────────────────────────────────────────
$projects = $pdo->query('SELECT * FROM projects ORDER BY created_at DESC')->fetchAll();

// ── Fetch unread messages ─────────────────────────────────────
$messages = $pdo->query('SELECT * FROM messages ORDER BY created_at DESC LIMIT 20')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — Portfolio</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        body { padding: 20px; }

        /* ── Layout ── */
        .dash-header {
            display: flex; justify-content: space-between; align-items: center;
            max-width: 1100px; margin: 0 auto 40px;
            flex-wrap: wrap; gap: 10px;
        }
        .dash-header h1 { font-size: 2rem; margin: 0; }
        .dash-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            max-width: 1100px;
            margin: auto;
        }
        @media (max-width: 768px) { .dash-grid { grid-template-columns: 1fr; } }

        /* ── Cards ── */
        .dash-card { padding: 30px; }
        .dash-card h2 { margin-top: 0; font-size: 1.4rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 12px; margin-bottom: 20px; }

        /* ── Form ── */
        .dash-form { display: flex; flex-direction: column; gap: 14px; }
        .dash-form input, .dash-form textarea {
            width: 100%; padding: 12px; border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(0,0,0,0.2); color: #fff;
            font-family: inherit; font-size: 0.95rem; transition: 0.3s;
        }
        .dash-form input:focus, .dash-form textarea:focus {
            outline: none; border-color: #a18cd1; background: rgba(0,0,0,0.35);
        }
        .dash-form .btn-row { display: flex; gap: 10px; flex-wrap: wrap; }

        /* ── Projects table ── */
        .proj-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 12px; }
        .proj-item {
            background: rgba(0,0,0,0.2); border-radius: 12px;
            padding: 15px 18px; display: flex;
            justify-content: space-between; align-items: flex-start;
            gap: 10px; border: 1px solid rgba(255,255,255,0.05);
            transition: 0.3s;
        }
        .proj-item:hover { border-color: rgba(0,242,254,0.3); }
        .proj-item-info h4 { margin: 0 0 4px; color: #f8fafc; }
        .proj-item-info p  { margin: 0; color: #94a3b8; font-size: 0.85rem; }
        .proj-actions { display: flex; gap: 8px; flex-shrink: 0; }
        .btn-sm {
            padding: 6px 14px; border-radius: 8px; border: none;
            font-size: 0.82rem; font-weight: 600; cursor: pointer; transition: 0.3s;
            text-decoration: none; display: inline-block;
        }
        .btn-edit   { background: rgba(79,172,254,0.15); color: #4facfe; border: 1px solid #4facfe; }
        .btn-delete { background: rgba(239,68,68,0.15);  color: #f87171;  border: 1px solid #f87171;  }
        .btn-edit:hover   { background: #4facfe; color: #fff; }
        .btn-delete:hover { background: #f87171; color: #fff; }

        /* ── Messages table ── */
        .msg-item {
            background: rgba(0,0,0,0.2); border-radius: 12px;
            padding: 15px 18px; border: 1px solid rgba(255,255,255,0.05);
            margin-bottom: 10px;
        }
        .msg-meta { display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 6px; }
        .msg-meta strong { color: #f8fafc; }
        .msg-meta span   { color: #4facfe; font-size: 0.85rem; }
        .msg-meta time   { color: #64748b; font-size: 0.8rem; margin-left: auto; }
        .msg-text { color: #cbd5e1; font-size: 0.9rem; line-height: 1.5; }
        .no-data  { color: #64748b; font-style: italic; text-align: center; padding: 20px 0; }

        /* ── Toast ── */
        .admin-toast {
            position: fixed; top: 20px; right: 20px;
            padding: 12px 22px; border-radius: 12px;
            font-weight: 600; font-size: 0.9rem; z-index: 9999;
            animation: fadeIn 0.4s ease, fadeOut 0.4s ease 3.5s forwards;
        }
        .admin-toast.success { background: rgba(34,197,94,0.9); color: #fff; }
        .admin-toast.error   { background: rgba(239,68,68,0.9);  color: #fff; }
        @keyframes fadeIn  { from { opacity:0; transform: translateY(-10px); } to { opacity:1; transform: translateY(0); } }
        @keyframes fadeOut { from { opacity:1; } to { opacity:0; } }

        /* Light mode overrides */
        body.light .dash-form input, body.light .dash-form textarea { background: #f1f5f9; border: 1px solid #cbd5e1; color: #0f172a; }
        body.light .proj-item, body.light .msg-item { background: rgba(241,245,249,0.6); border-color: rgba(0,0,0,0.05); }
        body.light .proj-item-info h4, body.light .msg-meta strong { color: #0f172a; }
    </style>
</head>
<body>

<?php if ($toast['msg']): ?>
<div class="admin-toast <?= $toast['type'] ?>">
    <?= htmlspecialchars($toast['msg']) ?>
</div>
<?php endif; ?>

<div class="dash-header">
    <h1 class="gradient-text">⚙ Admin Dashboard</h1>
    <div style="display:flex;gap:12px;align-items:center;">
        <span style="color:#64748b;font-size:0.9rem;">Hello, <strong style="color:#a18cd1"><?= htmlspecialchars($_SESSION['admin_username']) ?></strong></span>
        <a href="../index.html" class="btn-sm btn-edit">← Portfolio</a>
        <a href="logout.php" class="btn-sm btn-delete">Logout</a>
    </div>
</div>

<div class="dash-grid">

    <!-- ── ADD / EDIT FORM ── -->
    <div class="dash-card glass-panel">
        <h2 class="gradient-text">
            <?= $editProject ? '✏️ Edit Project' : '➕ Add Project' ?>
        </h2>
        <form method="POST" action="index.php" class="dash-form">
            <input type="hidden" name="action" value="<?= $editProject ? 'edit' : 'add' ?>">
            <?php if ($editProject): ?>
                <input type="hidden" name="id" value="<?= (int)$editProject['id'] ?>">
            <?php endif; ?>

            <input type="text" name="title"
                   placeholder="Project Title *"
                   value="<?= htmlspecialchars($editProject['title'] ?? '') ?>"
                   required>

            <textarea name="description" rows="4"
                      placeholder="Description *" required><?= htmlspecialchars($editProject['description'] ?? '') ?></textarea>

            <input type="url" name="github_link"
                   placeholder="GitHub URL (optional)"
                   value="<?= htmlspecialchars($editProject['github_link'] ?? '') ?>">

            <div class="btn-row">
                <button type="submit" class="gradient-btn" style="font-size:0.95rem;padding:11px 22px;">
                    <?= $editProject ? 'Update Project' : 'Add Project' ?>
                </button>
                <?php if ($editProject): ?>
                    <a href="index.php" class="btn-sm btn-edit" style="padding:11px 18px;">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- ── PROJECT LIST ── -->
    <div class="dash-card glass-panel">
        <h2 class="gradient-text">📁 Projects (<?= count($projects) ?>)</h2>
        <?php if ($projects): ?>
        <ul class="proj-list">
            <?php foreach ($projects as $p): ?>
            <li class="proj-item">
                <div class="proj-item-info">
                    <h4><?= htmlspecialchars($p['title']) ?></h4>
                    <p><?= htmlspecialchars(substr($p['description'], 0, 70)) . (strlen($p['description']) > 70 ? '…' : '') ?></p>
                </div>
                <div class="proj-actions">
                    <a href="index.php?edit=<?= (int)$p['id'] ?>" class="btn-sm btn-edit">Edit</a>
                    <a href="index.php?delete=<?= (int)$p['id'] ?>"
                       class="btn-sm btn-delete"
                       onclick="return confirm('Delete \'<?= addslashes($p['title']) ?>\'?')">Delete</a>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php else: ?>
            <p class="no-data">No projects yet. Add one above!</p>
        <?php endif; ?>
    </div>

    <!-- ── INBOX ── -->
    <div class="dash-card glass-panel" style="grid-column: 1 / -1;">
        <h2 class="gradient-text">📨 Contact Messages (<?= count($messages) ?>)</h2>
        <?php if ($messages): ?>
            <?php foreach ($messages as $m): ?>
            <div class="msg-item">
                <div class="msg-meta">
                    <strong><?= htmlspecialchars($m['name']) ?></strong>
                    <span><?= htmlspecialchars($m['email']) ?></span>
                    <time><?= date('d M Y H:i', strtotime($m['created_at'])) ?></time>
                </div>
                <p class="msg-text"><?= nl2br(htmlspecialchars($m['message'])) ?></p>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-data">No messages yet.</p>
        <?php endif; ?>
    </div>

</div>
</body>
</html>
