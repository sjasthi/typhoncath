<main class="login-shell">
    <section class="login-card">
        <h1>Typhon Cath CRM</h1>
        <p class="text-muted">Sign in to continue.</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="/login">
            <label>Email</label>
            <input class="form-control" type="email" name="email" required
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

            <label>Password</label>
            <input class="form-control" type="password" name="password" required>

            <button class="btn btn-primary w-100 mt-3" type="submit">Login</button>
        </form>
    </section>
</main>
