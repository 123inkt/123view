<?php
declare(strict_types=1);

namespace DR\Review\Model\Webhook\Gitlab;

use Stringable;
use Symfony\Component\Serializer\Attribute\SerializedName;

class PushEvent implements Stringable
{
    #[SerializedName('object_kind')]
    public string $objectKind;
    #[SerializedName('event_type')]
    public string $eventType;
    #[SerializedName('project_id')]
    public int    $projectId;

    public function __toString(): string
    {
        return sprintf('PushEvent(objectKind: %s, eventType: %s, projectId: %d)', $this->objectKind, $this->eventType, $this->projectId);
    }
}
