<?php

namespace Haskel\MapSerializerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * Default debug mode value.
     *
     * @var bool
     */
    private $debug;

    /**
     * @var string
     */
    private $projectDir;

    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @param bool $debug
     */
    public function __construct($debug, $projectDir, $cacheDir)
    {
        $this->debug      = (bool) $debug;
        $this->projectDir = $projectDir;
        $this->cacheDir   = $cacheDir;
    }

    /**
     * Generates the configuration tree.
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('map_serializer', 'array');

        // json_options
        // extractors
        // formatters
        $rootNode
            ->children()
                ->scalarNode('default_schema_name')->defaultValue('default')->end()
                ->scalarNode('cache_dir')->cannotBeEmpty()->defaultValue(sprintf('%s/map_serializer', $this->cacheDir))->end()
                ->scalarNode('config_dir')->cannotBeEmpty()->defaultValue(sprintf('%s/config/map_serializer', $this->projectDir))->end()
                ->scalarNode('extractor_namespace')->defaultValue('MapSerializerCache')->end()
            ->end();

        return $treeBuilder;
    }
}