<?php

declare(strict_types=1);

namespace ElliotJReed\OpenAiProductFeed\Internal;

use ElliotJReed\OpenAiProductFeed\Exception\InvalidFeedState;

/**
 * The stream handling shared by every feed format.
 *
 * A feed always writes to a stream. Where the caller has not supplied one, a temporary stream is opened on the first
 * write and read back by toString(), which keeps the two ways of rendering a feed on a single path. Where the caller
 * has supplied one, each record reaches it as it is written, so memory use stays constant regardless of how large the
 * catalogue is.
 *
 * @internal
 */
abstract class Writer
{
    /** @var resource|null */
    private $stream;

    private bool $streaming = false;
    private bool $ended = false;

    /**
     * Directs output to the caller's stream, rather than accumulating it to be returned as a string.
     */
    public function streamTo(mixed $stream): void
    {
        if ($this->streaming) {
            throw InvalidFeedState::alreadyStreamedTo();
        }

        if (null !== $this->stream) {
            throw InvalidFeedState::alreadyStarted('stream');
        }

        if (!\is_resource($stream) || 'stream' !== \get_resource_type($stream)) {
            throw InvalidFeedState::notAWritableStream();
        }

        $this->stream = $stream;
        $this->streaming = true;

        $this->start($stream);
    }

    /**
     * Writes a single record. Where the feed is being streamed it reaches the stream immediately.
     *
     * @param array<string, scalar|list<string>|array<string, string>> $attributes
     */
    public function write(array $attributes): void
    {
        if ($this->ended) {
            throw InvalidFeedState::alreadyEnded();
        }

        $this->writeRecord($this->stream(), $attributes);
    }

    /**
     * Closes a streamed feed. The stream itself is left open for the caller to close.
     */
    public function end(): void
    {
        if (!$this->streaming) {
            throw InvalidFeedState::notStreaming();
        }

        if ($this->ended) {
            throw InvalidFeedState::alreadyEnded();
        }

        $this->ended = true;
    }

    /**
     * Closes the feed and returns everything written to it. Only available where the feed is not being streamed, and
     * may be called repeatedly, returning the same contents each time.
     */
    public function toString(): string
    {
        if ($this->streaming) {
            throw InvalidFeedState::alreadyStreaming($this->contentsMethod());
        }

        $stream = $this->stream();
        $this->ended = true;

        \rewind($stream);

        return (string) \stream_get_contents($stream);
    }

    /**
     * Whether anything has been written yet, which is what makes a configuration change no longer safe to apply.
     */
    protected function hasStarted(): bool
    {
        return null !== $this->stream;
    }

    /**
     * Writes whatever the format needs before its first record, such as a header row. Formats which need nothing
     * leave this alone.
     *
     * @param resource $stream
     */
    protected function start(mixed $stream): void
    {
    }

    /**
     * @param resource                                                 $stream
     * @param array<string, scalar|list<string>|array<string, string>> $attributes
     */
    abstract protected function writeRecord(mixed $stream, array $attributes): void;

    /**
     * The name of the feed method which returns the contents as a string, used to phrase the error raised when a
     * streamed feed is asked for them.
     */
    abstract protected function contentsMethod(): string;

    /**
     * @return resource
     */
    private function stream(): mixed
    {
        if (null === $this->stream) {
            $this->stream = \fopen('php://temp', 'w+');

            $this->start($this->stream);
        }

        return $this->stream;
    }
}
