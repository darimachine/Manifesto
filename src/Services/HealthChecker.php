<?php

declare(strict_types=1);

namespace Manifesto\Services;

/**
 * Performs a single HTTP request to a URL and reports reachability.
 * Stateless — does NOT touch the database. Callers decide what to persist.
 */
final class HealthChecker
{
    /**
     * Checks whether the given URL is reachable within the timeout.
     *
     * Returns an associative array with:
     *   - status       string  'up' | 'down' | 'error'
     *   - http_code    int     HTTP response code (0 when no connection was made)
     *   - duration_ms  int     Wall-clock time of the request in milliseconds
     *   - error        ?string cURL error message, or null on success
     *
     * 'up'    → HTTP 2xx or 3xx response received
     * 'down'  → connection succeeded but server returned 4xx/5xx, or cURL failed
     * 'error' → URL is syntactically invalid (no request was attempted)
     *
     * SSL verification is intentionally disabled so self-signed certificates on
     * local Docker services do not cause false negatives.
     *
     * @return array{status: string, http_code: int, duration_ms: int, error: ?string}
     */
    public function check(string $url, int $timeoutSeconds = 5): array
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return [
                'status'      => 'error',
                'http_code'   => 0,
                'duration_ms' => 0,
                'error'       => 'Invalid URL',
            ];
        }

        $ch = curl_init($url);
        if ($ch === false) {
            return [
                'status'      => 'error',
                'http_code'   => 0,
                'duration_ms' => 0,
                'error'       => 'Failed to initialise cURL handle',
            ];
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $timeoutSeconds,
            CURLOPT_CONNECTTIMEOUT => $timeoutSeconds,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_NOBODY         => false,
            CURLOPT_HEADER         => false,
            CURLOPT_USERAGENT      => 'Manifesto-HealthCheck/1.0',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);

        $start    = microtime(true);
        $body     = curl_exec($ch);
        $duration = (int) ((microtime(true) - $start) * 1000);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($body === false || $curlErr !== '') {
            return [
                'status'      => 'down',
                'http_code'   => $httpCode,
                'duration_ms' => $duration,
                'error'       => $curlErr !== '' ? $curlErr : 'Connection failed',
            ];
        }

        return [
            'status'      => ($httpCode >= 200 && $httpCode < 400) ? 'up' : 'down',
            'http_code'   => $httpCode,
            'duration_ms' => $duration,
            'error'       => null,
        ];
    }
}
