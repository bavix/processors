<?php

namespace Bavix\Processors;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Response
{

    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * Response constructor.
     *
     * @param ServerRequestInterface  $request
     * @param ResponseInterface $response
     */
    public function __construct(ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->request  = $request;
        $this->response = $response;
    }

    protected function status(): void
    {
        $code   = $this->response->getStatusCode();
        $reason = $this->response->getReasonPhrase();

        if (!empty($reason))
        {
            $version = $this->response->getProtocolVersion();
            \header('HTTP/' . $version . ' ' . $code . ' ' . $reason);
        }

        \http_response_code($code);
    }

    protected function headers(): void
    {
        \header_remove();

        foreach ($this->response->getHeaders() as $name => $value)
        {
            \header($name . ': ' . $this->response->getHeaderLine($name));
        }
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $this->headers();
        $this->status();

        return (string)$this->response->getBody();
    }

}
