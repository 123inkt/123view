<?php
declare(strict_types=1);

namespace DR\Review\EventSubscriber\ViewModel;

use DR\Review\ViewModel\ViewModelInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

#[AsEventListener(KernelEvents::VIEW, 'transform')]
class ViewModelSubscriber
{
    public function __construct(private readonly SerializerInterface $serializer)
    {
    }

    public function transform(ViewEvent $event): void
    {
        $object = $event->getControllerResult();
        if ($object instanceof ViewModelInterface === false) {
            return;
        }

        $data = $this->serializer->serialize($object, JsonEncoder::FORMAT);

        $event->setResponse(new Response($data, headers: ['Content-Type' => 'application/json']));
    }
}
