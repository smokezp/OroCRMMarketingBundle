<?php

namespace Oro\Bundle\TrackingBundle\Tests\Unit\ImportExport;

use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\TrackingBundle\ImportExport\ContextReader;
use Symfony\Component\Filesystem\Filesystem;

class ContextReaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    protected $directory;

    /**
     * @var Filesystem
     */
    protected $fs;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $contextRegistry;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $context;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $stepExecution;

    /**
     * @var ContextReader
     */
    protected $reader;

    protected function setUp(): void
    {
        $this->contextRegistry = $this->createMock(ContextRegistry::class);
        $this->context = $this->createMock(ContextInterface::class);
        $this->stepExecution = $this->createMock(StepExecution::class);

        $this->reader = new ContextReader($this->contextRegistry);
    }

    public function testRead()
    {
        $data = [
            'name'  => 'event_name',
            'value' => 'done'
        ];

        $this->context
            ->expects($this->once())
            ->method('getOption')
            ->will($this->returnValue($data));

        $this->context
            ->expects($this->once())
            ->method('hasOption')
            ->with($this->equalTo('data'))
            ->will($this->returnValue(true));

        $this->contextRegistry
            ->expects($this->once())
            ->method('getByStepExecution')
            ->will($this->returnValue($this->context));

        $this->reader->setStepExecution($this->stepExecution);
        $result = $this->reader->read();
        $this->assertEquals($data, $result);
    }

    public function testReadFailed()
    {
        $this->expectException(\Oro\Bundle\ImportExportBundle\Exception\InvalidConfigurationException::class);
        $this->expectExceptionMessage('Configuration reader must contain "data".');

        $this->context
            ->expects($this->once())
            ->method('hasOption')
            ->with($this->equalTo('data'))
            ->will($this->returnValue(false));

        $this->contextRegistry
            ->expects($this->once())
            ->method('getByStepExecution')
            ->will($this->returnValue($this->context));

        $this->reader->setStepExecution($this->stepExecution);
        $this->reader->read();
    }
}
