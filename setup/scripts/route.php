<?php
// redirects  all the static requests to wowgaming
$uri = $_SERVER["REQUEST_URI"];

if (strpos($uri, "static/images/logos/header.png") > -1) {
    return false;
}

function urlExists($url) {
    $headers = @get_headers($url);
    return $headers && strpos($headers[0], '200') !== false;
}

if (preg_match('/^\/static\/.*\.(png|gif|jpg)$/i', $uri, $matches)) {
    $extension = $matches[1];

    $externalUrl = "https://wowgaming.altervista.org/aowow" . $uri;

    if (strpos($uri, "wow/maps") == -1) {
        header("Location: $externalUrl", true, 302);
    }

    // fallback for wow maps with names *-1.png/gif/jpg
    if (urlExists($externalUrl)) {
        header("Location: $externalUrl", true, 302);
    } else {
        $fallbackUrl = preg_replace('/\.(png|gif|jpg)$/i', "-1.$extension", $externalUrl);
        header("Location: $fallbackUrl", true, 302);
    }

    exit();
}

return false;
?>
