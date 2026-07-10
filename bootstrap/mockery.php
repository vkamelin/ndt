<?php

declare(strict_types=1);

use Symfony\Component\Console\Formatter\OutputFormatterInterface;

if (class_exists('Mockery')) {
    return;
}

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
        return new class implements \Symfony\Component\Console\Output\OutputInterface {
            private int $verbosity = self::VERBOSITY_NORMAL;
            private bool $decorated = false;
            private OutputFormatterInterface $formatter;

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

            public function setFormatter(OutputFormatterInterface $formatter): void
            {
                $this->formatter = $formatter;
            }

            public function getFormatter(): OutputFormatterInterface
            {
                return $this->formatter;
            }
        };
    }
}
