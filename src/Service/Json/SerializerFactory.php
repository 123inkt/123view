<?php

declare(strict_types=1);

namespace DR\Review\Service\Json;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class SerializerFactory
{
    public function createObjectSerializer(): SerializerInterface
    {
        // create metadata factory to read annotation from the class
        $metaDataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));

        // create name converter that will convert property names based on the @SerializedName annotation
        $nameConverter = new MetadataAwareNameConverter($metaDataFactory);

        // create extractors to get PHP-Doc information
        $extractor = new PropertyInfoExtractor([], [new PhpDocExtractor(), new ReflectionExtractor()]);

        return new Serializer(
            [
                new ArrayDenormalizer(),
                new ObjectNormalizer($metaDataFactory, $nameConverter, null, $extractor)
            ],
            [new JsonEncoder()]
        );
    }
}
