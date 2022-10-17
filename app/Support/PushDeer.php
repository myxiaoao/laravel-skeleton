<?php

namespace App\Support;

use GuzzleHttp\Middleware;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class PushDeer extends FoundationSdk
{
    public function messagePush(string $text, string $desp = '', string $type = 'markdown'): Response
    {
        return $this->pendingRequest->post(
            'message/push',
            $this->validateData(
                [
                    'text' => $text,
                    'desp' => $desp,
                    'type' => $type,
                ],
                [
                    'text' => 'required|string',
                    'desp' => 'string',
                    'type' => 'in:markdown,text,image',
                ]
            )
        );
    }

    public function messageList(int $limit = 10): Response
    {
        return $this->pendingRequest->post(
            'message/list',
            $this->validateData(
                [
                    'limit' => $limit,
                ],
                [
                    'limit' => 'int',
                ]
            )
        );
    }

    protected function validateConfig(array $config): array
    {
        return $this->validateData($config, [
            'options' => 'array',
            'options.allow_redirects' => 'bool|array',
            'options.auth' => 'array|string|nullable',
            'options.body' => 'string|resource|\Psr\Http\Message\StreamInterface',
            'options.cert' => 'string|array',
            'options.cookies' => '\GuzzleHttp\Cookie\CookieJarInterface',
            'options.connect_timeout' => 'numeric',
            'options.debug' => 'bool|resource',
            'options.decode_content' => 'string|bool',
            'options.delay' => 'numeric',
            'options.expect' => 'bool|integer',
            'options.form_params' => 'array',
            'options.headers' => 'array',
            'options.http_errors' => 'bool',
            'options.idn_conversion' => 'bool',
            'options.json' => 'nullable|string|integer|numeric|array|object',
            'options.multipart' => 'array',
            'options.on_headers' => 'callable',
            'options.on_stats' => 'callable',
            'options.proxy' => 'string|array',
            'options.query' => 'array|string',
            'options.sink' => 'string|resource|\Psr\Http\Message\StreamInterface',
            'options.ssl_key' => 'string|array',
            'options.stream' => 'bool',
            'options.synchronous' => 'bool',
            'options.verify' => 'bool|string',
            'options.timeout' => 'numeric',
            'options.version' => 'string|numeric',

            'token' => 'required|string',
            'key' => 'required|string',
            'base_url' => 'required|url'
        ]);
    }

    protected function buildPendingRequest(array $config): PendingRequest
    {
        return Http::withOptions($config['options'])
            ->baseUrl($config['base_url'])
            ->asJson()
            ->withOptions([
                'json' => $data = [
                    'token' => $this->config['token'],
                    'pushkey' => $this->config['key']
                ],
                'form_params' => $data,
                'query' => $data
            ])
            ->withMiddleware(function (callable $handler): callable {
                return function (RequestInterface $request, array $options) use ($handler) {
                    $options['laravel_data']['pushkey'] = $this->config['key'];
                    $request->withHeader('X-Timestamp', microtime(true));

                    /** @var \GuzzleHttp\Promise\PromiseInterface $promise */
                    $promise = $handler($request, $options);

                    return $promise->then(function (ResponseInterface $response) {
                        return $response->withHeader('X-Timestamp', microtime(true));
                    });
                };
            })
            ->withMiddleware(Middleware::mapRequest(function (RequestInterface $request) {
                return $request->withHeader('X-Date-Time', now()->toDateTimeString());
            }))
            ->withMiddleware(Middleware::mapResponse(function (ResponseInterface $response) {
                return $response->withHeader('X-Date-Time', now()->toDateTimeString());
            }))
            ->withMiddleware(Middleware::tap(
                function (RequestInterface $request, array $options) {
                },
                function (RequestInterface $request, array $options, PromiseInterface $promise) {
                }
            ));
    }
}