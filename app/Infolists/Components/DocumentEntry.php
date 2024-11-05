<?php

namespace App\Infolists\Components;

use Filament\Infolists\Components\ImageEntry;
use Closure;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;
use League\Flysystem\UnableToCheckFileExistence;
use Throwable;

class DocumentEntry extends ImageEntry
{
    protected string $view = 'infolists.components.document-entry';

    protected string | Closure | null $defaultDocumentUrl = null;

    public function defaultDocumentUrl(string | Closure | null $url): static
    {
        $this->defaultDocumentUrl = $url;
        return $this;
    }

    public function getDocumentUrl(?string $state = null): ?string
    {
        if ((filter_var($state, FILTER_VALIDATE_URL) !== false) || str($state)->startsWith('data:')) {
            return $state;
        }

        /** @var FilesystemAdapter $storage */
        $storage = $this->getDisk();

        if ($this->shouldCheckFileExistence()) {
            try {
                if (!$storage->exists($state)) {
                    return null;
                }
            } catch (UnableToCheckFileExistence $exception) {
                return null;
            }
        }

        if ($this->getVisibility() === 'private') {
            try {
                return $storage->temporaryUrl($state, now()->addMinutes(5));
            } catch (Throwable $exception) {
                // This driver does not support creating temporary URLs.
            }
        }

        return $storage->url($state);
    }

    public function getDefaultDocumentUrl(): ?string
    {
        return $this->evaluate($this->defaultDocumentUrl);
    }

    public function getRecord(): ?Model
    {
        $record = parent::getRecord();

        if (!$record) {
            return null;
        }

        if ($this->hasRelationship($record)) {
            $relatedRecord = $this->getRelationshipResults($record);
            if (is_array($relatedRecord)) {
                return !empty($relatedRecord) ? $relatedRecord[0] : null;
            }
            return $relatedRecord;
        }

        return $record;
    }


    public function getDocumentNames(): array
    {
        $record = $this->getRecord();

        if (!$record) {
            return [];
        }

        $documents = $record->media->where('collection_name', 'document-attachments');

        return $documents->pluck('name')->toArray();
    }
}
