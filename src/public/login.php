<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

// Reject state-changing (POST) requests without a valid CSRF token.
require_once __DIR__ . '/../app/Middleware/csrf.php';

use App\Core\Auth;

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (Auth::attempt($email, $password)) {
        header('Location: /dashboard.php');
        exit;
    }

    $error = 'Invalid login credentials.';
}

include __DIR__ . '/../app/Shared/header.php';
?>

<main class="login-shell">
    <section class="login-card">
        <h1>Typhon Cath CRM</h1>
        <p class="text-muted">Sign in to continue.</p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <?= App\Core\Csrf::field() ?>
            <label>Email</label>
            <input class="form-control" type="email" name="email" required>

            <label>Password</label>
            <input class="form-control" type="password" name="password" required>

            <button class="btn btn-primary w-100 mt-3" type="submit">Login</button>
        </form>
    </section>
</main>

<?php include __DIR__ . '/../app/Shared/footer.php'; ?>
