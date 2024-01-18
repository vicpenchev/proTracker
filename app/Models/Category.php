<?php

namespace App\Models;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory;

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
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * @return void
     */
    protected static function booted() : void
    {
        static::addGlobalScope('by_user', function (Builder $builder) {
            if(auth()->check()) {
                $builder->where('user_id', auth()->id());
            }
        });
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
