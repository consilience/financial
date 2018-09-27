<?php

namespace Consilience\Iso8583\Tests\Cache;

use Consilience\Iso8583\Cache\CacheManager;
use PHPUnit\Framework\TestCase;

class CacheManagerTest extends TestCase
{
    /** @test */
    public function configureCacheDirectory()
    {
        $cacheManager = new CacheManager([
            'cacheDirectory' => '/test/cache/dir',
        ]);

        $reflection = new \ReflectionClass($cacheManager);
        $method = $reflection->getMethod('getConfiguration');
        $method->setAccessible(true);

        $this->assertEquals('/test/cache/dir', $method->invoke($cacheManager, 'cacheDirectory'));
    }

}
