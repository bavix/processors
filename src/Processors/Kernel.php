<?php

namespace Bavix\Processors;

use Bavix\Exceptions\Invalid;
use Bavix\Exceptions\Runtime;
use Psr\Http\Message\ServerRequestInterface;

class Kernel
{

    /**
     * @var array
     */
    protected $bundles = [];

    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * @var Factory
     */
    protected $factory;

    /**
     * @var string
     */
    protected $bundle;

    /**
     * Kernel constructor.
     *
     * @param Factory $factory
     */
    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @return string
     *
     * @throws Runtime
     */
    protected function bundle(): string
    {
        if (!$this->bundle)
        {
            $this->bundle = $this->request->getAttribute('bundle');

            if (!$this->bundle)
            {
                throw new Runtime('Bundle `name` is empty');
            }
        }

        return $this->bundle;
    }

    /**
     * @return Factory
     */
    protected function factory(): Factory
    {
        return $this->factory;
    }

    /**
     * @param array|\Traversable $iterate
     *
     * @return self
     */
    public function setBundles($iterate): self
    {
        foreach ($iterate as $name => $bundle)
        {
            $this->pushBundle($name, $bundle);
        }

        return $this;
    }

    /**
     * @param string $name
     * @param string $bundle
     *
     * @return $this
     *
     * @throws Invalid
     */
    public function pushBundle(string $name, string $bundle): self
    {
        if (!class_exists($bundle))
        {
            throw new Invalid('Processor ' . $bundle . ' not found');
        }

        $this->bundles[$name] = $bundle;

        return $this;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @throws Runtime
     */
    public function terminate(ServerRequestInterface $request): void
    {
        $this->request = $request;

        if (empty($this->bundles))
        {
            throw new Runtime('Empty bundles');
        }

        $bundle = $this->bundle();
        $class  = $this->bundles[$bundle];

        /**
         * @var Manager $manager
         */
        $manager = new $class($this->factory());
        $manager->setRequest($request);

        die($manager->next());
    }

}
