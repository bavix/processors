<?php

namespace Bavix\Processors;

use Bavix\Helpers\JSON;
use Bavix\Exceptions\NotFound\Path;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class Manager implements Dispatcher
{

    /**
     * @var string
     */
    protected $actionName = 'action';

    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * @var Factory
     */
    protected $factory;

    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * @var ResponseInterface
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
     */
    public function __construct(Factory $factory, Dispatcher $dispatcher = null)
    {
        $this->factory    = $factory;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param ResponseInterface $response
     *
     * @return Dispatcher
     */
    public function setResponse(ResponseInterface $response): Dispatcher
    {
        $this->response = $response;

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
     * @return ResponseInterface
     */
    public function response(): ResponseInterface
    {
        if (!$this->response)
        {
            $this->setResponse(
                $this->dispatcher ?
                    $this->dispatcher->response() :
                    $this->factory->response->createResponse()
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
     * @return string
     */
    public function next(): string
    {
        $this->message = $this->handle();

        if ($this->message instanceof Dispatcher)
        {
            return $this->message->next();
        }

        if (!is_object($this->message) || !($this->message instanceof ResponseInterface))
        {
            $stream   = $this->factory->stream->createStream($this->message);
            $response = $this->response()->withBody($stream);

            $this->setResponse($response);
        }

        return (string)$this;
    }

    /**
     * @return mixed
     */
    public function handle()
    {
        $action = $this->request()->getAttribute($this->actionName);

        if ($action === null)
        {
            throw new Path('Action `' . $this->actionName . '` name not found!');
        }

        $data = $this->$action(...$this->arguments());

        if (is_object($data) && $data instanceof Dispatcher)
        {
            return $data;
        }

        if (is_string($data) && class_exists($data))
        {
            return new $data($this->factory, $this);
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
        return (string)new Response($this->request(), $this->response());
    }

}
