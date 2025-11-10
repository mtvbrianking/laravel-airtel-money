<?php

declare(strict_types=1);

namespace Bmatovu\AirtelMoney\Support;

use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Str;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

final class GuzzleHttpLogMiddleware
{
    private LoggerInterface $logger;

    private array $mask = ['Authorization', 'Cookie', 'Set-Cookie', 'X-Xsrf-Token'];

    private array $only = ['Authorization', 'Accept', 'X-Request-Id', 'Content-Type', 'Content-Length', 'Connection'];

    private array $hide = ['User-Agent', 'Host', 'Date', 'Postman-Token', 'Php-Auth-Pw', 'Php-Auth-User'];

    private array $skip = ['/token'];

    private int $size = 0;

    private string|float $start = 0;

    public function __construct()
    {
        $this->start = microtime(true);

        $app = Container::getInstance();

        $this->logger = $app->make(LoggerInterface::class);

        $config = $app->make(Repository::class);

        $this->mask = array_map('strtolower', $config->get('logging.http.mask', $this->mask));
        $this->only = array_map('strtolower', $config->get('logging.http.only', $this->only));
        $this->hide = array_map('strtolower', $config->get('logging.http.hide', $this->hide));
        $this->skip = $config->get('logging.http.skip', $this->skip);
        $this->size = $config->get('logging.http.size', $this->size);
    }

    // --- Fluent Configuration API ---

    public function useLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    public function usemask(array $mask): self
    {
        $this->mask = array_map('strtolower', $mask);

        return $this;
    }

    public function useOnly(array $only): self
    {
        $this->only = $only ? array_map('strtolower', $only) : null;

        return $this;
    }

    public function useHide(array $hide): self
    {
        $this->hide = $hide ? array_map('strtolower', $hide) : null;

        return $this;
    }

    public function useSkip(array $skip): self
    {
        $this->skip = $skip;

        return $this;
    }

    public function useSize(int $length): self
    {
        $this->size = $length;

        return $this;
    }

    // --- Middleware Entry Point ---

    public function __invoke(callable $handler): callable
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            // $start = microtime(true);
            $requestId = $_SERVER['REQUEST_ID'] ?? Str::random(8);
            $request = $request->withHeader('X-Request-Id', $requestId);

            $maskRequest = $this->filterAndMaskRequest($request);

            $this->logger->info(sprintf(
                'HTTP_OUT [Request] %s %s HTTP/%s',
                $maskRequest->getMethod(),
                $maskRequest->getUri(),
                $maskRequest->getProtocolVersion()
            ));

            if (! $this->shouldSkip($request->getUri())) {
                $this->logger->debug('HTTP_OUT [Request] Headers', $this->formatHeaders($maskRequest));
                $this->logger->debug('HTTP_OUT [Request] Body '.$this->trimBody((string) $maskRequest->getBody()));
            }

            return $handler($request, $options)->then(
                function (ResponseInterface $response) use ($request): ResponseInterface {
                    $duration = (int) (microtime(true) - $this->start) * 1000;
                    // $duration = (int) (microtime(true) - LARAVEL_START) * 1000;

                    $maskResponse = $this->filterAndMaskResponse($response);

                    $this->logger->info(sprintf(
                        'HTTP_OUT [Response] HTTP/%s %s %s %dms',
                        $maskResponse->getProtocolVersion(),
                        $maskResponse->getStatusCode(),
                        Response::$statusTexts[$maskResponse->getStatusCode()] ?? '',
                        $duration
                    ));

                    if (! $this->shouldSkip($request->getUri())) {
                        $this->logger->debug('HTTP_OUT [Response] Headers', $this->formatHeaders($maskResponse));
                        $this->logger->debug('HTTP_OUT [Response] Body '.$this->trimBody((string) $maskResponse->getBody()));
                    }

                    return $response;
                }
            );
        };
    }

    // --- Internals ---

    private function filterAndMaskRequest(RequestInterface $request): RequestInterface
    {
        foreach ($request->getHeaders() as $name => $values) {
            $name = strtolower((string) $name);

            if ($this->shouldRemoveHeader($name)) {
                $request = $request->withoutHeader($name);

                continue;
            }

            if (in_array($name, $this->mask, true)) {
                $request = $request->withHeader($name, ['**********']);
            }
        }

        return $request;
    }

    private function filterAndMaskResponse(ResponseInterface $response): ResponseInterface
    {
        foreach ($response->getHeaders() as $name => $values) {
            $name = strtolower((string) $name);

            if ($this->shouldRemoveHeader($name)) {
                $response = $response->withoutHeader($name);

                continue;
            }

            if (in_array($name, $this->mask, true)) {
                $response = $response->withHeader($name, ['**********']);
            }
        }

        return $response;
    }

    private function shouldRemoveHeader(string $name): bool
    {
        if ($this->hide && in_array($name, $this->hide)) {
            return true;
        }

        if ($this->only && ! in_array($name, $this->only)) {
            return true;
        }

        return false;
    }

    private function formatHeaders($message): array
    {
        $headers = [];

        foreach ($message->getHeaders() as $name => $values) {
            $headers[$name] = count($values) > 1 ? $values : $values[0];
        }

        return $headers;
    }

    private function trimBody(string $body): string
    {
        // $length = strlen($body);

        // return $length > $this->size
        //     ? substr($body, 0, $this->size).sprintf('... [truncated, %d bytes total]', $length)
        //     : $body;

        if (! $this->size) {
            return $body;
        }

        return Str::limit($body, $this->size, end: ' ... [truncated]', preserveWords: true);
    }

    private function shouldSkip(UriInterface $uri): bool
    {
        if (empty($this->skip)) {
            return false;
        }

        $path = $uri->getPath();
        foreach ($this->skip as $skipPath) {
            if (stripos($path, $skipPath) !== false) {
                return true;
            }
        }

        return false;
    }
}
