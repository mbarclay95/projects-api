<?php

namespace App\Models\ApiModels;

use App\Repositories\Drafts\DraftAdminCandidatesRepository;
use Mbarclay36\LaravelCrud\Traits\IsApiModel;

class DraftAdminCandidateApiModel
{
    use IsApiModel;

    protected static array $apiModelAttributes = ['id', 'name'];

    protected static function getRepositoryClass(): string
    {
        return DraftAdminCandidatesRepository::class;
    }
}
