<?php
declare(strict_types=1);

/**
 * http_forms.php — live CSRF enforcement test against a running instance.
 *
 * For every POST endpoint it fires two requests and checks the server's own
 * behaviour, so the assertions are data- and permission-independent:
 *
 *   - BAD  token  -> the server MUST reject it. We look for the exact message
 *                    the middleware emits: "Invalid or missing CSRF token".
 *   - GOOD token  -> the server MUST NOT emit that message (it may 200, 302,
 *                    or even 403 for a permission reason — just not a CSRF 403).
 *
 * This works because Csrf::check() runs at the top of each handler, before any
 * routing/permission/validation logic, and the token is reused for the whole
 * session (so one token scraped after login is valid everywhere).
 *
 * Requirements: a running app, a test DB, and valid login credentials.
 * The PHP `curl` extension must be enabled.
 *
 * Usage:
 *   php tests/http_forms.php --base=http://localhost:8080 \
 *       --email=admin@example.com --password=secret
 *
 *   Optional:
 *     --login-path=/login   (default; some setups use /login.php)
 *     --token-page=/modules/rfq/create.php   page to scrape a fresh token from
 *     --insecure            skip TLS verification (self-signed certs)
 *
 * Exit: 0 = all passed, 1 = failures, 2 = setup error (login/token failed).
 */

if (!function_exists('curl_init')) {
    fwrite(STDERR, "ERROR: PHP curl extension is required.\n");
    exit(2);
}

// ---- args ------------------------------------------------------------------
$opts = getopt('', [
    'base:', 'email:', 'password:', 'login-path::', 'token-page::', 'insecure',
]);
$base      = rtrim($opts['base'] ?? '', '/');
$email     = $opts['email'] ?? '';
$password  = $opts['password'] ?? '';
$loginPath = $opts['login-path'] ?? '/login';
$tokenPage = $opts['token-page'] ?? '/modules/rfq/create.php';
$insecure  = isset($opts['insecure']);

if ($base === '' || $email === '' || $password === '') {
    fwrite(STDERR, "Usage: php tests/http_forms.php --base=URL --email=E --password=P\n");
    exit(2);
}

$CSRF_MESSAGE = 'Invalid or missing CSRF token';

// POST endpoints to probe (paths as they appear in the forms' action=).
$endpoints = [
    '/admin/users.php',
    '/admin/permissions.php',
    '/modules/campaign/create.php',
    '/modules/campaign/edit.php',
    '/modules/campaign/detail.php',
    '/modules/campaign/audience.php',
    '/modules/campaign/preview_audience.php',
    '/modules/customer/create_account.php',
    '/modules/customer/account_detail.php',
    '/modules/inventory/products.php',
    '/modules/rfq/create.php',
    '/modules/rfq/edit.php',
    '/modules/rfq/detail.php',
    '/modules/rfq/create_quote.php',
    '/modules/rfq/edit_quote.php',
    '/modules/rfq/create_reservation.php',
    '/modules/rfq/edit_reservation.php',
];

// ---- curl helper -----------------------------------------------------------
$cookieJar = tempnam(sys_get_temp_dir(), 'csrf_cookies_');

/**
 * @return array{status:int, body:string}
 */
function http(string $method, string $url, ?array $post = null): array {
    global $cookieJar, $insecure;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,   // we want to see 302s, not follow them
        CURLOPT_COOKIEJAR      => $cookieJar,
        CURLOPT_COOKIEFILE     => $cookieJar,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_SSL_VERIFYPEER => !$insecure,
        CURLOPT_SSL_VERIFYHOST => $insecure ? 0 : 2,
    ]);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post ?? []));
    }
    $body   = (string)curl_exec($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err    = curl_error($ch);
    curl_close($ch);
    if ($body === '' && $status === 0) {
        fwrite(STDERR, "  curl error for $url: $err\n");
    }
    return ['status' => $status, 'body' => $body];
}

/** Pull a CSRF token from a hidden input or the <meta> tag in an HTML body. */
function scrape_token(string $html): ?string {
    if (preg_match('/name="_csrf"\s+value="([^"]+)"/', $html, $m)) {
        return $m[1];
    }
    if (preg_match('/name="csrf-token"\s+content="([^"]+)"/', $html, $m)) {
        return $m[1];
    }
    return null;
}

// ---- 1. log in -------------------------------------------------------------
fwrite(STDOUT, "== Setup: logging in ==\n");

$loginGet = http('GET', $base . $loginPath);
$loginToken = scrape_token($loginGet['body']);
if ($loginToken === null) {
    fwrite(STDERR, "SETUP FAIL: could not find a CSRF token on $loginPath (status {$loginGet['status']}).\n");
    exit(2);
}

$loginPost = http('POST', $base . $loginPath, [
    '_csrf'    => $loginToken,
    'email'    => $email,
    'password' => $password,
]);

// A successful login redirects (302) away from the login page.
if ($loginPost['status'] !== 302 && stripos($loginPost['body'], 'alert-danger') !== false) {
    fwrite(STDERR, "SETUP FAIL: login rejected the credentials (status {$loginPost['status']}).\n");
    exit(2);
}
fwrite(STDOUT, "  login ok (status {$loginPost['status']})\n");

// ---- 2. get a token valid for the logged-in session ------------------------
$tokenGet = http('GET', $base . $tokenPage);
$token = scrape_token($tokenGet['body']) ?? $loginToken; // token persists across regenerate
fwrite(STDOUT, "  using session token " . substr($token, 0, 8) . "...\n\n");

// ---- 3. probe every endpoint ----------------------------------------------
fwrite(STDOUT, "== CSRF enforcement per endpoint ==\n");
$failures = 0;
$passes   = 0;

foreach ($endpoints as $path) {
    $url = $base . $path;

    // (a) BAD token -> must be rejected with the CSRF message.
    $bad = http('POST', $url, ['_csrf' => 'this-is-not-a-valid-token']);
    $badRejected = strpos($bad['body'], $CSRF_MESSAGE) !== false;

    // (b) GOOD token -> must NOT produce the CSRF message.
    $good = http('POST', $url, ['_csrf' => $token]);
    $goodAccepted = strpos($good['body'], $CSRF_MESSAGE) === false;

    if ($badRejected && $goodAccepted) {
        $passes++;
        fwrite(STDOUT, sprintf(
            "PASS  %-42s bad=%d(rejected) good=%d(passed)\n",
            $path, $bad['status'], $good['status']
        ));
    } else {
        $failures++;
        $why = [];
        if (!$badRejected)  { $why[] = "bad token NOT rejected (status {$bad['status']})"; }
        if (!$goodAccepted) { $why[] = "good token hit CSRF wall (status {$good['status']})"; }
        fwrite(STDOUT, sprintf("FAIL  %-42s %s\n", $path, implode('; ', $why)));
    }
}

@unlink($cookieJar);

fwrite(STDOUT, "\n----------------------------------------\n");
fwrite(STDOUT, sprintf("%d passed, %d failed\n", $passes, $failures));
exit($failures > 0 ? 1 : 0);
