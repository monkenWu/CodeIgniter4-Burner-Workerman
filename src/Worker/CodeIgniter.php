<?php

namespace Monken\CIBurner\Workerman\Worker;

use CodeIgniter\Events\Events;
use Config\Workerman;
use Monken\CIBurner\Workerman\Config;
use Nyholm\Psr7\ServerRequest as PsrRequest;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\Response;
use Workerman\Worker;

class CodeIgniter extends WorkerRegistrar
{
    protected Workerman $workermanConfig;

    public function __construct()
    {
        $this->workermanConfig = config('Workerman');
    }

    public function initWorker(): Worker
    {
        $config = $this->workermanConfig;

        $webWorker = new Worker(
            'http://0.0.0.0:' . $this->workermanConfig->listeningPort,
            $this->workermanConfig->ssl ? [
                'ssl' => [
                    'local_cert'        => $this->workermanConfig->sslCertFilePath,
                    'local_pk'          => $this->workermanConfig->sslKeyFilePath,
                    'verify_peer'       => $this->workermanConfig->sslVerifyPeer,
                    'allow_self_signed' => $this->workermanConfig->sslAllowSelfSigned,
                ],
            ] : []
        );
        $webWorker->name = 'CodeIgniter4';
        $webWorker->reloadable = true;
        Config::instanceSetting($webWorker, $this->workermanConfig);

        // Worker
        $webWorker->onMessage = static function (TcpConnection $connection, Request $request) use ($config) {
            $config->runtimeTcpConnection($connection, $request);

            // Static File
            $response = \Monken\CIBurner\Workerman\StaticFile::withFile($request);
            if ((null === $response) === false) {
                $connection->send($response);

                return;
            }

            // init psr7 request
            $_SERVER['HTTP_USER_AGENT'] = $request->header('User-Agent');
            $psrRequest                 = (new PsrRequest(
                $request->method(),
                $request->uri(),
                $request->header(),
                $request->rawBody(),
                $request->protocolVersion(),
                $_SERVER
            ))->withQueryParams($request->get())
                ->withCookieParams($request->cookie())
                ->withParsedBody($request->post() ?? [])
                ->withUploadedFiles($request->file() ?? []);
            unset($request);

            // process response
            if ($response === null) {
                /** @var \Psr\Http\Message\ResponseInterface */
                $response = \Monken\CIBurner\App::run($psrRequest);
            }

            $workermanResponse = new Response(
                $response->getStatusCode(),
                $response->getHeaders(),
                $response->getBody()->getContents()
            );

            $connection->send($workermanResponse);
            Events::trigger('burnerAfterSendResponse', $connection);
            \Monken\CIBurner\App::clean();
        };

        return $webWorker;
    }
}
