<?php
declare(strict_types=1);

namespace DR\Review\Model\Mercure;

use JsonSerializable;
use Psr\Http\Message\UriInterface;

readonly class UpdateMessage implements JsonSerializable
{
    public function __construct(
        public int $eventId,
        public int $userId,
        public ?int $reviewId,
        public string $eventName,
        public string $title,
        public string $message,
        public UriInterface $url
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'eventId'   => $this->eventId,
            'userId'    => $this->userId,
            'reviewId'  => $this->reviewId,
            'eventName' => $this->eventName,
            'title'     => $this->title,
            'message'   => $this->message,
            'url'       => (string)$this->url,
        ];
    }
}
