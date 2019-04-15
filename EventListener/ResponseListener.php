<?php

namespace Haskel\MapSerializerBundle\EventListener;

use Haskel\MapSerializerBundle\Annotation\Schema;
use Haskel\SchemaSerializer\EntityExtractor\ExtractorGenerator;
use Haskel\SchemaSerializer\Exception\ExtractorGeneratorException;
use Haskel\SchemaSerializer\Formatter\DatetimeFormatter;
use Haskel\SchemaSerializer\Serializer;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\Yaml\Yaml;

class ResponseListener
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var string
     */
    private $projectDir;

    /**
     * @param Serializer $serializer
     */
    public function __construct(Serializer $serializer, $projectDir)
    {
        $this->serializer = $serializer;
        $this->projectDir = $projectDir;
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $result  = $event->getControllerResult();
        $request = $event->getRequest();
        $schema  = 'default';
        if ($request->attributes->has(Schema::ATTR)) {
            $schema = $request->attributes->get(Schema::ATTR);
        }
        $response = json_encode($this->serializer->serialize($result, $schema));
        $event->setResponse(new JsonResponse($response, 200, [], true));
    }
}