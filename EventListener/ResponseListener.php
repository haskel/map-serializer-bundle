<?php

namespace App\Http;

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
    public function __construct($projectDir)
    {
        $this->serializer = $serializer;
        $this->projectDir = $projectDir;
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $result   = $event->getControllerResult();
//        $serializer = new Serializer();
//        $serializer->addFormatter(new DatetimeFormatter());
//        $generator = new ExtractorGenerator('MapSerializerCache');

//        $cacheDirectory = $this->projectDir . "/var/cache/map_serializer";
//        $serializer->setExtractorsDir($cacheDirectory);

//        $finder = new Finder();
//        $finder->files()->in($this->projectDir . "/config/map_serializer");
//        foreach ($finder as $file) {
//            $typeSchemas = Yaml::parseFile($file->getRealPath());
//            foreach ($typeSchemas as $type => $schemas) {
//                foreach ($schemas as $schemaName => $schema) {
//                    $serializer->addSchema($type, $schemaName, $schema);
//
//                    try {
//                        $generated = $generator->generate($type, $schemaName, $schema);
//                        $generated->saveFile($cacheDirectory);
//                        $serializer->addExtractor($type, $schemaName, $generated->getFullClassName());
//                    } catch (ExtractorGeneratorException $e) {
//
//                    }
//                }
//            }
//        }


        $request = $event->getRequest();
        $schema = 'default';
        if ($request->attributes->has(Schema::ATTR)) {
            $schema = $request->attributes->get(Schema::ATTR);
        }
        $response = json_encode($this->serializer->serialize($result, $schema));
        $event->setResponse(new JsonResponse($response, 200, [], true));
    }
}