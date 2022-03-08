<?php

namespace App\Enums;

class Permissions
{

    private const VIEW_ANY = '_view_any';
    private const VIEW_ANY_FOR_USER = '_view_any_for_user';
    private const VIEW = '_view';
    private const VIEW_FOR_USER = '_view_for_user';
    private const CREATE = '_create';
    private const UPDATE = '_update';
    private const UPDATE_FOR_USER = '_update_for_user';
    private const DELETE = '_delete';
    private const DELETE_FOR_USER = '_delete_for_user';
    private const RESTORE = '_restore';
    private const RESTORE_FOR_USER = '_restore_for_user';

    private const USERS = 'users';
    const USERS_VIEW_ANY = self::USERS . self::VIEW_ANY;
    const USERS_CREATE = self::USERS . self::CREATE;
    const USERS_UPDATE = self::USERS . self::UPDATE;
    const USERS_DELETE = self::USERS . self::DELETE;

    private const GOALS = 'goals';
    const GOALS_VIEW_ANY_FOR_USER = self::GOALS . self::VIEW_ANY_FOR_USER;
    const GOALS_VIEW_FOR_USER = self::GOALS . self::VIEW_FOR_USER;
    const GOALS_CREATE = self::GOALS . self::CREATE;
    const GOALS_UPDATE_FOR_USER = self::GOALS . self::UPDATE_FOR_USER;
    const GOALS_DELETE_FOR_USER = self::GOALS . self::DELETE_FOR_USER;
    const GOALS_RESTORE_FOR_USER = self::GOALS . self::RESTORE_FOR_USER;




    private const CLIENT = 'client_';
    const VIEW_USERS_PAGE = self::CLIENT . 'view_users_page';
    const VIEW_GOALS_PAGE = self::CLIENT . 'view_goals_page';

}
