<?php
// ponytail: one-off scratch; test login + protected endpoint, delete after use
function api(string $method, string $path, ?array $body = null, ?string $token = null): array {
    $ch = curl_init('http://127.0.0.1:8000' . $path);
    $headers = ['Content-Type: application/json', 'Accept: application/json'];
    if ($token) $headers[] = 'Authorization: Bearer ' . $token;
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_POSTFIELDS     => $body ? json_encode($body) : null,
        CURLOPT_TIMEOUT        => 30,
    ]);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'body' => substr((string)$res, 0, 400)];
}

$login = api('POST', '/api/v1/auth/login', ['email' => 'owner@majubersamasentosa.co.id', 'password' => 'password']);
echo 'LOGIN: ' . $login['code'] . ' ' . $login['body'] . PHP_EOL;

preg_match('/"token":"([^"]+)"/', $login['body'], $m);
if (isset($m[1])) {
    $users = api('GET', '/api/v1/users', null, $m[1]);
    echo 'USERS: ' . $users['code'] . ' ' . $users['body'] . PHP_EOL;
}
