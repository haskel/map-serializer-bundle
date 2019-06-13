<?php
namespace Haskel\MapSerializerBundle;

use Haskel\MapSerializerBundle\DependencyInjection\FormatterCompilerPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MapSerializerBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new FormatterCompilerPass());
    }
}