<?php

namespace JsonApi\Metadata;

use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use JsonApi\Router\ApiUrlGeneratorInterface;
use JsonApi\SecurityStrategy\SecurityStrategyInterface;
use JsonApi\Transformer\TransformerPoolInterface;

/**
 * @package JsonApi\Metadata
 */
interface MetadataContainerInterface extends TransformerPoolInterface, ApiUrlGeneratorInterface
{
    /**
     * @param string $class
     * @return ObjectManager|EntityManager
     */
    public function getEntityManager(string $class);

    /**
     * @param string $strategy
     * @param array $options
     * @return SecurityStrategyInterface
     */
    public function buildSecurityStrategy(string $strategy, array $options): SecurityStrategyInterface;
}
