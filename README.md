# Schedule Service

A task schedule system for running tasks at specific intervals.

## Table of Contents

- [Getting started](#getting-started)
    - [Requirements](#requirements)
    - [Highlights](#highlights)
- [Documentation](#documentation)
    - [Usage](#usage)
        - [Scheduling Tasks](#scheduling-tasks)
        - [Running Scheduled Tasks](#running-scheduled-tasks)
    - [Tasks](#tasks)
        - [Callable Task](#callable-task)
        - [Command Task](#command-task)
        - [Invokable Task](#invokable-task)
        - [Ping Task](#ping-task)
        - [Process Task](#process-task)
    - [Task Methods](#task-methods)
        - [General Task Methods](#general-task-methods)
        - [Schedule Task Methods](#schedule-task-methods)
    - [Task Parameters](#task-parameters)
        - [After Parameter](#after-parameter)
        - [Before Parameter](#before-parameter)
        - [Failed Parameter](#failed-parameter)
        - [Mail Parameter](#mail-parameter)
        - [Monitor Parameter](#monitor-parameter)
        - [Ping Parameter](#ping-parameter)
        - [Send Result To Parameter](#send-result-to-parameter)
        - [Skip Parameter](#skip-parameter)
        - [Without Overlapping Parameter](#without-overlapping-parameter)
    - [Task Schedule](#task-schedule)
        - [Cron Expression](#cron-expression)
        - [Dates](#dates)
    - [Task Processor](#task-processor)
    - [Task Result](#task-result)
    - [Task Results](#task-results)
    - [Schedule](#schedule)
    - [Schedule Processor](#schedule-processor)
    - [Console](#console)
        - [Run Command](#run-command)
        - [List Command](#list-command)
    - [Events](#events)
    - [Learn More](#learn-more)
        - [Running Scheduled Tasks Without Console](#running-scheduled-tasks-without-console)
- [Credits](#credits)
___

# Getting started

Add the latest version of the schedule service project running this command.

```
composer require tobento/service-schedule
```

## Requirements

- PHP 8.0 or greater

## Highlights

- Framework-agnostic, will work with any project
- Decoupled design

# Documentation

## Usage

### Scheduling Tasks

To schedule a task you may use the default [Schedule](#schedule) or create your own fitting your needs.

### Running Scheduled Tasks

The most common way to run the schedule processor is a Cron job that runs the schedule:run every minute. The following should be added to your production server's crontab:

```
* * * * * cd /path-to-your-project && php console.php schedule:run >> /dev/null 2>&1
```

**Example of console.php**

Example using the [Console Service](https://github.com/tobento-ch/service-console), [Clock Service](https://github.com/tobento-ch/service-clock) and [Container Service](https://github.com/tobento-ch/service-container):

```
composer require tobento/service-console
composer require tobento/service-clock
composer require tobento/service-container
```

```
use Tobento\Service\Clock\SystemClock;
use Tobento\Service\Container\Container;
use Tobento\Service\Console\Console;
use Tobento\Service\Console\Command;
use Tobento\Service\Console\InteractorInterface;
use Tobento\Service\Console\Symfony;
use Tobento\Service\Schedule\Task;
use Tobento\Service\Schedule\Parameter;
use Tobento\Service\Schedule\TaskProcessor;
use Tobento\Service\Schedule\TaskProcessorInterface;
use Tobento\Service\Schedule\ScheduleProcessor;
use Tobento\Service\Schedule\ScheduleProcessorInterface;
use Tobento\Service\Schedule\Schedule;
use Tobento\Service\Schedule\ScheduleInterface;
use Psr\Container\ContainerInterface;
use Psr\Clock\ClockInterface;

// Container bindings:
$container = new Container();
$container->set(ClockInterface::class, new SystemClock());
$container->set(TaskProcessorInterface::class, TaskProcessor::class);
$container->set(ScheduleProcessorInterface::class, ScheduleProcessor::class);

// Schedule:
$container->set(ScheduleInterface::class, function() {
    $schedule = new Schedule(name: 'default');
    
    $schedule->task(
        (new Task\CallableTask(
            callable: static function (): string {
                // do something:
                return 'task output';
            },
        ))->name('demo')
    );
    
    return $schedule;
});

// Console:
$console = new Symfony\Console(
    name: 'app',
    container: $container,
);

$console->addCommand(\Tobento\Service\Schedule\Console\ScheduleRunCommand::class);
$console->addCommand(\Tobento\Service\Schedule\Console\ScheduleListCommand::class);
$console->run();
```

## Tasks

### Callable Task

```php
use Tobento\Service\Schedule\Task\CallableTask;
use Tobento\Service\Schedule\Task\TaskInterface;

$task = new CallableTask(
    callable: static function (SomeService $service, $option): string {
        // do something
        
        // you may return the task output or nothing at all (void):
        return 'task output';
    },
    // you may set data passed to the function:
    params: ['option' => 'value'],
);

var_dump($task instanceof TaskInterface);
// bool(true)

// Specific task methods:
$callable = $task->getCallable();
$params = $task->getParams();
```

Check out the [Task Methods](#task-methods) section to learn more about the available methods as well as the [Task Parameters](#task-parameters).

**Invokable objects**

You may consider using invokable objects that contain an ```__invoke``` method:

```php
use Tobento\Service\Schedule\Task\CallableTask;

class SampleInvokable
{
    public function __invoke(SomeService $service, $option): string
    {
        // do something
        
        // you may return the task output or nothing at all (void):
        return 'task output';
    }
}

$task = new CallableTask(
    callable: new SampleInvokable(),
    // you may set data passed to the __invoke method:
    params: ['option' => 'value'],
);
```

### Command Task

The command task may be used to run the provided command using the [Console Service](https://github.com/tobento-ch/service-console).

```php
use Tobento\Service\Schedule\Task\CommandTask;
use Tobento\Service\Schedule\Task\TaskInterface;
use Tobento\Service\Console\ConsoleInterface;
use Tobento\Service\Console\ExecutedInterface;

$task = new CommandTask(
    command: 'command:name',
    // you may set command input data:
    input: [
        // passing arguments:
        'username' => 'Tom',
        // with array value:
        'username' => ['Tom', 'Tim'],
        // passing options:
        '--some-option' => 'value',
        // with array value:
        '--some-option' => ['value'],
    ],
);

var_dump($task instanceof TaskInterface);
// bool(true)

// Specific task methods:
$command = $task->getCommand();
$input = $task->getInput();

// Returning null if task is not processed yet, otherwise the console.
$console = $task->getConsole();
// null|ConsoleInterface

// Returning null if task is not processed yet, otherwise the executed.
$executed = $task->getExecuted();
// null|ExecutedInterface
```

Check out the [Task Methods](#task-methods) section to learn more about the available methods as well as the [Task Parameters](#task-parameters).

**Requirements**

This command task requires the [**Console Service**](https://github.com/tobento-ch/service-console):

```
composer require tobento/service-console
```

### Invokable Task

You may create invokable tasks by simply extending the ```InvokableTask::class```. The ```__invoke``` method will be called when the task is being processed.

```php
use Tobento\Service\Schedule\Task\InvokableTask;
use Tobento\Service\Schedule\Task\Schedule\CronExpression;

class SampleTask extends InvokableTask
{
    public function __construct()
    {
        // you may set default parameters:
        $this->id('A unique id');
        $this->name('A task name');
        $this->description('A task description');
        
        // you may set a default schedule:
        $this->schedule(new CronExpression('* * * * *'));
        // or
        $this->cron('* * * * *');
    }
    
    public function __invoke(SomeService $service): string
    {
        // do something
        
        // you may return the task output or nothing at all (void):
        return 'task output';
    }
}

$task = (new SampleTask())->cron('* * * * *');
```

Check out the [Task Methods](#task-methods) section to learn more about the available methods as well as the [Task Parameters](#task-parameters).

### Ping Task

The ping task may be used to ping the provided URI.

```php
use Tobento\Service\Schedule\Task\PingTask;
use Tobento\Service\Schedule\Task\TaskInterface;
use Psr\Http\Message\ResponseInterface;

$task = new PingTask(
    uri: 'https://example.com/ping',
    method: 'GET', // default
    options: [],
);

var_dump($task instanceof TaskInterface);
// bool(true)

// Specific task methods:
$uri = $task->getUri();
$method = $task->getMethod();
$options = $task->getOptions();

// Returning null if task is not processed yet, otherwise the response.
$response = $task->getResponse();
// null|ResponseInterface
```

Check out the [Task Methods](#task-methods) section to learn more about the available methods as well as the [Task Parameters](#task-parameters).

**Requirements**

This ping task requires [**Guzzle, PHP HTTP client**](https://github.com/guzzle/guzzle):

```
composer require guzzlehttp/guzzle
```

### Process Task

The process task may be used to execute shell commands using the [Symfony Process Component](https://github.com/symfony/process).

```php
use Tobento\Service\Schedule\Task\ProcessTask;
use Tobento\Service\Schedule\Task\TaskInterface;
use Symfony\Component\Process\Process;

$task = new ProcessTask(
    process: '/bin/script',
);

// or with a process instance:
$task = new ProcessTask(
    process: Process::fromShellCommandline('/bin/script')
        ->setTimeout(20),
);

var_dump($task instanceof TaskInterface);
// bool(true)

// Specific task methods:
$process = $task->getProcess();
```

Check out the [Task Methods](#task-methods) section to learn more about the available methods as well as the [Task Parameters](#task-parameters).

**Requirements**

This process task requires the [Symfony Process Component](https://github.com/symfony/process):

```
composer require symfony/process
```

## Task Methods

All of the available [Tasks](#tasks) supporting the following methods.

### General Task Methods

```php
// You may set a unique task id:
$task->id('taskId');

// You may set a task name:
$task->name('A task name');

// You may set a description:
$task->description('A task description');
```

### Schedule Task Methods

```php
use Tobento\Service\Schedule\TaskScheduleInterface;
use Tobento\Service\Schedule\Task\Schedule\CronExpression;

// you may set the schedule implementing TaskScheduleInterface
$task->schedule(new CronExpression(
    expression: '* * * * *',
    // you may specify a timezone:
    timezone: 'Europe/Berlin', // string|\DateTimeZone
));

// or you may use the cron method:
$task->cron(
    expression: '* * * * *',
    // you may specify a timezone:
    timezone: 'Europe/Berlin', // string|\DateTimeZone
);
```

Check out the [Task Schedule](#task-schedule) section for the available task schedules.

You may consider using the cron generator already available:

```php
use Butschster\CronExpression\Generator;

$task->cron(Generator::create()->daily());
```

Check out the [Cron Expression Generator](https://github.com/butschster/CronExpressionGenerator) for more examples.

## Task Schedule

### Cron Expression

You may use the ```CronExpression::class``` to define at which interval you want to run the task.

```php
use Tobento\Service\Schedule\Task\Schedule\CronExpression;
use Tobento\Service\Schedule\TaskScheduleInterface;

$cron = new CronExpression(
    expression: '* * * * *',
    // you may specify a timezone:
    timezone: 'Europe/Berlin', // string|\DateTimeZone
);

var_dump($cron instanceof TaskScheduleInterface);
// bool(true)
```

You may consider using the cron generator already available:

```php
use Tobento\Service\Schedule\Task\Schedule\CronExpression;
use Butschster\CronExpression\Generator;

$cron = new CronExpression(
    expression: Generator::create()->daily(),
);
```

Check out the [Cron Expression Generator](https://github.com/butschster/CronExpressionGenerator) for more examples.

### Dates

You may use the ```Dates::class``` to run tasks at specific dates every year.

```php
use Tobento\Service\Schedule\Task\Schedule\Dates;
use Tobento\Service\Schedule\TaskScheduleInterface;

$dates = new Dates(
    // Every year yyyy-05-12 15:38
    new \DateTime('2023-05-12 15:38:45'),
    // Every year yyyy-08-24 10:15
    new \DateTimeImmutable('2023-08-24 10:15:33'),
);

var_dump($dates instanceof TaskScheduleInterface);
// bool(true)
```

## Task Parameters

You may use the available parameters providing basic features for tasks or [create custom parameters](#creating-custom-task-parameters) to add new features or customizing existing to suit your needs.

All of the available [Tasks](#tasks) supporting the following parameters and its helper methods.

### After Parameter

The after parameter executes the specified handler after the task is processed.

The ```TaskResultInterface::class``` will be passed to your handler. In addition, you may request any service being resolved (autowired) by the container passed to the [task processor](#task-processor).

```php
use Tobento\Service\Schedule\Task\CommandTask;
use Tobento\Service\Schedule\Parameter;
use Tobento\Service\Schedule\TaskResultInterface;

// any callable handler:
$task = (new CommandTask('command:name'))
    ->parameter(new Parameter\After(static function(TaskResultInterface $result, SomeService $service): void {
        // executes after the task is processed
    }))
    // or using the helper method:
    ->after(static function (TaskResultInterface $result, SomeService $service): void {
        // executes after the task is processed
    });

// or a handler implementing Parameter\AfterTaskHandler
$task = (new CommandTask('command:name'))
    ->parameter(new Parameter\After(handler: $handler))
    // or using the helper method:
    ->after(handler: $handler);
```

### Before Parameter

The before parameter executes the specified handler before the task is processed.

The ```TaskInterface::class``` will be passed to your handler. In addition, you may request any service being resolved (autowired) by the container passed to the [task processor](#task-processor).

```php
use Tobento\Service\Schedule\Task\CommandTask;
use Tobento\Service\Schedule\Parameter;
use Tobento\Service\Schedule\TaskInterface;

// any callable handler:
$task = (new CommandTask('command:name'))
    ->parameter(new Parameter\Before(static function(TaskInterface $task, SomeService $service): void {
        // executes before the task is processed
    }))
    // or using the helper method:
    ->before(static function (TaskInterface $task, SomeService $service): void {
        // executes before the task is processed
    });

// or a handler implementing Parameter\BeforeTaskHandler
$task = (new CommandTask('command:name'))
    ->parameter(new Parameter\Before(handler: $handler))
    // or using the helper method:
    ->before(handler: $handler);
```

**Skip Task**

You may throw a ```TaskSkipException::class``` if you want to prevent the task from being processed not resulting in a failure.

```php
use Tobento\Service\Schedule\Task\CommandTask;
use Tobento\Service\Schedule\TaskSkipException;

// any callable handler:
$task = (new CommandTask('command:name'))
    ->before(static function (): void {
        throw new TaskSkipException('Skipped because ...');
    });
```

### Failed Parameter

The failed parameter executes the specified handler if the task fails.

The ```TaskResultInterface::class``` will be passed to your handler. In addition, you may request any service being resolved (autowired) by the container passed to the [task processor](#task-processor).

```php
use Tobento\Service\Schedule\Task\CommandTask;
use Tobento\Service\Schedule\Parameter;
use Tobento\Service\Schedule\TaskResultInterface;

// any callable handler:
$task = (new CommandTask('command:name'))
    ->parameter(new Parameter\Failed(static function(TaskResultInterface $result, SomeService $service): void {
        // executes if the task failed
    }))
    // or using the helper method:
    ->failed(static function (TaskResultInterface $result, SomeService $service): void {
        // executes if the task failed
    });

// or a handler implementing Parameter\FailedTaskHandler
$task = (new CommandTask('command:name'))
    ->parameter(new Parameter\Failed(handler: $handler))
    // or using the helper method:
    ->failed(handler: $handler);
```

### Mail Parameter

The mail parameter may be used to send mails using the [Mail Service](https://github.com/tobento-ch/service-mail) before and/or after tasks are processed or when a task fails.

```php
use Tobento\Service\Schedule\Task\CommandTask;
use Tobento\Service\Schedule\Parameter;
use Tobento\Service\Mail\Message;

$task = (new CommandTask('command:name'))
    ->parameter(new Parameter\Mail(
        message: (new Message())->to('admin@example.com'),
        handle: ['before', 'after', 'failed'], // default
        // send mail only if task failed:
        // handle: ['failed'],
    ));
```

In addition, you may use the ```before```, ```after``` and ```failed``` helper methods:

```php
use Tobento\Service\Schedule\Task\CommandTask;
use Tobento\Service\Schedule\Parameter;
use Tobento\Service\Mail\Message;

$task = (new CommandTask('command:name'))
    ->before(new Parameter\Mail(
        message: (new Message())->to('admin@example.com'),
    ))
    ->after(new Parameter\Mail(
        message: (new Message())->to('admin@example.com'),
    ))
    ->failed(new Parameter\Mail(
        message: (new Message())->to('admin@example.com'),
    ));
```

**Requirements**

This parameter requires the [**Mail Service**](https://github.com/tobento-ch/service-mail):

First, install the mail service:

```
composer require tobento/service-mail
```

Finally, it requires the ```MailerInterface::class``` to be binded to your container passed to the [Task Processor](#task-processor):

Example using the [Container Service](https://github.com/tobento-ch/service-container):

```php
use Tobento\Service\Mail\MailerInterface;
use Tobento\Service\Schedule\TaskProcessor;
use Tobento\Service\Container\Container;

$container = new Container();
$container->set(MailerInterface::class, function() {
    // create mailer:
    return $mailer;
});

$taskProcessor = new TaskProcessor($container);
```

**Message From Address**

If you do not have defined a [default from address](https://github.com/tobento-ch/service-mail#default-addresses-and-parameters), you will need to set it:

```php
use Tobento\Service\Mail\Message;

$message = (new Message())
    ->from('from@example.com')
    ->to('admin@example.com');
```

**Message Contents**

You may set a message subject otherwise the task name will be used instead:

```php
use Tobento\Service\Mail\Message;

$message = (new Message())
    ->subject('Some task subject')
    ->to('admin@example.com');
```

A sent mail looks like:

```php
Task Status: Failed

Task ID: task-id

Task Name: Task's name

Task Description: Task's description (if any)

Task Output: Failed task's output (if any)

Task Exception: Failed task's exception stack trace (if any)
```

### Monitor Parameter

The monitor parameter may be used to monitor tasks processes such as the start time, running time and memeory usage.

```php
use Tobento\Service\Schedule\Task\CommandTask;
use Tobento\Service\Schedule\Parameter;

$task = (new CommandTask('command:name'))
    ->parameter(new Parameter\Monitor())
    // or using the helper method:
    ->monitor();
```

### Ping Parameter

The ping parameter may be used to ping the provided URI.

```php
use Tobento\Service\Schedule\Task\CommandTask;
use Tobento\Service\Schedule\Parameter;

$task = (new CommandTask('command:name'))
    ->parameter(new Parameter\Ping(
        uri: 'https://example.com/task',
        method: 'GET', // default
        options: [],
        handle: ['before', 'after', 'failed'], // default
        // pings only if task failed:
        // handle: ['failed'],
    ));
```

A ```X-Task-Status``` header will be added to the request with a ```Starting```, ```Success``` or ```Failed``` value depending on its processing state.

In addition, you may use the ```before```, ```after``` and ```failed``` helper methods:

```php
use Tobento\Service\Schedule\Task\CommandTask;
use Tobento\Service\Schedule\Parameter;

$task = (new CommandTask('command:name'))
    ->before(new Parameter\Ping(
        uri: 'https://example.com/task-before',
    ))
    ->after(new Parameter\Ping(
        uri: 'https://example.com/task-after',
    ))
    ->failed(new Parameter\Ping(
        uri: 'https://example.com/task-failed',
    ));
```

**Requirements**

This parameter requires [**Guzzle, PHP HTTP client**](https://github.com/guzzle/guzzle):

```
composer require guzzlehttp/guzzle
```

### Send Result To Parameter

The send result to parameter may be used to send the task result to the specified file.

```php
use Tobento\Service\Schedule\Task\CommandTask;
use Tobento\Service\Schedule\Parameter;
use Tobento\Service\Schedule\TaskInterface;

$task = (new CommandTask('command:name'))
    ->parameter(new Parameter\SendResultTo(
        file: 'dir/to/file.log',
        // if true sends only output, otherwise the whole result:
        onlyOutput: false, // default
        // if true appends result to file otherwise overwrites file:
        append: true, // default
        handle: ['after', 'failed'], // default
        // send result only after task is processed:
        // handle: ['after'],
    ));
```

In addition, you may use the ```after``` and ```failed``` helper methods:

```php
use Tobento\Service\Schedule\Task\CommandTask;
use Tobento\Service\Schedule\Parameter;

$task = (new CommandTask('command:name'))
    ->after(new Parameter\SendResultTo(
        file: 'dir/to/file-success.log',
    ))
    ->failed(new Parameter\SendResultTo(
        file: 'dir/to/file-failed.log',
    ));
```

### Skip Parameter

The skip parameter may be used to skip tasks if the ```skip``` parameter evalutes to ```true```. The parameter has a default priority of ```100000``` whereby it gets handled before any lower prioritized [task parameters](#task-parameters).

```php
use Tobento\Service\Schedule\Task\CommandTask;
use Tobento\Service\Schedule\Parameter;
use Tobento\Service\Schedule\TaskInterface;

// using a boolean:
$task = (new CommandTask('command:name'))
    ->parameter(new Parameter\Skip(
        skip: true,
        
        // You may specify a reason for skipping:
        reason: 'Because of ...'
    ));
    
// using a callable:
$task = (new CommandTask('command:name'))
    ->parameter(new Parameter\Skip(
        skip: static function(TaskInterface $task, SomeService $service)
            // skips if return value is true
            return true;
        },
        
        // You may specify a reason for skipping:
        reason: 'Because of ...'
    ));
    
// or using the helper method:
$task = (new CommandTask('command:name'))
    ->skip(skip: $skip, reason: 'Because of ...');
```

If using a callable, the ```TaskInterface::class``` will be passed to your callback function. In addition, you may request any service being resolved (autowired) by the container passed to the [task processor](#task-processor).

### Without Overlapping Parameter

The without overlapping parameter may be used to prevent tasks from overlapping.

```php
use Tobento\Service\Schedule\Task\CommandTask;
use Tobento\Service\Schedule\Parameter;

$task = (new CommandTask('command:name'))
    ->parameter(new Parameter\WithoutOverlapping(
        // You may set a unique id. If null it uses the task id.
        id: 'unique-task-id', // null|string
        
        // You may set a maximum expected lock duration in seconds:
        ttl: 86400, // default
    ))
    // or using the helper method:
    ->withoutOverlapping()
    // with id and ttl:
    ->withoutOverlapping(id: 'unique-task-id', ttl: 86400);
```

**Requirements**

The parameter requires a ```CacheInterface::class``` to be binded to your container passed to the [Task Processor](#task-processor):

Example using the [Cache Service](https://github.com/tobento-ch/service-cache) and [Container Service](https://github.com/tobento-ch/service-container):

```php
use Psr\SimpleCache\CacheInterface;
use Tobento\Service\Schedule\TaskProcessor;
use Tobento\Service\Container\Container;
use Tobento\Service\Cache\Simple\Psr6Cache;
use Tobento\Service\Cache\ArrayCacheItemPool;
use Tobento\Service\Clock\SystemClock;

$container = new Container();
$container->set(CacheInterface::class, function() {
    // create cache:
    return new Psr6Cache(
        pool: new ArrayCacheItemPool(
            clock: new SystemClock(),
        ),
        namespace: 'default',
        ttl: null,
    );
});

$taskProcessor = new TaskProcessor($container);
```

## Task Processor

The ```TaskProcessor::class``` is responsible for processing tasks.

```php
use Tobento\Service\Schedule\TaskProcessor;
use Tobento\Service\Schedule\TaskProcessorInterface;
use Psr\Container\ContainerInterface;

$taskProcessor = new TaskProcessor(
    container: $container // ContainerInterface
);

var_dump($taskProcessor instanceof TaskProcessorInterface);
// bool(true)
```

**Process Task**

```php
use Tobento\Service\Schedule\TaskInterface;
use Tobento\Service\Schedule\TaskResultInterface;

$result = $taskProcessor->processTask($task); // TaskInterface

var_dump($result instanceof TaskResultInterface);
// bool(true)
```

Check out the [Task Result](#task-result) to learn more.

## Task Result

```php
use Tobento\Service\Schedule\TaskResult;
use Tobento\Service\Schedule\TaskResultInterface;
use Tobento\Service\Schedule\TaskInterface;

$result = new TaskResult(
    task: $task,
    output: 'task output',
    exception: null, // null|\Throwable
);

var_dump($result instanceof TaskResultInterface);
// bool(true)

// Get the task:
$task = $result->task();
// TaskInterface

// Get the output:
$output = $result->output();
// string

// Get the exception:
$output = $result->exception();
// null|\Throwable

var_dump($result->isSuccessful());
// bool(true)

var_dump($result->isFailure());
// bool(false)

var_dump($result->isSkipped());
// bool(false)
```

## Task Results

```php
use Tobento\Service\Schedule\TaskResults;
use Tobento\Service\Schedule\TaskResultsInterface;
use Tobento\Service\Schedule\TaskResultInterface;

$results = new TaskResults();

var_dump($results instanceof TaskResultsInterface);
// bool(true)

// Add a task result:
$results->add($result); // TaskResultInterface

// Get all task results:
$taskResults = $results->all();
// iterable<int, TaskResultInterface>

// or just:
foreach($taskResults as $taskResult) {}

// Count all task results:
$number = $results->count();
// int(0)

// Filter all successful task results returning a new instance:
$taskResults = $results->successful();
// TaskResultsInterface

// Filter all failed task results returning a new instance:
$taskResults = $results->failed();
// TaskResultsInterface

// Filter all skipped task results returning a new instance:
$taskResults = $results->skipped();
// TaskResultsInterface

// You may use the filter method for filtering task results
// returning a new instance:
$taskResults = $results->filter(fn(TaskResultInterface $r): bool => is_null($r->exception()));
// TaskResultsInterface
```

## Schedule

```php
use Tobento\Service\Schedule\Schedule;
use Tobento\Service\Schedule\ScheduleInterface;
use Tobento\Service\Schedule\TaskInterface;

$schedule = new Schedule(name: 'default');

var_dump($schedule instanceof ScheduleInterface);
// bool(true)

// Schedule any task implementing TaskInterface
$schedule->task($task);

// You may get a task by its id returning null if task is not found:
$task = $schedule->getTask(id: 'taskId');
// null|TaskInterface

// You may get all tasks:
$tasks = $schedule->all();
// iterable<int, TaskInterface>
```

**Example adding tasks**

```php
use Tobento\Service\Schedule\Task;
use Tobento\Service\Schedule\Parameter;
use Butschster\CronExpression\Generator;

$schedule->task(
    (new Task\CommandTask(
        command: 'command:name',
    ))
    // schedule task:
    ->cron(Generator::create()->everyTenMinutes())
    // adding parameters:
    ->parameter(Parameter\SendResultTo(
        file: 'dir/to/file.log',
    ))
    // or using helper methods:
    ->withoutOverlapping()
);

$schedule->task(
    (new Task\CallableTask(
        callable: static function (SomeService $service, $option): string {
            // do something
            
            // you may return the task output or nothing at all (void):
            return 'task output';
        },
        // you may set data passed to the function:
        params: ['option' => 'value'],
    ))
    ->name('Some name')
    ->description('Some description')
    // schedule task:
    ->cron(Generator::create()->everyTenMinutes())
    // adding parameters:
    ->parameter(Parameter\SendResultTo(
        file: 'dir/to/file.log',
    ))
    // or using helper methods:
    ->withoutOverlapping()
);
```

Check out the [Tasks](#tasks) section for more information about the individual tasks.

## Schedule Processor

The schedule processor is responsible for processing the scheduled tasks which are due.

```php
use Tobento\Service\Schedule\ScheduleProcessor;
use Tobento\Service\Schedule\ScheduleProcessorInterface;
use Tobento\Service\Schedule\TaskProcessorInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

$scheduleProcessor = new ScheduleProcessor(
    taskProcessor: $taskProcessor, // TaskProcessorInterface
    // you may set an event dispatcher if you want to support events:
    eventDispatcher: null, // null|EventDispatcherInterface
);

var_dump($scheduleProcessor instanceof ScheduleProcessorInterface);
// bool(true)
```

**Process Scheduled Task**

```php
use Tobento\Service\Schedule\ScheduleInterface;
use Tobento\Service\Schedule\TaskResultsInterface;

$results = $scheduleProcessor->processSchedule(
    schedule: $schedule, // ScheduleInterface
    now: new \DateTime(), // \DateTimeInterface
);

var_dump($results instanceof TaskResultsInterface);
// bool(true)
```

To process tasks when they are due, the ```processSchedule``` method should be called every minute indefinitely.

You may create a custom schedule processor fitting your needs though.

Check out the [Task Results](#task-results) to learn more.

## Console

You may use the following commands using the [Console Service](https://github.com/tobento-ch/service-console).

To get quickly started consider using the following two app bundles:

* [App Schedule](https://github.com/tobento-ch/app-schedule)
* [App Console](https://github.com/tobento-ch/app-console)

Otherwise, you need to install the [Console Service](https://github.com/tobento-ch/service-console) and set up your console by yourself. Check out the [Running Scheduled Tasks](#running-scheduled-tasks) section to see a possible implementation.

### Run Command

**Running due tasks**

```
php app schedule:run
```

**Running specific tasks by its id**

```
php app schedule:run --id=taskId --id=anotherTaskId
```

### List Command

Lists all tasks.

```
php app schedule:list
```

## Events

**Available Events**

```php
use Tobento\Service\Schedule\Event;
```

| Event | Description |
| --- | --- |
| ```Event\ScheduleStarting::class``` | The event will dispatch **before** the schedule is processed |
| ```Event\ScheduleFinished::class``` | The event will dispatch **after** the schedule is processed |
| ```Event\TaskStarting::class``` | The event will dispatch **before** the task is processed |
| ```Event\TaskFinished::class``` | The event will dispatch **after** the task is processed |

Just make sure you pass an event dispatcher to the [Schedule Processor](#schedule-processor)!

## Learn More

### Running Scheduled Tasks Without Console

Another way to run the schedule processor is a Cron job that runs your schedule.php script every minute. The following should be added to your production server's crontab:

```
* * * * * cd /path-to-your-project && php schedule.php >> /dev/null 2>&1
```

**Example of schedule.php**

Example using the [Container Service](https://github.com/tobento-ch/service-container):

```
composer require tobento/service-container
```

```
use Tobento\Service\Container\Container;
use Tobento\Service\Schedule\Task;
use Tobento\Service\Schedule\Parameter;
use Tobento\Service\Schedule\TaskProcessor;
use Tobento\Service\Schedule\TaskProcessorInterface;
use Tobento\Service\Schedule\ScheduleProcessor;
use Tobento\Service\Schedule\ScheduleProcessorInterface;
use Tobento\Service\Schedule\Schedule;
use Tobento\Service\Schedule\ScheduleInterface;
use Psr\Container\ContainerInterface;

// Container bindings:
$container = new Container();
$container->set(TaskProcessorInterface::class, TaskProcessor::class);
$container->set(ScheduleProcessorInterface::class, ScheduleProcessor::class);

// Schedule tasks:
$schedule = new Schedule(name: 'default');
$schedule->task(
    (new Task\CallableTask(
        callable: static function (): string {
            // do something:
            return 'task output';
        },
    ))->name('demo')
);

// Process the schedule:
$container->get(ScheduleProcessorInterface::class)->processSchedule(
    schedule: $schedule, // ScheduleInterface
    now: new \DateTime(), // \DateTimeInterface
);
```
# Credits

- [Tobias Strub](https://www.tobento.ch)
- [All Contributors](../../contributors)