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

use Tobento\Service\Schedule\TaskInterface;
use Tobento\Service\Schedule\TaskResultInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Client;

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
     * @param array $options
     * @param array $handle
     */
    public function __construct(
        protected string $uri,
        protected string $method = 'GET',
        protected array $options = [],
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
     * Returns the options.
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
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
     * @param null|ClientInterface $client
     * @return void
     */
    public function beforeTask(TaskInterface $task, null|ClientInterface $client = null): void
    {
        if (!in_array('before', $this->handle)) {
            return;
        }
        
        $client = $client ?: new Client();
        $options = $this->getOptions();
        $options['headers']['X-Task-Status'] = 'Starting';
        
        $client->request(
            method: $this->getMethod(),
            uri: $this->getUri(),
            options: $options,
        );
    }
    
    /**
     * After task.
     *
     * @param TaskInterface $task
     * @param null|ClientInterface $client
     * @return void
     */
    public function afterTask(TaskResultInterface $result, null|ClientInterface $client = null): void
    {
        if (!in_array('after', $this->handle)) {
            return;
        }
        
        $client = $client ?: new Client();
        $options = $this->getOptions();
        $options['headers']['X-Task-Status'] = 'Success';
        
        $client->request(
            method: $this->getMethod(),
            uri: $this->getUri(),
            options: $options,
        );
    }
    
    /**
     * Failed task.
     *
     * @param TaskInterface $task
     * @param null|ClientInterface $client
     * @return void
     */
    public function failedTask(TaskResultInterface $result, null|ClientInterface $client = null): void
    {
        if (!in_array('failed', $this->handle)) {
            return;
        }
        
        $client = $client ?: new Client();
        $options = $this->getOptions();
        $options['headers']['X-Task-Status'] = 'Failed';
        
        $client->request(
            method: $this->getMethod(),
            uri: $this->getUri(),
            options: $options,
        );
    }
}