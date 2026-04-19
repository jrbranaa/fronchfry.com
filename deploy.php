<?php
// GitHub webhook handler — runs git pull on push events.
// Setup: create a .deploy-secret file in this directory containing your webhook secret.

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

$secret_file = __DIR__ . '/.deploy-secret';
if (!file_exists($secret_file)) {
    http_response_code(500);
    exit('Secret not configured');
}
$secret = trim(file_get_contents($secret_file));

$sig_header = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
$payload    = file_get_contents('php://input');
$expected   = 'sha256=' . hash_hmac('sha256', $payload, $secret);

if (!hash_equals($expected, $sig_header)) {
    http_response_code(403);
    exit('Forbidden');
}

$data = json_decode($payload, true);

// Only act on pushes to the default/main branch
$ref = $data['ref'] ?? '';
if (!in_array($ref, ['refs/heads/main', 'refs/heads/master'])) {
    http_response_code(200);
    exit('Ignored (not main branch)');
}

$repo_dir = escapeshellarg(__DIR__);
$output   = shell_exec("cd {$repo_dir} && git pull 2>&1");

http_response_code(200);
header('Content-Type: text/plain');
echo $output;
