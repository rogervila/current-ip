<?php

namespace Tests\CurrentIP;

use CurrentIP\CurrentIP;
use PHPUnit\Framework\TestCase;

final class CurrentIPTest extends TestCase
{
    public function test_it_returns_null_ip(): void
    {
        $ip = CurrentIP::get();

        $this->assertNull($ip);
    }

    public function test_it_returns_remote_addr_ip(): void
    {
        $_SERVER['REMOTE_ADDR'] = '1.1.1.1';

        $ip = CurrentIP::get();

        $this->assertEquals('1.1.1.1', $ip);
    }

    public function test_it_returns_remote_addr_ipv6(): void
    {
        $_SERVER['REMOTE_ADDR'] = '2001:0db8:85a3:0000:0000:8a2e:0370:7334';

        $ip = CurrentIP::get();

        $this->assertEquals('2001:0db8:85a3:0000:0000:8a2e:0370:7334', $ip);
    }

    public function test_it_returns_x_forwarded_ip(): void
    {
        $_SERVER['REMOTE_ADDR'] = '192.168.1.1';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.1';

        $ip = CurrentIP::get();

        $this->assertEquals('203.0.113.1', $ip);
    }

    public function test_it_returns_x_forwarded_ip_multiple(): void
    {
        $_SERVER['REMOTE_ADDR'] = '192.168.1.1';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.1, 198.51.100.1, 192.0.2.1';

        $ip = CurrentIP::get();

        // Should return the first (original client) IP
        $this->assertEquals('203.0.113.1', $ip);
    }
}
