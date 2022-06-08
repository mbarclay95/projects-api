<?php

namespace App\Models;

use App\Traits\HasApiModel;
use App\Traits\HasCrudDestroyable;
use App\Traits\HasCrudIndexable;
use App\Traits\HasCrudPermissions;
use App\Traits\HasCrudShowable;
use App\Traits\HasCrudStorable;
use App\Traits\HasCrudUpdatable;
use Illuminate\Database\Eloquent\Model;

class BaseApiModel extends Model
{
    use HasApiModel, HasCrudIndexable, HasCrudStorable, HasCrudShowable, HasCrudUpdatable, HasCrudDestroyable, HasCrudPermissions;

    protected static $unguarded = true;

}
