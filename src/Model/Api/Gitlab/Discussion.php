<?php
declare(strict_types=1);

namespace DR\Review\Model\Api\Gitlab;

class Discussion
{
    public string $id;

    /** @var list<Note> */
    private array $notes = [];

    public function addNote(Note $note): void
    {
        $this->notes[] = $note;
    }

    public function getNote(int $index): ?Note
    {
        return $this->notes[$index] ?? null;
    }

    /**
     * @return list<Note>
     */
    public function getNotes(): array
    {
        return $this->notes;
    }

    public function removeNote(Note $note): void
    {
        $notes = $this->notes;
        foreach ($notes as $index => $currentNote) {
            if ($note->id === $currentNote->id) {
                unset($notes[$index]);
                break;
            }
        }

        $this->notes = array_values($notes);
    }
}
