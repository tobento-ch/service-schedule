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

use Tobento\Service\Schedule\TaskResultInterface;
use Tobento\Service\Schedule\TaskResult;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Client;

/**
 * PingTask
 */
class PingTask extends AbstractTask
{
    /**
     * @var null|ResponseInterface
     */
    protected null|ResponseInterface $response = null;
    
    /**
     * Create a new PingTask.
     *
     * @param string $uri
     * @param string $method
     * @param array $options
     */
    public function __construct(
        protected string $uri,
        protected string $method = 'GET',
        protected array $options = [],
    ) {}

    /**
     * Process the task.
     *
     * @param ContainerInterface $container
     * @return TaskResultInterface
     * @throws \Throwable
     */
    public function processTask(ContainerInterface $container): TaskResultInterface
    {
        $client = $container->has(ClientInterface::class)
            ? $container->get(ClientInterface::class)
            : new Client();
        
        $this->response = $client->request(
            method: $this->getMethod(),
            uri: $this->getUri(),
            options: $this->getOptions(),
        );
        
        return new TaskResult(task: $this, output: (string)$this->response->getBody());
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
     * Returns the options.
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
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