<?php

declare(strict_types=1);

namespace ElliotJReed\OpenAiProductFeed\Exception;

use LogicException;

/**
 * Thrown when a feed is used in a way its current state does not allow, such as adding a product after the feed has
 * been ended, or asking a streamed feed for its contents as a string.
 */
final class InvalidFeedState extends LogicException implements OpenAiProductFeedException
{
    public static function alreadyStreaming(string $method): self
    {
        return new self(\sprintf(
            'This feed is being streamed, so its contents cannot be returned as a string. ' .
            'Write the products to the stream and call end() instead of calling %s().',
            $method
        ));
    }

    public static function alreadyStreamedTo(): self
    {
        return new self(
            'This feed is being streamed already, so it cannot be streamed to a second destination.'
        );
    }

    public static function alreadyEnded(): self
    {
        return new self('This feed has been ended and can no longer be written to.');
    }

    public static function notStreaming(): self
    {
        return new self('This feed is not being streamed. Call stream() before calling end().');
    }

    public static function notAWritableStream(): self
    {
        return new self(
            'The feed must be streamed to a writable stream resource, for example fopen("feed.jsonl", "wb").'
        );
    }

    public static function alreadyStarted(string $method): self
    {
        return new self(\sprintf(
            'The %s() method configures the feed and must be called before the first product is added.',
            $method
        ));
    }
}
