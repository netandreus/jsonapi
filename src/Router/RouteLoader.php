<?php

namespace JsonApi\Router;

use JsonApi\Controller\ControllerInterface;
use JsonApi\Transformer\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Routing\RouteLoaderInterface;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * @package JsonApi\Router
 */
class RouteLoader extends Loader implements ApiUrlGeneratorInterface, RouteLoaderInterface
{
    const BASE_PATH          = '';
    const ENTITY_PATH        = '/{id}';
    const RELATIONSHIPS_PATH = '/{id}/relationships/{relationship}';

    const ATTRIBUTE = '_json_api';

    private $isLoaded = false;

    /**
     * @var array
     */
    private $options = [
        'expose' => true,
    ];

    /**
     * @var array[][]
     */
    private static $routeParameters = [
        'list'                 => ['list',                self::BASE_PATH,          Request::METHOD_GET, [
        ]],
        'create'               => ['create',              self::BASE_PATH,          Request::METHOD_POST, [
        ]],
        'delete'               => ['delete',              self::ENTITY_PATH,        Request::METHOD_DELETE, [
            'id'           => '[^/]+',
        ]],
        'patch'                => ['patch',               self::ENTITY_PATH,        Request::METHOD_PATCH, [
            'id'           => '[^/]+',
        ]],
        'get'                  => ['fetch',               self::ENTITY_PATH,        Request::METHOD_GET, [
            'id'           => '[^/]+',
        ]],
        'relationships'        => ['relationships',       self::RELATIONSHIPS_PATH, Request::METHOD_GET, [
            'id'           => '[^/]+',
            'relationship' => '[^/]+',
        ]],
        'relationships_delete' => ['relationshipsDelete', self::RELATIONSHIPS_PATH, Request::METHOD_DELETE, [
            'id'           => '[^/]+',
            'relationship' => '[^/]+',
        ]],
        'relationships_patch'  => ['relationshipsPatch',  self::RELATIONSHIPS_PATH, Request::METHOD_PATCH, [
            'id'           => '[^/]+',
            'relationship' => '[^/]+',
        ]],
        'relationships_post'   => ['relationshipsPost',   self::RELATIONSHIPS_PATH, Request::METHOD_POST, [
            'id'           => '[^/]+',
            'relationship' => '[^/]+',
        ]],
    ];

    /**
     * @var ControllerInterface[]
     */
    private $controllerList;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $schemes;

    /**
     * @var string|null
     */
    private $host;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var string|null
     */
    private $prefixUrl = null;

    /**
     * @var string
     */
    private $scheme;

    /**
     * @param RequestStack $requestStack
     * @param string $scheme
     * @param string $host
     * @param string $path
     * @param string $name
     * @param ControllerInterface[] $controllerList
     */
    public function __construct(
        RequestStack $requestStack,
        ?string $scheme,
        ?string $host,
        string $path,
        string $name,
        array $controllerList
    ) {
        $this->scheme = $scheme;
        $this->schemes = (array) $scheme;
        $this->host = $host;
        $this->path = $path;
        $this->name = $name;
        foreach ($controllerList as $serviceId => $controller) {
            $this->controllerList[$serviceId] = $controller;
        }
        $this->requestStack = $requestStack;
    }

    public function import($resource, string $type = null)
    {
        return parent::import($resource, $type); // TODO: Change the autogenerated stub
    }

    /**
     * @inheritDoc
     */
    public function load($resource, string $type = null)
    {
        if (true === $this->isLoaded) {
            throw new \RuntimeException('Do not add the "jsonapi_route" loader twice');
        }

        $collection = new RouteCollection();
        foreach ($this->controllerList as $serviceId => $controller) {
            $type = $controller->getType();
            foreach (self::$routeParameters as $name => [$action, $path, $method, $requirements]) {
                if (method_exists($controller, $action)) {
                    $collection->add(
                        $this->name.$type.'_'.$name,
                        new Route(
                            $this->path.$type.$path,
                            ['_controller' => $serviceId.'::'.$action, 'type' => $type, self::ATTRIBUTE => true],
                            $requirements,
                            $this->options,
                            $this->host,
                            $this->schemes,
                            [$method]
                        )
                    );
                }
            }
        }
        $this->isLoaded = true;
        return $collection;
    }

    public function supports($resource, string $type = null)
    {
        return 'jsonapi_route' === $type;
    }

    private function getHost(): string
    {
        if ($this->scheme && $this->host) {
            return $this->scheme.'://'.$this->host;
        } elseif ($request = $this->requestStack->getMasterRequest()) {
            return $request->getScheme().'://'.$request->getHost();
        } else {
            throw new InvalidArgumentException();
        }
    }

    /**
     * @return string
     */
    private function getPrefixUrl(): string
    {
        if ($this->prefixUrl === null) {
            $this->prefixUrl = $this->getHost().$this->path;
        }
        return $this->prefixUrl;
    }

    /**
     * @inheritDoc
     */
    public function generateUrl(string $type): string
    {
        return $this->getPrefixUrl().$type;
    }

    /**
     * @inheritDoc
     */
    public function generateEntityUrl(string $type, string $id): string
    {
        return $this->getPrefixUrl().$type.'/'.$id;
    }

    /**
     * @inheritDoc
     */
    public function generateRelationshipUrl(string $type, string $id, string $name): string
    {
        return $this->getPrefixUrl().$type.'/'.$id.'/relationships/'.$name;
    }
}
