<?php declare(strict_types=1);


namespace Pinepain\SimpleRouting;

use PHPUnit\Framework\TestCase;
use Pinepain\SimpleRouting\Chunks\DynamicChunk;
use Pinepain\SimpleRouting\Chunks\StaticChunk;

class UrlGeneratorTest extends TestCase
{

    /**
     * @covers \Pinepain\SimpleRouting\UrlGenerator::setMap
     * @covers \Pinepain\SimpleRouting\UrlGenerator::getMap
     */
    public function testGetSetMap()
    {
        /** @var FormatsHandler | \PHPUnit_Framework_MockObject_MockObject $handler */
        $handler = $this->getMockBuilder(FormatsHandler::class)
                        ->disableOriginalConstructor()
                        ->getMock();

        $generator = new UrlGenerator($handler);

        $this->assertEquals([], $generator->getMap());
        $generator->setMap(['test', 'map']);
        $this->assertEquals(['test', 'map'], $generator->getMap());
    }

    /**
     * @covers \Pinepain\SimpleRouting\UrlGenerator::setMapFromRoutesCollector
     */
    public function testSetMapFromRoutes()
    {
        /** @var FormatsHandler | \PHPUnit_Framework_MockObject_MockObject $handler */
        $handler = $this->getMockBuilder(FormatsHandler::class)
                        ->disableOriginalConstructor()
                        ->getMock();

        /** @var UrlGenerator | \PHPUnit_Framework_MockObject_MockObject $generator */
        $generator = $this->getMockBuilder(UrlGenerator::class)
                          ->setMethods(['setMap'])
                          ->setConstructorArgs([$handler])
                          ->getMock();

        /** @var RoutesCollector | \PHPUnit_Framework_MockObject_MockObject $collector */
        $collector = $this->getMockBuilder(RoutesCollector::class)
                          ->setMethods(['getStaticRoutes', 'getDynamicRoutes'])
                          ->disableOriginalConstructor()
                          ->getMock();


        $collector->expects($this->exactly(1))
                  ->method('getStaticRoutes')
                  ->willReturn([
                      '/static/path' => new Route('static handler', $static_chunks = [new StaticChunk('/static/path')]),
                  ]);

        $collector->expects($this->exactly(1))
                  ->method('getDynamicRoutes')
                  ->willReturn([
                      '/dynamic/{path}' => new Route('dynamic handler', $dynamic_chunks = [
                          new StaticChunk('/dynamic/'),
                          new DynamicChunk('path'),
                      ]),
                      '/static/path'    => new Route('static handler', [new StaticChunk('static routes should have precedence over dynamic and this one should be ignore')]),
                  ]);

        $generator->expects($this->once())
                  ->method('setMap')
                  ->with(
                      [
                          'static handler' => $static_chunks,
                          'dynamic handler' => $dynamic_chunks,
                      ]
                  );

        $generator->setMapFromRoutesCollector($collector);
    }

    /**
     * @covers \Pinepain\SimpleRouting\UrlGenerator::handleStaticPart
     */
    public function testHandleStaticPart()
    {
        /** @var \Pinepain\SimpleRouting\FormatsHandler | \PHPUnit_Framework_MockObject_MockObject $handler */
        $handler = $this->getMockBuilder(FormatsHandler::class)
                        ->disableOriginalConstructor()
                        ->getMock();

        $generator = new UrlGenerator($handler);

        $this->assertEquals('', $generator->handleStaticPart(''));
        $this->assertEquals('/', $generator->handleStaticPart('/'));
        $this->assertEquals('test', $generator->handleStaticPart('test'));
        $this->assertEquals('/test', $generator->handleStaticPart('/test'));
        $this->assertEquals('/test/', $generator->handleStaticPart('/test/'));
        $this->assertEquals('/test/test', $generator->handleStaticPart('/test/test'));
        $this->assertEquals('/test/test/', $generator->handleStaticPart('/test/test/'));
        $this->assertEquals('test/test/', $generator->handleStaticPart('test/test/'));

        $this->assertEquals('test/with%20spaces', $generator->handleStaticPart('test/with spaces'));
        $this->assertEquals('test/slashes', $generator->handleStaticPart('test/slashes'));
        $this->assertEquals('test/%E4%B8%AD%E5%9B%BD/%E4%B8%AD%E5%9C%8B', $generator->handleStaticPart('test/中国/中國'));
        $this->assertEquals('test/%D0%BA%D0%B8%D1%80%D0%B8/%D0%BB%D0%BB%D0%B8%D1%86%D0%B0',
            $generator->handleStaticPart('test/кири/ллица'));
        $this->assertEquals('test/%E0%A4%B9%E0%A4%BF%E0%A4%82/%E0%A4%A6%E0%A5%80',
            $generator->handleStaticPart('test/हिं/दी'));
    }

