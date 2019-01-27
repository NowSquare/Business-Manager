<?php
namespace Czim\FileHandling\Test\Unit\Support\Content;

use Czim\FileHandling\Contracts\Storage\ProcessableFileInterface;
use Czim\FileHandling\Contracts\Storage\StorableFileFactoryInterface;
use Czim\FileHandling\Contracts\Storage\StorableFileInterface;
use Czim\FileHandling\Contracts\Variant\VariantStrategyFactoryInterface;
use Czim\FileHandling\Contracts\Variant\VariantStrategyInterface;
use Czim\FileHandling\Exceptions\VariantStrategyShouldNotBeAppliedException;
use Czim\FileHandling\Test\TestCase;
use Czim\FileHandling\Variant\VariantProcessor;
use Hamcrest\Matchers;
use Mockery;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class VariantProcessorTest extends TestCase
{

    /**
     * @var vfsStreamDirectory
     */
    protected $vfsRoot;

    public function setUp()
    {
        parent::setUp();

        $this->vfsRoot = vfsStream::setup('tmp');
    }

    /**
     * @test
     */
    function it_sets_a_config()
    {
        $strategyFactory = $this->getMockStrategyFactory();

        $processor = new VariantProcessor($this->getMockFileFactory(), $strategyFactory);

        $strategyFactory->shouldReceive('setConfig')->once()->with(['test' => true])->andReturnSelf();

        static::assertSame($processor, $processor->setConfig([
            VariantProcessor::CONFIG_VARIANT_FACTORY => ['test' => true],
        ]));
    }

    /**
     * @test
     */
    function it_processes_a_variant()
    {
        $fileFactory     = $this->getMockFileFactory();
        $strategyFactory = $this->getMockStrategyFactory();

        $processor = new VariantProcessor($fileFactory, $strategyFactory);

        // Prepare mock source file
        vfsStream::newFile('file')->at($this->vfsRoot)->setContent('dummy contents');
        $tmpPath = $this->vfsRoot->url() . '/file';

        $target = $this->makeMockTargetStorableFile();
        $source = $this->makeMockSourceStorableFile($tmpPath);

        $fileFactory->shouldReceive('uploaded')->once()->andReturnSelf();
        $fileFactory->shouldReceive('makeFromLocalPath')->once()->andReturn($target);

        // Prepare mock strategy
        $mockStrategy = $this->makeMockVariantStrategy();
        $strategyFactory->shouldReceive('make')->with('test-strategy')->once()->andReturn($mockStrategy);

        $variant = $processor->process($source, 'variant', [ 'test-strategy' => ['test' => 'a'] ]);

        static::assertInstanceOf(ProcessableFileInterface::class, $variant);
        static::assertEquals('text/plain', $variant->mimeType());
    }

    /**
     * @test
     * @expectedException \Czim\FileHandling\Exceptions\VariantStrategyNotAppliedException
     */
    function it_throws_an_exception_if_applying_returns_false()
    {
        $fileFactory     = $this->getMockFileFactory();
        $strategyFactory = $this->getMockStrategyFactory();

        $processor = new VariantProcessor($fileFactory, $strategyFactory);

        // Prepare mock source file
        vfsStream::newFile('file')->at($this->vfsRoot)->setContent('dummy contents');
        $tmpPath = $this->vfsRoot->url() . '/file';

        $target = $this->makeMockTargetStorableFile();
        $source = $this->makeMockSourceStorableFile($tmpPath);

        $fileFactory->shouldReceive('uploaded')->once()->andReturnSelf();
        $fileFactory->shouldReceive('makeFromLocalPath')->once()->andReturn($target);

        // Prepare mock strategy
        $mockStrategy = $this->makeMockVariantStrategy(false);
        $strategyFactory->shouldReceive('make')->with('test-strategy')->once()->andReturn($mockStrategy);

        $processor->process($source, 'variant', [ 'test-strategy' => ['test' => 'a'] ]);
    }

    /**
     * @test
     * @expectedException \Czim\FileHandling\Exceptions\VariantStrategyNotAppliedException
     */
    function it_throws_an_exception_if_strategy_should_not_be_applied_and_configured_to()
    {
        $fileFactory     = $this->getMockFileFactory();
        $strategyFactory = $this->getMockStrategyFactory();

        $processor = new VariantProcessor($fileFactory, $strategyFactory);
        $processor->setConfig([ VariantProcessor::CONFIG_FORCE_APPLY => true ]);

        // Prepare mock source file
        vfsStream::newFile('file')->at($this->vfsRoot)->setContent('dummy contents');
        $tmpPath = $this->vfsRoot->url() . '/file';

        $target = $this->makeMockTargetStorableFile();
        $source = $this->makeMockSourceStorableFile($tmpPath);

        $fileFactory->shouldReceive('uploaded')->once()->andReturnSelf();
        $fileFactory->shouldReceive('makeFromLocalPath')->once()->andReturn($target);

        // Prepare mock strategy
        $mockStrategy = $this->makeMockVariantStrategy(true, false);
        $strategyFactory->shouldReceive('make')->with('test-strategy')->once()->andReturn($mockStrategy);

        $processor->process($source, 'variant', [ 'test-strategy' => ['test' => 'a'] ]);
    }

    /**
     * @test
     */
    function it_silently_ignores_a_strategy_if_it_should_not_be_applied_for_a_mimetype()
    {
        $fileFactory     = $this->getMockFileFactory();
        $strategyFactory = $this->getMockStrategyFactory();

        $processor = new VariantProcessor($fileFactory, $strategyFactory);

        // Prepare mock source file
        vfsStream::newFile('file')->at($this->vfsRoot)->setContent('dummy contents');
        $tmpPath = $this->vfsRoot->url() . '/file';

        $target = $this->makeMockTargetStorableFile();
        $source = $this->makeMockSourceStorableFile($tmpPath);

        $fileFactory->shouldReceive('uploaded')->once()->andReturnSelf();
        $fileFactory->shouldReceive('makeFromLocalPath')->once()->andReturn($target);

        // Prepare mock strategy
        $mockStrategy = $this->makeMockVariantStrategy(true, false);
        $strategyFactory->shouldReceive('make')->with('test-strategy')->once()->andReturn($mockStrategy);

        $variant = $processor->process($source, 'variant', [ 'test-strategy' => ['test' => 'a'] ]);

        static::assertInstanceOf(ProcessableFileInterface::class, $variant);
        static::assertEquals('text/plain', $variant->mimeType());
    }



    // ------------------------------------------------------------------------------
    //      Helpers
    // ------------------------------------------------------------------------------

    /**
     * @param string $path
     * @return StorableFileInterface|Mockery\MockInterface
     */
    protected function makeMockTargetStorableFile($path = '/copy/path')
    {
        /** @var Mockery\MockInterface|StorableFileInterface $source */
        $target = Mockery::mock(StorableFileInterface::class);
        $target->shouldReceive('path')->andReturn($path);

        return $target;
    }

    /**
     * @param string $tmpPath
     * @param string $mimeType
     * @return StorableFileInterface|Mockery\MockInterface
     */
    protected function makeMockSourceStorableFile($tmpPath, $mimeType = 'text/plain')
    {
        /** @var Mockery\MockInterface|StorableFileInterface $source */
        $source = Mockery::mock(StorableFileInterface::class);
        $source->shouldReceive('path')->andReturn($tmpPath);
        $source->shouldReceive('mimeType')->andReturn($mimeType);
        $source->shouldReceive('name')->andReturn(pathinfo($tmpPath, PATHINFO_BASENAME));
        $source->shouldReceive('extension')->andReturn(pathinfo($tmpPath, PATHINFO_EXTENSION));

        $source->shouldReceive('copy')->once()
            ->with(Matchers::nonEmptyString())
            ->andReturnUsing(function ($path) {
                file_put_contents($path, 'dummy contents');
                return true;
            });

        return $source;
    }

    /**
     * @param bool  $successful
     * @param bool  $shouldApply
     * @param array $options
     * @return VariantStrategyInterface|Mockery\MockInterface
     */
    protected function makeMockVariantStrategy(
        $successful = true,
        $shouldApply = true,
        $options = ['test' => 'a']
    ) {
        $mock = Mockery::mock(VariantStrategyInterface::class);
        $mock->shouldReceive('setOptions')->once()->with($options)->andReturnSelf();

        if ($shouldApply) {
            $mock->shouldReceive('apply')->once()
                ->with(Mockery::type(ProcessableFileInterface::class))
                ->andReturnUsing(function ($file) use ($successful) {
                    return $successful ? $file : false;
                });
        } else {
            $mock->shouldReceive('apply')->once()
                ->with(Mockery::type(ProcessableFileInterface::class))
                ->andThrow(new VariantStrategyShouldNotBeAppliedException);
        }


        return $mock;
    }


    /**
     * @return Mockery\MockInterface|StorableFileFactoryInterface
     */
    protected function getMockFileFactory()
    {
        return Mockery::mock(StorableFileFactoryInterface::class);
    }

    /**
     * @return Mockery\MockInterface|VariantStrategyFactoryInterface
     */
    protected function getMockStrategyFactory()
    {
        return Mockery::mock(VariantStrategyFactoryInterface::class);
    }

}
