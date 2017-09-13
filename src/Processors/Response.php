<?php

namespace Bavix\Processors;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Response
{

    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * @var MessageInterface
     */
    protected $message;

    /**
     * Response constructor.
     *
     * @param ServerRequestInterface  $request
     * @param MessageInterface $response
     */
    public function __construct(ServerRequestInterface $request, MessageInterface $response)
    {
        $this->request = $request;
        $this->message = $response;
    }

    protected function status(): void
    {
        if ($this->message instanceof ResponseInterface)
        {
            $code   = $this->message->getStatusCode();
            $reason = $this->message->getReasonPhrase();

            if (!empty($reason))
            {
                $version = $this->message->getProtocolVersion();
                \header('HTTP/' . $version . ' ' . $code . ' ' . $reason);
            }

            \http_response_code($code);
        }
    }

    protected function headers(): void
    {
        \header_remove();

        foreach ($this->message->getHeaders() as $name => $value)
        {
            \header($name . ': ' . $this->message->getHeaderLine($name));
        }
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $this->headers();
        $this->status();

        return (string)$this->message->getBody();
    }

}
