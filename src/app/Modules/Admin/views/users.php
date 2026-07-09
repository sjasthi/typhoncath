<?php
$roleBadgeMap = [
    'Super Admin'       => 'rfq-badge-danger',
    'Admin'             => 'rfq-badge-warning',
    'Sales User'        => 'rfq-badge-info',
    'Marketing User'    => 'rfq-badge-success',
    'Inventory Manager' => 'rfq-badge-quoted',
];
?>

<section class="card">

    <div class="module-header">
        <h1>Users</h1>
        <div class="module-header-actions">
            <a href="/admin/permissions.php" class="btn btn-secondary">Permission Matrix</a>
            <a href="/admin/users.php?action=create" class="btn btn-primary">+ Add User</a>
        </div>
    </div>

    <?php if (empty($users)): ?>
    <p class="text-muted">No users found.</p>
    <?php else: ?>

    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u):
                $isCustom = !empty($u['owner_user_id']);
                $badge    = $isCustom ? 'rfq-badge-warning' : ($roleBadgeMap[$u['role_name']] ?? 'rfq-badge-neutral');
                $isSelf   = (int)$u['id'] === (int)(App\Core\Auth::user()['id'] ?? 0);
            ?>
            <tr>
                <td>
                    <?= htmlspecialchars($u['name']) ?>
                    <?php if ($isSelf): ?>
                    <span class="rfq-badge rfq-badge-neutral" style="font-size:0.65rem;margin-left:4px;">you</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td>
                    <span class="rfq-badge <?= $badge ?>">
                        <?= $isCustom ? 'Custom' : htmlspecialchars($u['role_name']) ?>
                    </span>
                </td>
                <td><?= htmlspecialchars(date('M j, Y', strtotime($u['created_at']))) ?></td>
                <td>
                    <a href="/admin/users.php?action=edit&id=<?= (int)$u['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>

                    <?php if (!$isSelf): ?>
                    <form method="POST" action="/admin/users.php" style="display:inline;"
                          onsubmit="return confirm('Delete <?= htmlspecialchars(addslashes($u['name'])) ?>? This cannot be undone.');">
                        <?= App\Core\Csrf::field() ?>
                        <input type="hidden" name="_action" value="delete">
                        <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm">&times;</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php endif; ?>

</section>
