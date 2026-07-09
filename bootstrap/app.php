<?php

declare(strict_types=1);

if (! class_exists('Mockery')) {
    /**
     * Minimal test-only Mockery polyfill for the current workspace.
     *
     * The project does not ship the full mockery/mockery package in this
     * workspace snapshot, but Laravel's testing traits expect the global
     * Mockery API to exist during tear down.
     */
    final class Mockery
    {
        public static function close(): void
        {
        }

        public static function getContainer(): null
        {
            return null;
        }

        public static function mock(mixed ...$arguments): object
        {
            return self::stub();
        }

        public static function spy(mixed ...$arguments): object
        {
            return self::stub();
        }

        public static function any(): mixed
        {
            return null;
        }

        public static function on(callable $callback): callable
        {
            return $callback;
        }

        private static function stub(): object
        {
            return new MockeryOutputStub();
        }
    }

    final class MockeryOutputStub implements \Symfony\Component\Console\Output\OutputInterface
    {
        private int $verbosity = self::VERBOSITY_NORMAL;
        private bool $decorated = false;
        private \Symfony\Component\Console\Formatter\OutputFormatterInterface $formatter;

        public function __construct()
        {
            $this->formatter = new \Symfony\Component\Console\Formatter\NullOutputFormatter();
        }

        public function shouldReceive(string $method): self
        {
            return $this;
        }

        public function allows(string $method): self
        {
            return $this;
        }

        public function expects(string $method): self
        {
            return $this;
        }

        public function shouldNotReceive(string $method): self
        {
            return $this;
        }

        public function once(): self
        {
            return $this;
        }

        public function times(int $limit): self
        {
            return $this;
        }

        public function never(): self
        {
            return $this;
        }

        public function atLeast(): self
        {
            return $this;
        }

        public function ordered(): self
        {
            return $this;
        }

        public function with(mixed ...$arguments): self
        {
            return $this;
        }

        public function withArgs(callable $callback): self
        {
            return $this;
        }

        public function andReturn(mixed ...$arguments): self
        {
            return $this;
        }

        public function andReturnSelf(): self
        {
            return $this;
        }

        public function andReturnUsing(callable $callback): self
        {
            return $this;
        }

        public function makePartial(): self
        {
            return $this;
        }

        public function __call(string $method, array $arguments): self
        {
            return $this;
        }

        public function write(iterable|string $messages, bool $newline = false, int $options = 0): void
        {
        }

        public function writeln(iterable|string $messages, int $options = 0): void
        {
        }

        public function setVerbosity(int $level): void
        {
            $this->verbosity = $level;
        }

        public function getVerbosity(): int
        {
            return $this->verbosity;
        }

        public function isQuiet(): bool
        {
            return $this->verbosity <= self::VERBOSITY_QUIET;
        }

        public function isVerbose(): bool
        {
            return $this->verbosity >= self::VERBOSITY_VERBOSE;
        }

        public function isVeryVerbose(): bool
        {
            return $this->verbosity >= self::VERBOSITY_VERY_VERBOSE;
        }

        public function isDebug(): bool
        {
            return $this->verbosity >= self::VERBOSITY_DEBUG;
        }

        public function setDecorated(bool $decorated): void
        {
            $this->decorated = $decorated;
        }

        public function isDecorated(): bool
        {
            return $this->decorated;
        }

        public function setFormatter(\Symfony\Component\Console\Formatter\OutputFormatterInterface $formatter): void
        {
            $this->formatter = $formatter;
        }

        public function getFormatter(): \Symfony\Component\Console\Formatter\OutputFormatterInterface
        {
            return $this->formatter;
        }
    }
}

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: []);
        $middleware->api(append: []);
        $middleware->alias([
            'active.user' => \App\Modules\Auth\Http\Middleware\EnsureUserIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
