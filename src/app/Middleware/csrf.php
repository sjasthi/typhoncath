<?php
/*
 * CSRF protection middleware.
 *
 * Include this AFTER bootstrap.php on any page that handles a POST (or other
 * state-changing) request. It validates the token that Csrf::field() embeds in
 * the form. Safe methods (GET/HEAD) pass straight through, so it is harmless to
 * include on pages that also serve read-only requests.
 *
 * Example usage at the top of a POST-handling page:
 *
 *   require_once __DIR__ . '/../../../app/Core/bootstrap.php';
 *   require_once __DIR__ . '/../../../app/Middleware/require_auth.php';
 *   require_once __DIR__ . '/../../../app/Middleware/csrf.php';
 *
 * And in the matching form view:
 *
 *   <form method="POST">
 *       <?= App\Core\Csrf::field() ?>
 *       ...
 *   </form>
 */

use App\Core\Csrf;

Csrf::check();
