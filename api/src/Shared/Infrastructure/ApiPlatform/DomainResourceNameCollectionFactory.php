<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\ApiPlatform;

use ApiPlatform\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceNameCollection;
use ApiPlatform\Metadata\Util\ReflectionClassRecursiveIterator;

readonly class DomainResourceNameCollectionFactory implements ResourceNameCollectionFactoryInterface
{
    /**
     * @param string[] $paths
     */
    public function __construct(
        private array $paths,
        private ?ResourceNameCollectionFactoryInterface $decorated = null,
    ) {
    }

    public function create(): ResourceNameCollection
    {
        $classes = [];

        if ($this->decorated) {
            foreach ($this->decorated->create() as $resourceClass) {
                $classes[$resourceClass] = true;
            }
        }

        foreach (ReflectionClassRecursiveIterator::getReflectionClassesFromDirectories($this->paths) as $className => $reflectionClass) {
            foreach ($this->findResourceClass($reflectionClass) as $resourceClass) {
                $classes[$resourceClass] = true;
            }
        }

        return new ResourceNameCollection(array_keys($classes));
    }

    private function findResourceClass(\ReflectionClass $reflectionClass): \Traversable
    {
        foreach ($reflectionClass->getAttributes(DomainOperation::class, \ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            $resource = $attribute->newInstance();
            \assert($resource instanceof DomainOperation);
            yield $resource->getClass() ?? throw new \InvalidArgumentException('The class attribute should be declared in the DomainOperation.');
        }
    }
}
