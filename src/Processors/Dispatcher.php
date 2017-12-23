<?php

namespace Bavix\Processors;

use Psr\Http\Message\MessageInterface;
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
     * @param MessageInterface $message
     *
     * @return self
     */
    public function setMessage(MessageInterface $message): self;

    /**
     * @param ServerRequestInterface $request
     *
     * @return self
     */
    public function setRequest(ServerRequestInterface $request): self;

    /**
     * @return MessageInterface
     */
    public function message(): MessageInterface;

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
     * @param string $action
     * 
     * @return string
     */
    public function next(string $action = null): string;

}
