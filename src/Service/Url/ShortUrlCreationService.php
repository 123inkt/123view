<?php
declare(strict_types=1);

namespace DR\Review\Service\Url;

use DR\Review\Entity\Url\ShortUrl;
use DR\Review\Repository\Url\ShortUrlRepository;
use Psr\Http\Message\UriInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Throwable;

readonly class ShortUrlCreationService
{
    use ClockAwareTrait;

    public function __construct(private ShortUrlRepository $repository, private ShortKeyGeneratorService $keyGenerator)
    {
    }

    /**
     * Create a new ShortUrl entity with unique key and save it to database
     * @throws Throwable
     */
    public function createShortUrl(UriInterface $uri): ShortUrl
    {
        $shortKey = $this->keyGenerator->generateUniqueShortKey();

        $shortUrl = new ShortUrl();
        $shortUrl->setShortKey($shortKey);
        $shortUrl->setOriginalUrl($uri);
        $shortUrl->setCreateTimestamp($this->now()->getTimestamp());

        $this->repository->save($shortUrl, true);

        return $shortUrl;
    }
}
