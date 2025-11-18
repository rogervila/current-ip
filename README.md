[![Status](https://github.com/rogervila/current-ip/workflows/test/badge.svg)](https://github.com/rogervila/current-ip/actions)
[![StyleCI](https://github.styleci.io/repos/211657121/shield?branch=main)](https://github.styleci.io/repos/211657121)

[![Latest Stable Version](https://poser.pugx.org/rogervila/current-ip/v/stable)](https://packagist.org/packages/rogervila/current-ip)
[![Total Downloads](https://poser.pugx.org/rogervila/current-ip/downloads)](https://packagist.org/packages/rogervila/current-ip)
[![License](https://poser.pugx.org/rogervila/current-ip/license)](https://packagist.org/packages/rogervila/current-ip)

# Current IP

A lightweight, framework-agnostic PHP library for detecting the client's IP address from HTTP requests. No dependencies, no framework requirements—just pure PHP.

## Features

- 🚀 **Zero dependencies** - Pure PHP implementation
- 🔍 **Proxy-aware** - Handles X-Forwarded-For and other proxy headers
- ✅ **IP validation** - Built-in validation using PHP's filter functions
- 🔒 **Security-conscious** - Validates IP format and handles spoofing scenarios
- 📦 **Lightweight** - Single function, minimal footprint
- 🌐 **Framework-agnostic** - Works with any PHP application

## Installation

Install via Composer:

```bash
composer require rogervila/current-ip
```

## Requirements

- PHP 7.3 or higher

## Usage

### Basic Usage

```php
<?php
require_once 'vendor/autoload.php';

use CurrentIP\CurrentIP;

$ip = CurrentIP::get();
echo "Client IP: " . ($ip ?? 'Unable to determine');
```

### In Your Application

```php
<?php
use CurrentIP\CurrentIP;

// Get the client IP
$clientIP = CurrentIP::get() ?? '0.0.0.0';

// Use it for logging
error_log("Request from IP: " . $clientIP);

// Use it for rate limiting
if (isRateLimited($clientIP)) {
    http_response_code(429);
    exit('Too many requests');
}

// Use it for geolocation
$country = getCountryByIP($clientIP);
```

### Handling Null Results

```php
<?php
use CurrentIP\CurrentIP;

$ip = CurrentIP::get();

if ($ip === null) {
    // Handle case where IP cannot be determined
    error_log("Unable to determine client IP");
    $ip = '0.0.0.0'; // fallback
}
```

## How It Works

The function checks the following server variables in priority order:

1. `HTTP_CLIENT_IP` - IP from shared internet connection
2. `HTTP_X_FORWARDED_FOR` - IP passed from proxy/load balancer
3. `HTTP_X_FORWARDED` - Alternative forwarded IP header
4. `HTTP_X_CLUSTER_CLIENT_IP` - IP from cluster/load balancer
5. `HTTP_FORWARDED_FOR` - Another proxy variation
6. `HTTP_FORWARDED` - Standard forwarded header (RFC 7239)
7. `REMOTE_ADDR` - Direct connection IP (most reliable without proxy)

### Proxy Chain Handling

When behind multiple proxies, `X-Forwarded-For` may contain multiple IPs:

```
X-Forwarded-For: client_ip, proxy1_ip, proxy2_ip
```

The function automatically extracts the first IP (the original client IP).

### IP Validation

The function validates IPs in two passes:

1. **Strict validation**: Excludes private and reserved IP ranges
2. **Relaxed validation**: Allows private IPs (useful for development environments)

This ensures you get valid IPs in production while maintaining functionality in local development.

## GDPR & Privacy Compliance

### ⚠️ CRITICAL LEGAL NOTICE

**IP addresses are considered personal data under GDPR and other privacy regulations.**

If you collect, store, or process IP addresses in the European Union or from EU citizens, you **MUST**:

1. ✅ **Inform users** - Clearly state in your privacy policy that you collect IP addresses
2. ✅ **Specify purpose** - Explain why you're collecting IPs (security, analytics, fraud prevention, etc.)
3. ✅ **Obtain consent** - Get explicit consent when required by law
4. ✅ **Implement retention policies** - Don't store IPs longer than necessary
5. ✅ **Secure the data** - Protect IP addresses like any other personal data
6. ✅ **Honor data subject rights** - Allow users to access, delete, or export their data

### Example Privacy Policy Statement

```
We collect your IP address for the following purposes:
- Security monitoring and fraud prevention
- Service optimization and error diagnosis
- Geographic personalization of content

Your IP address is retained for [X days/months] and is processed 
under the legal basis of [legitimate interest/consent/contract].

You have the right to access, rectify, or delete your personal data.
For more information, contact: privacy@example.com
```

### Legal Bases for IP Collection (GDPR Article 6)

- **Consent** - User explicitly agrees (requires opt-in)
- **Contract** - Necessary to provide the service
- **Legal obligation** - Required by law (e.g., tax records)
- **Legitimate interest** - Security, fraud prevention (must be justified)

### Implementation Recommendations

1. **Anonymize when possible** - Hash or truncate IPs if full address isn't needed
2. **Log retention** - Auto-delete old logs containing IPs
3. **Cookie consent integration** - Include IP collection in your consent banner
4. **Data Processing Agreement** - Ensure third-party services are GDPR-compliant

```php
<?php
// Example: Anonymize IP by removing last octet
function anonymizeIP($ip) {
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        return preg_replace('/\.\d+$/', '.0', $ip);
    }
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        return preg_replace('/:[^:]+$/', ':0', $ip);
    }
    return $ip;
}

$ip = CurrentIP::get();
$anonymizedIP = anonymizeIP($ip); // Use for analytics
```

**This library only detects IPs. You are responsible for handling them in compliance with applicable laws.**

## Security Considerations

### ⚠️ Important Security Notice

**HTTP headers can be spoofed by clients.** The `X-Forwarded-For` and similar headers can be easily manipulated by malicious users.

### Recommendations for Production

1. **Trust your infrastructure**: Only use forwarded headers if you're behind a trusted proxy/load balancer
2. **Validate proxy chain**: In high-security scenarios, verify the request came through your proxy
3. **Use allowlists**: Maintain a list of trusted proxy IPs
4. **Don't rely solely on IP**: Don't use IP addresses as the only security measure

### Enhanced Security Example

```php
<?php
use CurrentIP\CurrentIP;

function getClientIPSecure() {
    $trustedProxies = ['10.0.0.1', '10.0.0.2']; // Your load balancers
    $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? null;
    
    // Only trust forwarded headers if request came from trusted proxy
    if (in_array($remoteAddr, $trustedProxies)) {
        return CurrentIP::get();
    }
    
    // Otherwise, only use direct connection IP
    return $remoteAddr;
}
```

## Common Use Cases

### 1. Access Logging

```php
<?php
use CurrentIP\CurrentIP;

$ip = CurrentIP::get();
$logEntry = sprintf(
    "[%s] %s %s %s",
    date('Y-m-d H:i:s'),
    $ip,
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI']
);
error_log($logEntry);
```

### 2. Rate Limiting

```php
<?php
use CurrentIP\CurrentIP;

$ip = CurrentIP::get();
$key = "rate_limit:" . $ip;

// Using your cache system (Redis, Memcached, etc.)
$requests = $cache->increment($key);
if ($requests === 1) {
    $cache->expire($key, 3600); // 1 hour window
}

if ($requests > 100) {
    http_response_code(429);
    exit('Rate limit exceeded');
}
```

### 3. Geolocation

```php
<?php
use CurrentIP\CurrentIP;

$ip = CurrentIP::get();

// Use with a geolocation service
$geoData = getGeoLocation($ip);
$currency = getCurrencyByCountry($geoData['country']);
$language = getLanguageByCountry($geoData['country']);
```

### 4. Security Blocking

```php
<?php
use CurrentIP\CurrentIP;

$ip = CurrentIP::get();
$blockedIPs = ['192.168.1.100', '10.0.0.50'];

if (in_array($ip, $blockedIPs)) {
    http_response_code(403);
    exit('Access denied');
}
```

## Testing

### Local Development

When developing locally, the function will return your local IP (e.g., `127.0.0.1` or `::1`).

### Testing Behind a Proxy

To test proxy scenarios, you can manually set the headers:

```php
<?php
// Simulate proxy headers (for testing only)
$_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.1, 198.51.100.1';
$_SERVER['REMOTE_ADDR'] = '192.168.1.1';

$ip = CurrentIP::get();
echo $ip; // Should output: 203.0.113.1
```

## Troubleshooting

### Getting `127.0.0.1` or `::1` in Production

This usually means:
- Your web server configuration isn't setting the forwarded headers
- You need to configure your load balancer to pass `X-Forwarded-For`

### Getting `null`

This happens when:
- The `$_SERVER` superglobal doesn't contain any IP-related variables
- All detected values fail IP validation
- Running PHP in CLI mode without server context

### Getting Private IPs in Production

This is expected if:
- You're behind a proxy/load balancer that isn't configured to forward client IPs
- The proxy headers aren't being set correctly

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

MIT License - feel free to use this in your projects.

## Support

If you encounter any issues or have questions, please file an issue on the GitHub repository.

## Credits

Developed and maintained by [Roger Vilà](https://rogervila.es)

Built mainly with AI

---

**Note**: Always consider your specific infrastructure and security requirements when implementing IP detection in production environments.
## License

Current IP is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
