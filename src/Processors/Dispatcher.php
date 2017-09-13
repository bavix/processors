<?php

namespace Bavix\Processors;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface Dispatcher
{

    /**
     * Dispatcher constructor.
     *
     * @param Factory $factory
     * @param Dispatcher|null $dispatcher
     */
    public function __construct(Factory $factory, self $dispatcher = null);

    /**
     * @param ResponseInterface $response
     *
     * @return self
     */
    public function setResponse(ResponseInterface $response): self;

    /**
     * @param ServerRequestInterface $request
     *
     * @return self
     */
    public function setRequest(ServerRequestInterface $request): self;

    /**
     * @return ResponseInterface
     */
    public function response(): ResponseInterface;

    /**
     * @return ServerRequestInterface
     */
    public function request(): ServerRequestInterface;

    /**
     * @return mixed
     */
    public function handle();

    /**
     * @return array
     */
    public function arguments(): array;

    /**
     * @return int
     */
    public function jsonOptions(): int;

    /**
     * @return string
     */
    public function __toString(): string;

    /**
     * @return string
     */
    public function next(): string;

}
