<?php

namespace JsonApi\Context;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use JsonApi\ContextInclude\ContextIncludeInterface;
use JsonApi\Metadata\MetadataInterface;
use JsonApi\Metadata\RegisterInterface;

/**
 * @package JsonApi
 */
interface ContextInterface extends RegisterInterface
{
    /**
     * @return MetadataInterface
     */
    public function getMetadata(): MetadataInterface;

    /**
     * @return ContextIncludeInterface
     */
    public function getInclude(): ContextIncludeInterface;

    /**
     * @return ObjectManager|EntityManager
     */
    public function getEntityManager();

    /**
     * @return ObjectRepository
     */
    public function getRepository(): ObjectRepository;

    /**
     * @return string[]
     */
    public function getMeta(): array;
}
