<?php
declare(strict_types=1);

namespace DR\Review\Model\Webhook\Gitlab;

use Symfony\Component\Serializer\Attribute\SerializedName;

class PushEvent
{
    #[SerializedName('object_kind')]
    public string $objectKind;
    #[SerializedName('event_type')]
    public string $eventType;
    #[SerializedName('project_id')]
    public int    $projectId;
}
