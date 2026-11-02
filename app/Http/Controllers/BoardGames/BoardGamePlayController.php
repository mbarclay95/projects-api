<?php

namespace App\Http\Controllers\BoardGames;

use App\Enums\BoardGamePlayStatusEnum;
use App\Enums\BoardGameTypeEnum;
use App\Models\BoardGames\BoardGamePlay;
use Illuminate\Validation\Rule;
use Mbarclay36\LaravelCrud\CrudController;

class BoardGamePlayController extends CrudController
{
    protected static string $modelClass = BoardGamePlay::class;

    protected static array $storeRules = [];

    protected static array $updateRules = [];

    public function __construct()
    {
        static::$storeRules = [
            'gameType' => ['required', Rule::enum(BoardGameTypeEnum::class)],
            'name' => ['nullable', 'string', 'max:255'],
        ];

        static::$updateRules = [
            'name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'required', Rule::enum(BoardGamePlayStatusEnum::class)],
            'state' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
