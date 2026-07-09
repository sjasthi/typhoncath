<?php
declare(strict_types=1);

namespace App\Core;

/**
 * CSRF (Cross-Site Request Forgery) protection.
 *
 * A CSRF token is a secret, unpredictable value the server hands to the
 * browser when it renders a form. When the form is submitted, the token comes
 * back and the server checks it matches the one stored in the session. A
 * malicious third-party site can trick a logged-in user's browser into sending
 * a request, but it cannot read this token, so it cannot forge a valid one.
 *
 * Usage:
 *   - In a form view:   <?= App\Core\Csrf::field() ?>
 *   - On a POST route:   require .../Middleware/csrf.php   (validates automatically)
 *     or manually:       Csrf::check();
 */
class Csrf
{
    private const SESSION_KEY = 'csrf_token';
    private const FIELD_NAME  = '_csrf';
    private const HEADER_NAME = 'HTTP_X_CSRF_TOKEN'; // for fetch()/AJAX requests

    /**
     * Return the current session's token, creating one on first use.
     * The same token is reused for the whole session (simple and sufficient
     * for this app; combined with SameSite cookies it's solid protection).
     */
    public static function token(): string
    {
        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * A ready-to-paste hidden input for any <form method="POST">.
     */
    public static function field(): string
    {
        $token = htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8');
        $name  = self::FIELD_NAME;
        return "<input type=\"hidden\" name=\"{$name}\" value=\"{$token}\">";
    }

    /** The raw token, for use in a <meta> tag or JS-driven request. */
    public static function metaTag(): string
    {
        $token = htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8');
        return "<meta name=\"csrf-token\" content=\"{$token}\">";
    }

    /**
     * Constant-time comparison of the submitted token against the session
     * token. Reads from the POST body first, then the X-CSRF-Token header.
     */
    public static function validate(): bool
    {
        $sessionToken = $_SESSION[self::SESSION_KEY] ?? '';
        if ($sessionToken === '') {
            return false;
        }

        $submitted = $_POST[self::FIELD_NAME]
            ?? $_SERVER[self::HEADER_NAME]
            ?? '';

        return is_string($submitted)
            && $submitted !== ''
            && hash_equals($sessionToken, $submitted);
    }

    /**
     * Enforce a valid token on state-changing requests. Safe methods
     * (GET/HEAD/OPTIONS) are ignored. On failure it sends 403 and halts.
     */
    public static function check(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return;
        }

        if (!self::validate()) {
            http_response_code(403);
            exit('Invalid or missing CSRF token. Please reload the page and try again.');
        }
    }
}
