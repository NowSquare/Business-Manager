<?php
namespace Czim\FileHandling\Variant;

use Czim\FileHandling\Contracts\Variant\VariantStrategyFactoryInterface;
use Czim\FileHandling\Contracts\Variant\VariantStrategyInterface;
use Psr\Container\ContainerInterface;
use RuntimeException;

/**
 * Class VariantStrategyFactory
 *
 * Creates instances of variant manipulation strategies.
 */
class VariantStrategyFactory implements VariantStrategyFactoryInterface
{
    const CONFIG_ALIASES = 'aliases';

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $config = [];


    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    /**
     * Sets the configuration for the factory.
     *
     * @param array $config
     * @return $this
     */
    public function setConfig(array $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Returns strategy instance.
     *
     * @param string $strategy  strategy class or alias
     * @param array  $options   options for the strategy
     * @return VariantStrategyInterface
     */
    public function make($strategy, array $options = [])
    {
        $instance = $this->instantiateClass(
            $this->resolveStrategyClassName($strategy)
        );

        if ( ! ($instance instanceof VariantStrategyInterface)) {
            throw new RuntimeException("Variant strategy created for '{$strategy}' is of incorrect type");
        }

        return $instance->setOptions($options);
    }

    /**
     * @param string $strategy
     * @return string
     */
    protected function resolveStrategyClassName($strategy)
    {
        if (    array_key_exists(static::CONFIG_ALIASES, $this->config)
            &&  array_key_exists($strategy, $this->config[ static::CONFIG_ALIASES ])
        ) {
            $strategyClass = $this->config[ static::CONFIG_ALIASES ][ $strategy ];
        } else {
            $strategyClass = $strategy;
        }

        if ( ! $this->container->has($strategyClass)) {
            throw new RuntimeException("Cannot resolve variant strategy '{$strategy}'");
        }

        return $strategyClass;
    }

    /**
     * @param string $class
     * @return object
     */
    protected function instantiateClass($class)
    {
        return $this->container->get($class);
    }

}
