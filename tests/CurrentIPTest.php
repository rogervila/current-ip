<?php

namespace Tests\CurrentIP;

use CurrentIP\CurrentIP;
use PHPUnit\Framework\TestCase;

final class CurrentIP extends TestCase
{
    public function test_it_returns_ip(): void
    {
        $ip = CurrentIP::get();

        $this->assertNotNull($ip);
    }
}
