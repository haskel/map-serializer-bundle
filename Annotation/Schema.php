<?php
namespace Haskel\MapSerializerBundle\Annotation;

/**
 * @Annotation
 */
class Schema
{
    const ATTR = 'map_serializer_schema';

    /**
     * @var string
     */
    public $name;

    public function __construct($name)
    {
        $this->name = $name;
    }
}