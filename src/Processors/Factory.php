<?php

namespace Bavix\Processors;

use Bavix\Exceptions\NotFound\Path;
use Http\Message\MessageFactory;
use Http\Message\StreamFactory;
use Http\Message\UriFactory;
use Interop\Http\Factory\ServerRequestFactoryInterface;

/**
 * Class Factory
 *
 * @package Bavix\Processors
 *
 * @property ServerRequestFactoryInterface $request
 * @property MessageFactory                $message
 * @property StreamFactory                 $stream
 * @property UriFactory                    $uri
 */
class Factory
{

    /**
     * @var array
     */
    protected $map = [];

    /**
     * @var array
     */
    protected $factories = [
        'request' => ServerRequestFactoryInterface::class,
        'message' => MessageFactory::class,
        'stream'  => StreamFactory::class,
        'uri'     => UriFactory::class,
    ];

    /**
     * Factory constructor.
     *
     * @param array|\Traversable $data
     */
    public function __construct($data)
    {
        foreach ($data as $name => $value)
        {
            $this->{$name} = $value;
        }
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        if (!isset($this->map[$name]))
        {
            $class            = $this->factories[$name];
            $this->map[$name] = new $class();
        }

        return $this->map[$name];
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return Factory
     */
    public function __set(string $name, string $value): self
    {
        if (!isset($this->factories[$name]))
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

    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return isset($this->map[$name]);
    }

}
