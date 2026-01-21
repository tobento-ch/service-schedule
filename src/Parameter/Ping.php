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

namespace Tobento\Service\Schedule\Parameter;

use Psr\Container\ContainerInterface;
use Tobento\Service\Schedule\Task\PingTask;
use Tobento\Service\Schedule\TaskInterface;
use Tobento\Service\Schedule\TaskResultInterface;

/**
 * Ping.
 */
class Ping extends Parameter implements BeforeTaskHandler, AfterTaskHandler, FailedTaskHandler
{
    /**
     * Create a new Ping.
     *
     * @param string $uri
     * @param string $method
     * @param array $query
     * @param array $headers
     * @param string|null $body
     * @param array $handle
     */
    public function __construct(
        protected string $uri,
        protected string $method = 'GET',
        protected array $query = [],
        protected array $headers = [],
        protected string|null $body = null,
        protected array $handle = ['before', 'after', 'failed'],
    ) {}

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
     * Returns the before task handler.
     *
     * @return callable
     */
    public function getBeforeTaskHandler(): callable
    {
        return [$this, 'beforeTask'];
    }
    
    /**
     * Returns the after task handler.
     *
     * @return callable
     */
    public function getAfterTaskHandler(): callable
    {
        return [$this, 'afterTask'];
    }
    
    /**
     * Returns the failed task handler.
     *
     * @return callable
     */
    public function getFailedTaskHandler(): callable
    {
        return [$this, 'failedTask'];
    }
    
    /**
     * Before task.
     *
     * @param TaskInterface $task
     * @param ContainerInterface $container
     * @return void
     */
    public function beforeTask(TaskInterface $task, ContainerInterface $container): void
    {
        if (!in_array('before', $this->handle)) {
            return;
        }
        
        $this->runPing(status: 'Starting', container: $container);
    }
    
    /**
     * After task.
     *
     * @param TaskResultInterface $result
     * @param ContainerInterface $container
     * @return void
     */
    public function afterTask(TaskResultInterface $result, ContainerInterface $container): void
    {
        if (!in_array('after', $this->handle)) {
            return;
        }
        
        $this->runPing(status: 'Success', container: $container);
    }
    
    /**
     * Failed task.
     *
     * @param TaskResultInterface $result
     * @param ContainerInterface $container
     * @return void
     */
    public function failedTask(TaskResultInterface $result, ContainerInterface $container): void
    {
        if (!in_array('failed', $this->handle)) {
            return;
        }
        
        $this->runPing(status: 'Failed', container: $container);
    }
    
    /**
     * Run the ping.
     *
     * @param string $status
     * @param ContainerInterface $container
     * @return void
     */
    protected function runPing(string $status, ContainerInterface $container): void
    {
        $headers = $this->headers;
        $headers['X-Task-Status'] = $status;

        $task = new PingTask(
            uri: $this->getUri(),
            method: $this->getMethod(),
            query: $this->getQuery(),
            headers: $headers,
            body: $this->getBody(),
        );

        $task->processTask($container);
    }
}