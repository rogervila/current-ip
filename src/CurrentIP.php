<?php

namespace CurrentIP;

class CurrentIP
{
    public static function get(): ?string
    {
        // Fast path: direct connection without proxy
        if (!empty($_SERVER['REMOTE_ADDR'])) {
            $remoteAddr = $_SERVER['REMOTE_ADDR'];
            if (filter_var($remoteAddr, FILTER_VALIDATE_IP)) {
                if (empty($_SERVER['HTTP_X_FORWARDED_FOR']) &&
                    empty($_SERVER['HTTP_CLIENT_IP']) &&
                    empty($_SERVER['HTTP_X_FORWARDED']) &&
                    empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) &&
                    empty($_SERVER['HTTP_FORWARDED_FOR']) &&
                    empty($_SERVER['HTTP_FORWARDED'])) {
                    return $remoteAddr;
                }
            }
        }

        // Check proxy headers
        $headers = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_CLIENT_IP',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED'
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                if (($commaPosition = strpos($ip, ',')) !== false) {
                    $ip = trim(substr($ip, 0, $commaPosition));
                }
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
    }
}
