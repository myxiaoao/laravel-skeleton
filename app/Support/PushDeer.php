<?php

namespace App\Support;

use GuzzleHttp\Middleware;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Utils;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @see http://www.pushdeer.com/dev.html
 */
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
                ['limit' => $limit],
                ['limit' => 'int']
            )
        );
    }

    protected function validateConfig(array $config): array
    {
        return array_merge(
            [
                'http_options' => [],
                'base_url' => 'https://api2.pushdeer.com',
            ],
            $this->validateData($config, [
                'http_options' => 'array',
                'http_options.allow_redirects' => 'bool|array',
                'http_options.auth' => 'array|string|nullable',
                'http_options.body' => 'string|resource|\Psr\Http\Message\StreamInterface',
                'http_options.cert' => 'string|array',
                'http_options.cookies' => '\GuzzleHttp\Cookie\CookieJarInterface',
                'http_options.connect_timeout' => 'numeric',
                'http_options.debug' => 'bool|resource',
                'http_options.decode_content' => 'string|bool',
                'http_options.delay' => 'numeric',
                'http_options.expect' => 'bool|integer',
                'http_options.form_params' => 'array',
                'http_options.headers' => 'array',
                'http_options.http_errors' => 'bool',
                'http_options.idn_conversion' => 'bool',
                'http_options.json' => 'nullable|string|integer|numeric|array|object',
                'http_options.multipart' => 'array',
                'http_options.on_headers' => 'callable',
                'http_options.on_stats' => 'callable',
                'http_options.proxy' => 'string|array',
                'http_options.query' => 'array|string',
                'http_options.sink' => 'string|resource|\Psr\Http\Message\StreamInterface',
                'http_options.ssl_key' => 'string|array',
                'http_options.stream' => 'bool',
                'http_options.synchronous' => 'bool',
                'http_options.verify' => 'bool|string',
                'http_options.timeout' => 'numeric',
                'http_options.version' => 'string|numeric',

                'base_url' => 'required|url',
                'key' => 'required|string',
                'token' => 'required|string',
            ])
        );
    }

    protected function buildPendingRequest(array $config): PendingRequest
    {
        return Http::baseUrl($config['base_url'])
            ->throw()
            ->asJson()
            ->withOptions($config['http_options'])
            ->withOptions([
                'json' => $data = [
                    'token' => $config['token'],
                    'pushkey' => $config['key'],
                ],
                'form_params' => $data,
                'query' => $data,
            ])
            ->withMiddleware(function (callable $handler) use ($config): callable {
                return function (RequestInterface $request, array $options) use ($config, $handler) {
                    $options['laravel_data']['pushkey'] = $config['key'];
                    $request->withHeader('X-Timestamp', (string) microtime(true));

                    // 修改请求
                    Utils::modifyRequest($request, []);

                    /** @var \GuzzleHttp\Promise\PromiseInterface $promise */
                    $promise = $handler($request, $options);

                    return $promise->then(function (ResponseInterface $response) {
                        return $response->withHeader('X-Timestamp', (string) microtime(true));
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
