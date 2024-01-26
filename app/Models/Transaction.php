<?php

namespace App\Models;

use App\Enums\TransactionTypeEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'account_id',
        'category_id',
        'value',
        'date',
        'from_acc',
        'to_acc',
        'notes',
        'published',
        'type',
    ];

    protected $hidden = [
        'create_type',
        'import_id'
    ];

    public function account() : BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function category() : BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function currency() : Model
    {
        $account_currency = $this->account()->get('currency_id')->first();
        return Currency::query()->where(['id' => $account_currency->currency_id])->first();
    }

    protected static function booted() : void
    {
        static::addGlobalScope('by_account', function (Builder $builder) {
            if(auth()->check()) {
                $builder->whereIn('account_id', Account::all('id')->toArray());
            }
        });
    }

    public static function getForm(): array
    {
        return [
            Select::make('account_id')
                ->relationship('account', 'title')
                ->required()
                ->searchable()
                ->preload()
                ->editOptionForm(Account::getForm()),
                //->createOptionForm(Account::getForm()),
                /*->createOptionUsing(function (array $data): Model {
                    $data['user_id'] = auth()->id();
                    //$account = Account::create($data);
                    //$account->save();
                    return Account::create($data);
                }),*/
            Select::make('type')
                ->label('Type')
                ->required()
                ->options(array_map(fn($value) => strtolower($value), TransactionTypeEnum::toArrayNames())),
            Select::make('category_id')
                ->label('Category')
                ->relationship('category', 'title')
                ->searchable()
                ->preload()
                ->editOptionForm(Category::getForm()),
                //->createOptionForm(Category::getForm()),
            TextInput::make('value')
                ->required()
                ->numeric()
                ->minValue(0)
                ->maxValue(999999.99),
            DateTimePicker::make('date')
                ->required(),
            TextInput::make('from_acc')
                ->maxLength(255),
            TextInput::make('to_acc')
                ->maxLength(255),
            Textarea::make('notes')
                ->maxLength(65535)
                ->columnSpanFull(),
            Toggle::make('published')
                ->required(),
        ];
    }

    public function publish(): void
    {
        $this->published = true;
        $this->save();
    }

    public function unpublish(): void
    {
        $this->published = false;
        $this->save();
    }

    public function setCategory(int $category): void
    {
        $this->category_id = $category;
        $this->save();
    }

    public function setType(int $type): void
    {
        $this->type = $type;
        $this->save();
    }
}
