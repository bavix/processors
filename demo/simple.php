<?php

include_once dirname(__DIR__) . '/vendor/autoload.php';

class P1 extends \Bavix\Processors\Manager
{

    public function next(): string
    {
        $this->request = $this->request()
            ->withAttribute($this->actionName, 'default');

        return parent::next();
    }

    public function default()
    {
        return P2::class;
    }
}

class P2 extends \Bavix\Processors\Manager
{
    public function next(): string
    {
        $this->request = $this->request()
            ->withAttribute($this->actionName, 'test');

        return parent::next();
    }

    public function test()
    {
        $message = $this->factory->message->createResponse();
        $stream  = Bavix\Http\Stream::create('<h1>' . __FUNCTION__);

        return $message
            ->withBody($stream);
//            ->withHeader('location', 'https://google.com')
//            ->withStatus(302);
    }
}

class Kernel extends \Bavix\Processors\Kernel
{

    public function bundle(): string
    {
        // route default bundle
        $this->request = $this->request->withAttribute('bundle', 'app');

        return parent::bundle();
    }

}

$factory = new \Bavix\Processors\Factory([
    'request' => \Bavix\Http\Factory\ServerRequestFactory::class,
    'message' => \Bavix\Http\Factory\MessageFactory::class,
    'stream'  => \Bavix\Http\Factory\StreamFactory::class,
    'uri'     => \Bavix\Http\Factory\UriFactory::class,
]);

$req     = $factory->request->createServerRequest(
    filter_input(INPUT_SERVER, 'REQUEST_METHOD') ?? 'GET',
    filter_input(INPUT_SERVER, 'REQUEST_URI') ?? '/'
);

$kernel  = new Kernel($factory);
$kernel->pushBundle('app', P1::class);
$kernel->terminate($req);
