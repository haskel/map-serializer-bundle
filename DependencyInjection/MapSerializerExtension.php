<?php
namespace Haskel\MapSerializerBundle\DependencyInjection;

use Haskel\SchemaSerializer\EntityExtractor\ExtractorGenerator;
use Haskel\SchemaSerializer\Exception\ExtractorGeneratorException;
use Haskel\SchemaSerializer\Formatter\DatetimeFormatter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

class MapSerializerExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('listeners.yaml');
        $loader->load('serializer.yaml');


        $serializer = $container->getDefinition('haskel.map_serializer.serializer');
        $datetimeFormatter = $container->setDefinition('haskel.map_serializer.formatter.datetime', new Definition(DatetimeFormatter::class));
        $serializer->addMethodCall('addFormatter', [$datetimeFormatter]);

        $generator = new ExtractorGenerator('MapSerializerCache');
        $projectDir = $container->getParameter('kernel.project_dir');
        $cacheDirectory = $projectDir . "/var/cache/map_serializer";
        $serializer->addMethodCall('setExtractorsDir', [$cacheDirectory]);

        $finder = new Finder();
        $finder->files()->in($projectDir . "/config/map_serializer");
        foreach ($finder as $file) {
            $typeSchemas = Yaml::parseFile($file->getRealPath());
            foreach ($typeSchemas as $type => $schemas) {
                foreach ($schemas as $schemaName => $schema) {
                    $serializer->addMethodCall('addSchema', [$type, $schemaName, $schema]);

                    try {
                        $generated = $generator->generate($type, $schemaName, $schema);
                        $generated->saveFile($cacheDirectory);
                        $serializer->addMethodCall('addExtractor', [$type, $schemaName, $generated->getFullClassName()]);
                    } catch (ExtractorGeneratorException $e) {

                    }
                }
            }
        }
    }

}