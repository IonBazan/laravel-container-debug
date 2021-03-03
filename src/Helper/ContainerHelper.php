<?php

namespace IonBazan\Laravel\ContainerDebug\Helper;

use Illuminate\Container\Container;
use ReflectionClass;

class ContainerHelper
{
    /**
     * @var Container
     */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * @return string[]
     */
    public function getTaggedServices(string $tag): array
    {
        $tags = $this->getProtectedProperty('tags');
        $tagged = $tags[$tag] ?? [];
        sort($tagged);

        return $tagged;
    }

    /**
     * @return string[]
     */
    public function getAllTags(): array
    {
        $tags = array_keys($this->getProtectedProperty('tags'));
        sort($tags);

        return $tags;
    }

    /**
     * @return string[]
     */
    public function getAllServices(bool $withAliases = true): array
    {
        $serviceIds = array_keys($this->container->getBindings());

        if ($withAliases) {
            $serviceIds = array_merge($serviceIds, $this->getAliases());
        }
        sort($serviceIds);

        return $serviceIds;
    }

    /**
     * @return string[]
     */
    public function getAliases(): array
    {
        $aliasIds = array_keys($this->getProtectedProperty('aliases'));
        sort($aliasIds);

        return $aliasIds;
    }

    /**
     * @return string[]
     */
    public function getServiceTags(string $serviceName): array
    {
        $tags = array_keys(array_filter(
                $this->getProtectedProperty('tags'),
                static function (array $services) use ($serviceName) {
                    return in_array($serviceName, $services, true);
                })
        );
        sort($tags);

        return $tags;
    }

    public function getClassNameDescription(string $id): string
    {
        if ($this->container->isAlias($id)) {
            $alias = $this->getProtectedProperty('aliases')[$id];

            return sprintf('alias for "%s"', $alias);
        }

        try {
            $instance = $this->container->make($id);

            if (!\is_object($instance)) {
                return sprintf('<%s> %s', \gettype($instance), \is_array($instance) ? json_encode($instance) : $instance);
            }

            return \get_class($instance);
        } catch (\Throwable $e) {
            return 'N/A';
        }
    }

    /**
     * @return mixed
     *
     * @throws \ReflectionException
     */
    private function getProtectedProperty(string $propertyName)
    {
        $reflection = new ReflectionClass($this->container);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($this->container);
    }
}
