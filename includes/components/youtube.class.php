<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

/** Only a validated ID reaches a fixed HTTPS origin; streamed bytes and worker duration are bounded. */
final class Youtube
{
    public const int MAX_BYTES = 65536;
    public const int CONNECT_MS = 2000;
    public const int TOTAL_MS = 5000;

    public static function fetch(string $id, ?int &$status = 0) : ?\stdClass
    {
        $status = 0;
        if (!preg_match('/^[a-zA-Z0-9_-]{11}$/D', $id) || !extension_loaded('curl') ||
            !(curl_version()['features'] & CURL_VERSION_ASYNCHDNS))
            return null;                                   // synchronous DNS cannot promise the configured deadline
        $curl = curl_init('https://www.youtube.com/oembed?format=json&url=https://www.youtube.com/watch?v='.$id);
        if (!$curl) return null;
        $body = '';
        $headers = 0;
        try
        {
            if (!curl_setopt_array($curl, [
                CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_CONNECTTIMEOUT_MS => self::CONNECT_MS,
                CURLOPT_TIMEOUT_MS => self::TOTAL_MS,
                CURLOPT_NOSIGNAL => true,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_HTTPHEADER => ['Accept: application/json'],
                CURLOPT_HEADERFUNCTION => static function ($handle, string $data) use (&$headers) : int {
                    $headers += strlen($data);
                    return $headers <= 16384 ? strlen($data) : 0;
                },
                CURLOPT_WRITEFUNCTION => static function ($handle, string $data) use (&$body) : int {
                    if (strlen($body) + strlen($data) > self::MAX_BYTES) return 0;
                    $body .= $data;
                    return strlen($data);
                }
            ])) return null;
            $ok = curl_exec($curl);
            $status = (int)curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
            if ($ok === false || $status !== 200) return null;
            $info = json_decode($body, false, 16, JSON_THROW_ON_ERROR);
            if (!$info instanceof \stdClass || ($info->type ?? null) !== 'video' || ($info->provider_name ?? null) !== 'YouTube' ||
                !is_string($info->title ?? null) || trim($info->title) === '' || strlen($info->title) > 4096 ||
                preg_match('/[\x00-\x1f\x7f]/', $info->title) || !is_string($info->thumbnail_url ?? null) ||
                strlen($info->thumbnail_url) > 64 || !preg_match('~^https://(?:i\.ytimg\.com|img\.youtube\.com)/[a-zA-Z0-9_./?=&%-]+$~D', $info->thumbnail_url))
                return null;
            foreach (['thumbnail_width', 'thumbnail_height'] as $field)
                if (!is_int($info->$field ?? null) || $info->$field < 1 || $info->$field > 4096) return null;
            // Normalize only the fields consumed by the legacy confirmation/persistence flow.
            return (object)['id' => $id, 'title' => mb_substr($info->title, 0, 64), 'thumbnail_url' => $info->thumbnail_url,
                'thumbnail_width' => $info->thumbnail_width, 'thumbnail_height' => $info->thumbnail_height];
        }
        catch (\Throwable) { return null; }
        finally { unset($curl); }
    }
}
