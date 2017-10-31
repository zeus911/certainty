<?php
namespace ParagonIE\Certainty\Tests;


use ParagonIE\Certainty\RemoteFetch;
use PHPUnit\Framework\TestCase;

class RemoteFetchTest extends TestCase
{
    /** @var string */
    protected $dir;

    public function setUp()
    {
        if (\getenv('TRAVIS') && PHP_VERSION_ID < 70200) {
            $this->markTestSkipped('Unknown GnuTLS errors');
        }
        $this->dir = __DIR__ . '/static/data-remote';
        if (!\is_dir($this->dir)) {
            \mkdir($this->dir);
        }
    }

    public function tearDown()
    {
        \unlink($this->dir . '/ca-certs.json');
        \unlink($this->dir . '/ca-certs.cache');
        foreach(\glob($this->dir . '/*.pem') as $f) {
            $real = \realpath($f);
            if (\strpos($real, $this->dir) === 0) {
                \unlink($f);
            }
        }
    }

    /**
     * @covers RemoteFetch
     */
    public function testRemoteFetch()
    {
        $this->assertFalse(\file_exists($this->dir . '/ca-certs.json'));
        $fetch = new RemoteFetch($this->dir);
        $fetch->getLatestBundle();
        $this->assertTrue(\file_exists($this->dir . '/ca-certs.json'));

        // Force a cache expiration
        \file_put_contents(
            $this->dir . '/ca-certs.cache',
            (new \DateTime())
                ->sub(new \DateInterval('PT06H'))
                ->format(\DateTime::ATOM)
        );
        $fetch->setCacheTimeout(new \DateInterval('PT01M'));
        $this->assertTrue($fetch->cacheExpired());
    }
}
