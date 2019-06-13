<?php

namespace Haskel\MapSerializerBundle\DependencyInjection;

use Haskel\MapSerializer\Serializer;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class FormatterCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('haskel.map_serializer.serializer')) {
            return;
        }
        $serializer = $container->findDefinition('haskel.map_serializer.serializer');

        $formatters = $container->findTaggedServiceIds('map_serializer.formatter');
        foreach ($formatters as $id => $tags) {
            $serializer->addMethodCall('addFormatter', [new Reference($id)]);
        }
    }
}