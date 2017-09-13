<?php

namespace Bavix\Processors;

use Bavix\Exceptions\NotFound\Path;
use Http\Message\MessageFactory;
use Http\Message\ResponseFactory;
use Http\Message\StreamFactory;
use Http\Message\UriFactory;
use Interop\Http\Factory\ServerRequestFactoryInterface;

/**
 * Class Factory
 *
 * @package Bavix\Processors
 *
 * @property-read MessageFactory                $message
 * @property-read ServerRequestFactoryInterface $request
 * @property-read ResponseFactory               $response
 * @property-read StreamFactory                 $stream
 * @property-read UriFactory                    $uri
 */
class Factory
{

    protected $map = [];

    /**
     * @var array
     */
    protected $factories = [
        'message'  => MessageFactory::class,
        'request'  => ServerRequestFactoryInterface::class,
        'response' => ResponseFactory::class,
        'stream'   => StreamFactory::class,
        'uri'      => UriFactory::class,
    ];

    public function __get(string $name)
    {
        if (!isset($this->map[$name]))
        {
            $class            = $this->factories[$name];
            $this->map[$name] = new $class();
        }

        return $this->map[$name];
    }

    public function __set(string $name, string $value): self
    {
        if (isset($this->factories[$name]))
        {
            throw new Path('Key `' . $name . '` not found');
        }

        if (isset($this->map[$name]))
        {
            throw new Path('It is impossible to change factory `' . $name . '` for the created class copy!');
        }

        $this->factories[$name] = $value;

        return $this;
    }

    public function __isset(string $name): bool
    {
        return isset($this->map[$name]);
    }

}