    /**
     * @covers \Pinepain\SimpleRouting\UrlGenerator::__construct
     * @covers \Pinepain\SimpleRouting\UrlGenerator::handleParameter
     */
    public function testHandleParameter()
    {
        /** @var \Pinepain\SimpleRouting\FormatsHandler | \PHPUnit_Framework_MockObject_MockObject $handler */
        $handler = $this->getMockBuilder(FormatsHandler::class)
                        ->setMethods(['handle'])
                        ->disableOriginalConstructor()
                        ->getMock();

        $handler->expects($this->at(0))
                ->method('handle')
                ->with('format-1', 'value-1')
                ->willReturn('handled-1');

        $handler->expects($this->at(1))
                ->method('handle')
                ->with('format-2', 'value-2')
                ->willReturn('handled-2');

        $generator = new UrlGenerator($handler);

        $this->assertSame('handled-1', $generator->handleParameter('format-1', 'value-1', 'name-1'));
        $this->assertSame('handled-2', $generator->handleParameter('format-2', 'value-2', 'name-2'));
    }


    /**
     * @covers \Pinepain\SimpleRouting\UrlGenerator::generate
     */
    public function testGenerate()
    {
        /** @var UrlGenerator | \PHPUnit_Framework_MockObject_MockObject $generator */
        $generator = $this->getMockBuilder(UrlGenerator::class)
                          ->setMethods(['getMap', 'handleStaticPart', 'handleParameter'])
                          ->disableOriginalConstructor()
                          ->getMock();

        $generator->expects($this->exactly(2))
                  ->method('getMap')
                  ->willReturn(['test' => [new StaticChunk('/test')]]);

        $generator->expects($this->once())
                  ->method('handleStaticPart')
                  ->with('/test')
                  ->willReturn('/test-handled');

        $this->assertNull($generator->generate('nonexistent'));
        $this->assertEquals('/test-handled', $generator->generate('test'));


        /** @var UrlGenerator | \PHPUnit_Framework_MockObject_MockObject $generator */
        $generator = $this->getMockBuilder(UrlGenerator::class)
                          ->setMethods(['getMap', 'handleStaticPart', 'handleParameter'])
                          ->disableOriginalConstructor()
                          ->getMock();

        $generator->expects($this->any())
                  ->method('getMap')
                  ->willReturn([
                      'test' => [
                          new StaticChunk('/test'),
                          new DynamicChunk('name', 'format', 'default-value', '/delimiter/'),
                      ],
                  ]);

        $generator->expects($this->any())
                  ->method('handleStaticPart')
                  ->withConsecutive(
                      ['/test'], ['/delimiter/'],
                      ['/test'], ['/delimiter/'],
                      ['/test'], ['/delimiter/'],
                      ['/test']
                  )
                  ->willReturnOnConsecutiveCalls(
                      '/test-handled', '/delimiter-handled/',
                      '/test-handled', '/delimiter-handled/',
                      '/test-handled', '/delimiter-handled/',
                      '/test-handled'
                  );

        $generator->expects($this->any())
                  ->method('handleParameter')
                  ->withConsecutive(
                      ['format', 'new-value', 'name'],
                      ['format', 'default-value', 'name'],
                      ['format', '0', 'name']
                  )
                  ->willReturnOnConsecutiveCalls(
                      'new-value-handled',
                      'default-value-handled',
                      '0-handled'
                  );

        $this->assertEquals(
            '/test-handled/delimiter-handled/new-value-handled',
            $generator->generate('test', ['name' => 'new-value'], true)
        );
        $this->assertEquals(
            '/test-handled/delimiter-handled/default-value-handled',
            $generator->generate('test', [], true)
        );
        $this->assertEquals(
            '/test-handled/delimiter-handled/0-handled',
            $generator->generate('test', ['name' => '0'], true)
        );

        $this->assertEquals('/test-handled', $generator->generate('test'));
    }

