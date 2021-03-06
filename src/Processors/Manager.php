<?php

namespace Bavix\Processors;

use Bavix\Context\Cookies;
use Bavix\Exceptions\Runtime;
use Bavix\Helpers\JSON;
use Bavix\Exceptions\NotFound\Path;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class Manager implements Dispatcher
{

    /**
     * @var string
     */
    protected $attribute = 'action';

    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * @var Cookies
     */
    protected $cookies;

    /**
     * @var Factory
     */
    protected $factory;

    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * @var MessageInterface
     */
    protected $response;

    /**
     * @var mixed
     */
    protected $message;

    /**
     * Manager constructor.
     *
     * @param Factory         $factory
     * @param Dispatcher|null $dispatcher
     * @param Cookies|null    $cookies
     */
    public function __construct(Factory $factory, Dispatcher $dispatcher = null, Cookies $cookies = null)
    {
        $this->factory    = $factory;
        $this->dispatcher = $dispatcher;
        $this->cookies    = $cookies;
    }

    /**
     * @param MessageInterface $message
     *
     * @return Dispatcher
     */
    public function setMessage(MessageInterface $message): Dispatcher
    {
        $this->response = $message;

        return $this;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return Dispatcher
     */
    public function setRequest(ServerRequestInterface $request): Dispatcher
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @return MessageInterface
     */
    public function message(): MessageInterface
    {
        if (!$this->response)
        {
            $this->setMessage(
                $this->dispatcher ?
                    $this->dispatcher->message() :
                    $this->factory->message->createResponse()
            );
        }

        return $this->response;
    }

    /**
     * @return ServerRequestInterface
     */
    public function request(): ServerRequestInterface
    {
        if (!$this->request)
        {
            $this->setRequest(
                $this->dispatcher ?
                    $this->dispatcher->request() :
                    $this->factory->request->createServerRequest(
                        filter_input(INPUT_SERVER, 'REQUEST_METHOD'),
                        filter_input(INPUT_SERVER, 'REQUEST_URI')
                    )
            );
        }

        return $this->request;
    }


    /**
     * @param string $action
     * 
     * @return string
     */
    public function next(string $action = null): string
    {
        $this->message = $this->handle($action);

        if ($this->message instanceof Dispatcher)
        {
            return $this->message->next();
        }

        $response = $this->message;

        if (!($response instanceof MessageInterface))
        {
            $stream   = $this->factory->stream->createStream($response);
            $response = $this->message()->withBody($stream);
        }

        $this->setMessage($response);

        return (string)$this;
    }

    /**
     * @param string $action
     * 
     * @return mixed
     */
    public function handle(string $action = null)
    {
        if (!$action)
        {
            $action = $this->request()->getAttribute($this->attribute);
        }

        if ($action === null)
        {
            throw new Path('Action `' . $this->attribute . '` name not found!');
        }

        if (!method_exists($this, $action))
        {
            throw new Runtime('Action `' . $action . '` not found');
        }

        $data = $this->$action(...$this->arguments());

        if (is_object($data) && $data instanceof Dispatcher)
        {
            return $data;
        }

        if (is_string($data) && class_exists($data))
        {
            return new $data($this->factory, $this, $this->cookies);
        }

        return $this->processing($data);
    }

    /**
     * @param mixed $data
     *
     * @return mixed
     */
    protected function processing($data)
    {
        if (\is_iterable($data))
        {
            $this->response = $this->message()
                ->withHeader('content-type', [
                    'application/json',
                    'charset=utf-8'
                ]);

            $data = JSON::encode($data, $this->jsonOptions());
        }

        return $data;
    }

    /**
     * @return int
     */
    public function jsonOptions(): int
    {
        return JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK;
    }

    /**
     * @return array
     */
    public function arguments(): array
    {
        return [$this->request()];
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)new Response($this->request(), $this->message(), $this->cookies);
    }

}
