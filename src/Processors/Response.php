<?php

namespace Bavix\Processors;

use Bavix\Context\Cookies;
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
     * @var Cookies
     */
    protected $cookies;

    /**
     * Response constructor.
     *
     * @param ServerRequestInterface  $request
     * @param MessageInterface $message
     * @param Cookies $cookies
     */
    public function __construct(ServerRequestInterface $request, MessageInterface $message, Cookies $cookies = null)
    {
        $this->request = $request;
        $this->message = $message;
        $this->cookies = $cookies;
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

    protected function cookies(): void
    {
        /**
         * @var ServerRequestInterface $message
         */
        $message = $this->message;

        if ($this->cookies && $message instanceof ServerRequestInterface)
        {
            foreach ($message->getCookieParams() as $name => $value)
            {
                $this->cookies->set($name, $value);
            }
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
        $this->cookies();
        $this->status();

        return (string)$this->message->getBody();
    }

}
