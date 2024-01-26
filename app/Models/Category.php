<?php

namespace App\Models;

use App\Traits\BootedModelUserActions;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Textarea;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Filament\Forms\Components\TextInput;

class Category extends Model
{
    use HasFactory, BootedModelUserActions;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'color',
        'description',
    ];

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getForm(): array
    {
        return [
            TextInput::make('title')
                ->required()
                ->maxLength(255),
            ColorPicker::make('color'),
            Textarea::make('description')
                ->maxLength(65535)
                ->columnSpanFull(),
        ];
    }
}
