<?php
declare(strict_types=1);

namespace DR\Review\Model\Webhook\Gitlab;

use Symfony\Component\Serializer\Annotation\SerializedName;

class NoteEvent
{
    #[SerializedName('object_kind')]
    public string         $objectKind;
    #[SerializedName('event_type')]
    public string         $eventType;
    #[SerializedName('project_id')]
    public int            $projectId;
    public string         $note;
    public User           $user;
    public Project        $project;
    #[SerializedName('object_attributes')]
    public NoteAttributes $attributes;
}
