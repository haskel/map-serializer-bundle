<?php

namespace Haskel\MapSerializerBundle\EventListener;

use Haskel\MapSerializerBundle\Annotation\Schema;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Doctrine\Common\Annotations\Reader;
use ReflectionClass;
use ReflectionException;
use RuntimeException;

class SchemaAnnotationListener
{
    private $annotationReader;

    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    public function onKernelController(FilterControllerEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $controllers = $event->getController();
        if (!is_array($controllers)) {
            return;
        }

        list($controller, $method) = $controllers;

        try {
            $controller = new ReflectionClass($controller);
        } catch (ReflectionException $e) {
            throw new RuntimeException('Failed to read annotation');
        }

        $classAnnotation = $this->handleClassAnnotation($controller);
        $methodAnnotation = $this->handleMethodAnnotation($controller, $method);

        if ($methodAnnotation) {
            $event->getRequest()->attributes->set(Schema::ATTR, $methodAnnotation->name['value']);
        }
    }

    /**
     * @param ReflectionClass $controller
     *
     * @return object|null
     */
    private function handleClassAnnotation(ReflectionClass $controller)
    {
        $annotation = $this->annotationReader->getClassAnnotation($controller, Schema::class);

        if ($annotation instanceof Schema) {
            return $annotation;
        }
    }

    /**
     * @param ReflectionClass $controller
     * @param string          $method
     *
     * @return object|null
     */
    private function handleMethodAnnotation(ReflectionClass $controller, string $method)
    {
        $method = $controller->getMethod($method);
        $annotation = $this->annotationReader->getMethodAnnotation($method, Schema::class);

        if ($annotation instanceof Schema) {
            return $annotation;
        }
    }
}