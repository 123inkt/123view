<?php
declare(strict_types=1);

namespace DR\Review\EventSubscriber\MarkDown;

use DR\Review\Service\Markdown\DocumentNodeIteratorFactory;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(DocumentParsedEvent::class, 'handle')]
readonly class AbsoluteImageUrlSubscriber
{
    public function __construct(private DocumentNodeIteratorFactory $iteratorFactory, private string $appAbsoluteUrl)
    {
    }

    public function handle(DocumentParsedEvent $event): void
    {
        foreach ($this->iteratorFactory->iterate($event->getDocument()) as $node) {
            if ($node instanceof Image === false || stripos($node->getUrl(), 'http') === 0) {
                continue;
            }

            $node->setUrl(sprintf('%s/%s', rtrim($this->appAbsoluteUrl, '/'), ltrim($node->getUrl(), '/')));
        }
    }
}