    /**
     * @covers                   \Pinepain\SimpleRouting\UrlGenerator::generate
     *
     * @expectedException \Pinepain\SimpleRouting\Exception
     * @expectedExceptionMessage Required parameter 'name' value missed
     */
    public function testGenerateFailureDueMissedParameter()
    {
        /** @var UrlGenerator | \PHPUnit_Framework_MockObject_MockObject $generator */
        $generator = $this->getMockBuilder(UrlGenerator::class)
                          ->setMethods(['getMap', 'handleStaticPart', 'handleParameter'])
                          ->disableOriginalConstructor()
                          ->getMock();

        $generator->expects($this->any())
                  ->method('getMap')
                  ->willReturn([
                      'test' => [
                          new StaticChunk('/test'),
                          new DynamicChunk('name', 'format', false, '/delimiter/'),
                      ],
                  ]);

        $generator->generate('test');
    }

    /**
     * @covers                   \Pinepain\SimpleRouting\UrlGenerator::generate
     *
     * @expectedException \Pinepain\SimpleRouting\Exception
     * @expectedExceptionMessage Empty value provided for parameter 'name'
     */
    public function testGenerateFailureDueEmptyGivenParameter()
    {
        /** @var UrlGenerator | \PHPUnit_Framework_MockObject_MockObject $generator */
        $generator = $this->getMockBuilder(UrlGenerator::class)
                          ->setMethods(['getMap', 'handleStaticPart', 'handleParameter'])
                          ->disableOriginalConstructor()
                          ->getMock();

        $generator->expects($this->once())
                  ->method('getMap')
                  ->willReturn([
                      'test' => [
                          new StaticChunk('/test'),
                          new DynamicChunk('name', 'format', false, '/delimiter/'),
                      ],
                  ]);

        $generator->expects($this->once())
                  ->method('handleStaticPart')
                  ->with('/test')
                  ->willReturn('/test-handled');

        $generator->generate('test', ['name' => '']);
    }

    /**
     * @covers                   \Pinepain\SimpleRouting\UrlGenerator::generate
     *
     * @expectedException \Pinepain\SimpleRouting\Exception
     * @expectedExceptionMessage Empty default value for parameter 'name' set and no other value provided
     */
    public function testGenerateFailureDueEmptyDefaultParameter()
    {
        /** @var UrlGenerator | \PHPUnit_Framework_MockObject_MockObject $generator */
        $generator = $this->getMockBuilder(UrlGenerator::class)
                          ->setMethods(['getMap', 'handleStaticPart', 'handleParameter'])
                          ->disableOriginalConstructor()
                          ->getMock();

        $generator->expects($this->once())
                  ->method('getMap')
                  ->willReturn([
                      'test' => [
                          new StaticChunk('/test'),
                          new DynamicChunk('name', 'format', '', '/delimiter/'),
                      ],
                  ]);

        $generator->expects($this->once())
                  ->method('handleStaticPart')
                  ->with('/test')
                  ->willReturn('/test-handled');

        $generator->generate('test', [], true);
    }
}
