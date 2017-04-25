<?php declare(strict_types=1);

namespace Pinepain\SimpleRouting\Tests\FormatHandlers;

use PHPUnit\Framework\TestCase;
use Pinepain\SimpleRouting\FormatHandlers\Path;

class PathTest extends TestCase
{
    /**
     * @covers \Pinepain\SimpleRouting\FormatHandlers\Path::handle
     */
    public function testHandle()
    {
        $handler = new Path();

        $this->assertEquals('', $handler->handle(''));
        $this->assertEquals('', $handler->handle('/'));
        $this->assertEquals('test', $handler->handle('test'));
        $this->assertEquals('test', $handler->handle('/test'));
        $this->assertEquals('test', $handler->handle('/test/'));
        $this->assertEquals('test/test', $handler->handle('/test/test'));
        $this->assertEquals('test/test', $handler->handle('/test/test/'));
        $this->assertEquals('test/test', $handler->handle('test/test/'));

        $this->assertEquals('test/with%20spaces', $handler->handle('test/with spaces'));
        $this->assertEquals('test/slashes', $handler->handle('test/slashes'));
        $this->assertEquals('test/%E4%B8%AD%E5%9B%BD/%E4%B8%AD%E5%9C%8B', $handler->handle('test/中国/中國'));
        $this->assertEquals('test/%E0%A4%B9%E0%A4%BF%E0%A4%82/%E0%A4%A6%E0%A5%80', $handler->handle('test/हिं/दी'));
        $this->assertEquals(
            'test/%D0%BA%D0%B8%D1%80%D0%B8/%D0%BB%D0%BB%D0%B8%D1%86%D0%B0',
            $handler->handle('test/кири/ллица')
        );
    }
}
