<?php
namespace Haskel\MapSerializerBundle\DependencyInjection;

use Haskel\MapSerializer\EntityExtractor\ExtractorGenerator;
use Haskel\MapSerializer\Exception\ExtractorGeneratorException;
use Haskel\MapSerializer\Formatter\DatetimeFormatter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

class MapSerializerExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration($container->getParameter('kernel.debug'),
                                 $container->getParameter('kernel.project_dir'),
                                 $container->getParameter('kernel.cache_dir'));
    }

    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('listeners.yaml');
        $loader->load('serializer.yaml');

        $serializer = $container->getDefinition('haskel.map_serializer.serializer');
        $datetimeFormatter = $container->setDefinition('haskel.map_serializer.formatter.datetime', new Definition(DatetimeFormatter::class));
        $serializer->addMethodCall('addFormatter', [$datetimeFormatter]);

        $extractorsDir = $config['cache_dir'] . "/extractor";
        $generator = new ExtractorGenerator($config['extractor_namespace']);
        $serializer->addMethodCall('setExtractorsDir', [$extractorsDir]);

        $yamlParsedDir = $config['cache_dir'] . "/yaml_cache";
        if (!file_exists($yamlParsedDir)) {
            mkdir($yamlParsedDir, 0755, true);
        }
        $finder = new Finder();
        $finder->files()->in($config['config_dir']);
        foreach ($finder as $file) {
            $cacheFileName = md5($file->getContents());
            $cacheFile = $yamlParsedDir . "/" . $cacheFileName;
            if (file_exists($cacheFile)) {
                $typeSchemas = unserialize(file_get_contents($cacheFile));
            } else {
                $typeSchemas = Yaml::parseFile($file->getRealPath());
                file_put_contents($cacheFile, serialize($typeSchemas));
            }
            foreach ($typeSchemas as $type => $schemas) {
                foreach ($schemas as $schemaName => $schema) {
                    $serializer->addMethodCall('addSchema', [$type, $schemaName, $schema]);

                    try {
                        $generated = $generator->generate($type, $schemaName, $schema);
                        $generated->saveFile($extractorsDir);
                        $serializer->addMethodCall('addExtractor', [$type, $schemaName, $generated->getFullClassName()]);
                    } catch (ExtractorGeneratorException $e) {

                    }
                }
            }
        }

        $responseListener = $container->getDefinition('haskel.map_serializer.response_listener');
        $responseListener->addArgument($container->getDefinition('haskel.map_serializer.serializer'));
        $responseListener->addArgument($config['default_schema_name']);
    }

}