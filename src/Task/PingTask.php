<?php

/**
 * TOBENTO
 *
 * @copyright   Tobias Strub, TOBENTO
 * @license     MIT License, see LICENSE file distributed with this source code.
 * @author      Tobias Strub
 * @link        https://www.tobento.ch
 */

declare(strict_types=1);

namespace Tobento\Service\Schedule\Task;

use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Tobento\Service\Schedule\TaskException;
use Tobento\Service\Schedule\TaskResult;
use Tobento\Service\Schedule\TaskResultInterface;

class PingTask extends AbstractTask
{
    /**
     * @var null|callable(ResponseInterface, PingTask): void
     */
    protected $failure = null;
    
    /**
     * @var null|ResponseInterface
     */
    protected null|ResponseInterface $response = null;
    
    /**
     * Create a new PingTask.
     *
     * @param string $uri
     * @param string $method
     * @param array $query
     * @param array $headers
     * @param string|null $body
     * @param null|callable(ResponseInterface, PingTask): void $failure
     *     A callback that determines whether the ping should fail.
     *     Throw any exception inside the callback to mark the task as failed.
     *     If the callback completes without throwing an exception, the task is treated as successful.
     */
    public function __construct(
        protected string $uri,
        protected string $method = 'GET',
        protected array $query = [],
        protected array $headers = [],
        protected string|null $body = null,
        null|callable $failure = null,
    ) {
        $this->failure = $failure;
    }

    /**
     * Process the task.
     *
     * @param ContainerInterface $container
     * @return TaskResultInterface
     * @throws \Throwable
     */
    public function processTask(ContainerInterface $container): TaskResultInterface
    {
        // Resolve PSR-18 client
        $client = $container->get(ClientInterface::class);

        // Resolve PSR-17 request factory
        $factory = $container->get(RequestFactoryInterface::class);
        
        // Build URI with query parameters
        $uri = $this->uri;

        if (!empty($this->getQuery())) {
            $queryString = http_build_query($this->getQuery());
            $uri .= (str_contains($uri, '?') ? '&' : '?') . $queryString;
        }
        
        // Build request
        $request = $factory->createRequest($this->method, $uri);

        // Add headers if provided
        foreach($this->getHeaders() as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        // Add body if provided
        if ($this->getBody() !== null) {
            $streamFactory = $container->get(StreamFactoryInterface::class);
            $stream = $streamFactory->createStream($this->getBody());
            $request = $request->withBody($stream);
        }

        // Send request
        $this->response = $client->sendRequest($request);

        // Default failure rule: throw if status >= 400
        $failure = $this->failure ?? static function (ResponseInterface $response, PingTask $task): void {
            if ($response->getStatusCode() >= 400) {
                throw new TaskException(
                    task: $task,
                    message: sprintf(
                        'Ping failed with status %s',
                        $response->getStatusCode()
                    ),
                );
            }
        };
        
        $exception = null;
        
        try {
            ($failure)($this->response, $this);
        } catch (\Throwable $e) {
            $exception = $e;
        }
        
        return new TaskResult(
            task: $this,
            output: (string) $this->response->getBody(),
            exception: $exception,
        );
    }
    
    /**
     * Returns a task name.
     *
     * @return string
     */
    public function getName(): string
    {
        if (!empty($this->name)) {
            return $this->name;
        }
        
        return sprintf('Ping: [%s] %s', $this->getMethod(), $this->getUri());
    }
    
    /**
     * Returns the uri.
     *
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }
    
    /**
     * Returns the method.
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Returns the query.
     *
     * @return array
     */
    public function getQuery(): array
    {
        return $this->query;
    }

    /**
     * Returns the headers.
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
    
    /**
     * Returns the body.
     *
     * @return null|string
     */
    public function getBody(): null|string
    {
        return $this->body;
    }
    
    /**
     * Returns the failure.
     *
     * @return null|callable
     */
    public function getFailure(): null|callable
    {
        return $this->failure;
    }
    
    /**
     * Returns the response.
     *
     * @return null|ResponseInterface
     */
    public function getResponse(): null|ResponseInterface
    {
        return $this->response;
    }
}