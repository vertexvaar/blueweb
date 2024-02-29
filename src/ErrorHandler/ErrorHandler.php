<?php

declare(strict_types=1);

namespace VerteXVaaR\BlueWeb\ErrorHandler;

use Parsedown;
use Throwable;
use VerteXVaaR\BlueContainer\Generated\DI;
use VerteXVaaR\BlueSprints\Environment\Context;
use VerteXVaaR\BlueSprints\Environment\Environment;

use function class_exists;
use function file_exists;
use function file_get_contents;
use function get_class;
use function implode;
use function is_array;
use function is_object;
use function is_string;
use function nl2br;
use function set_error_handler;
use function set_exception_handler;
use function var_dump;
use function var_export;

use const E_ALL;

class ErrorHandler
{
    protected bool $cssIncluded = false;

    public function register(): void
    {
        set_exception_handler($this->handleException(...));
        set_error_handler($this->printErrorPage(...), E_ALL);
    }

    public function handleException(Throwable $throwable): void
    {
        $this->printErrorPage(
            $throwable->getCode(),
            $throwable->getMessage(),
            $throwable->getFile(),
            $throwable->getLine(),
            $throwable->getTrace(),
        );
    }

    protected function printErrorPage(
        int $code,
        string $message,
        string $file,
        int $line,
        array $callStack = [],
    ): bool {
        $this->includeCss();
        $context = (new DI())->get(Environment::class)->context;
        echo '<div class="c-error">';
        if (Context::Development === $context || Context::Testing === $context) {
            echo '<h1>An error occurred</h1>';
            echo '<div class="c-error__report">';
            echo '<p>Message: </p><code class="c-error__message">' . $message . ' (Code: ' . $code . ')</code>';
            echo '<p>Error occured in:</p><code>' . $file . ' @ ' . $line . '</code>';
            echo '</div>';
            $this->printCallStack($callStack);
            $this->printHelp($code);
        } else {
            echo '<h1>An error occurred. Please contact your administrator</h1>';
        }
        echo '</div>';
        return true;
    }

    protected function printCallStack(array $callStack): void
    {
        if (empty($callStack)) {
            return;
        }
        echo '<h3>Call Stack:</h3>';
        echo '<ul class="c-error__call-stack">';
        foreach ($callStack as $trace) {
            echo '<li><code>';
            echo ($trace['file'] ?? '<file>') . ' @ ' . ($trace['line'] ?? '?') . '<br/>';
            echo ($trace['class'] ?? '') . ($trace['type'] ?? '') . $trace['function'] . '(';
            foreach ($trace['args'] as $argument) {
                if (is_object($argument)) {
                    echo get_class($argument);
                } elseif (is_array($argument)) {
                    $argumentArray = [];
                    foreach ($argument as $key => $value) {
                        if (is_array($value)) {
                            $value = var_export($value, true);
                        } elseif (is_object($value)) {
                            $value = get_class($value);
                        }
                        $argumentArray[] = "'" . $key . "'" . ' => ' . $value;
                    }
                    echo implode(', ', $argumentArray);
                } elseif (is_string($argument)) {
                    echo $argument;
                } else {
                    /** @noinspection ForgottenDebugOutputInspection */
                    var_dump($argument);
                }
            }
            echo ')';
            echo '</code></li>';
        }
        echo '</ul>';
    }

    protected function printHelp(int $code): void
    {
        $helpFile = __DIR__ . '/../../docs/exception/' . $code . '.md';
        if (file_exists($helpFile)) {
            $helpFileContents = file_get_contents($helpFile);
            echo '<h2>This might help you:</h2>';
            if (class_exists(Parsedown::class)) {
                echo (new Parsedown())->text($helpFileContents);
            } else {
                echo '<div class="c-error__md">';
                echo nl2br($helpFileContents);
                echo '</div>';
            }
        } else {
            echo '<span>No help file available</span>';
        }
    }

    protected function includeCss(): void
    {
        if (false === $this->cssIncluded) {
            $this->cssIncluded = true;
            echo '<link href="/css/error.css" rel="stylesheet">';
        }
    }
}
