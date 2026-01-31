<?php

namespace Modules\Flashcard\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Flashcard\Database\Factories\FlashcardFactory;
use OwenIt\Auditing\Contracts\Auditable;

class Flashcard extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['question', 'answer', 'status'];

    protected $attributes = [
        'status' => 'Not Answered',
    ];

    protected array $auditInclude = [
        'question',
        'answer',
        'status',
    ];

    protected bool $auditTimestamps = true;

    protected static function newFactory(): FlashcardFactory
    {
        return FlashcardFactory::new();
    }

    public function generateTags(): array
    {
        return [app()->runningInConsole() ? 'CLI' : 'API'];
    }
}
