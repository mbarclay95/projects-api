<?php

namespace App\Enums;

class Permissions
{

    const VIEW_ANY = '_view_any';
    const VIEW_ANY_FOR_USER = '_view_any_for_user';
    const VIEW = '_view';
    const VIEW_FOR_USER = '_view_for_user';
    const CREATE = '_create';
    const UPDATE = '_update';
    const UPDATE_FOR_USER = '_update_for_user';
    const DELETE = '_delete';
    const DELETE_FOR_USER = '_delete_for_user';
    const RESTORE = '_restore';
    const RESTORE_FOR_USER = '_restore_for_user';

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

    private const BACKUPS = 'backups';
    const BACKUPS_VIEW_ANY_FOR_USER = self::BACKUPS . self::VIEW_ANY_FOR_USER;
    const BACKUPS_VIEW_FOR_USER = self::BACKUPS . self::VIEW_FOR_USER;
    const BACKUPS_CREATE = self::BACKUPS . self::CREATE;
    const BACKUPS_UPDATE_FOR_USER = self::BACKUPS . self::UPDATE_FOR_USER;
    const BACKUPS_RUN_ACTIONS = self::BACKUPS . '_run_actions';

    private const SCHEDULED_BACKUPS = 'scheduled_backups';
    const SCHEDULED_BACKUPS_VIEW_ANY_FOR_USER = self::SCHEDULED_BACKUPS . self::VIEW_ANY_FOR_USER;
    const SCHEDULED_BACKUPS_VIEW_FOR_USER = self::SCHEDULED_BACKUPS . self::VIEW_FOR_USER;
    const SCHEDULED_BACKUPS_CREATE = self::SCHEDULED_BACKUPS . self::CREATE;
    const SCHEDULED_BACKUPS_UPDATE_FOR_USER = self::SCHEDULED_BACKUPS . self::UPDATE_FOR_USER;
    const SCHEDULED_BACKUPS_DELETE_FOR_USER = self::SCHEDULED_BACKUPS . self::DELETE_FOR_USER;
    const SCHEDULED_BACKUPS_RESTORE_FOR_USER = self::SCHEDULED_BACKUPS . self::RESTORE_FOR_USER;

    private const TARGETS = 'targets';
    const TARGETS_VIEW_ANY_FOR_USER = self::TARGETS . self::VIEW_ANY_FOR_USER;
    const TARGETS_CREATE = self::TARGETS . self::CREATE;
    const TARGETS_UPDATE_FOR_USER = self::TARGETS . self::UPDATE_FOR_USER;
    const TARGETS_DELETE_FOR_USER = self::TARGETS . self::DELETE_FOR_USER;

    private const FOLDERS = 'folders';
    const FOLDERS_VIEW_ANY_FOR_USER = self::FOLDERS . self::VIEW_ANY_FOR_USER;
    const FOLDERS_CREATE = self::FOLDERS . self::CREATE;
    const FOLDERS_UPDATE_FOR_USER = self::FOLDERS . self::UPDATE_FOR_USER;
    const FOLDERS_DELETE_FOR_USER = self::FOLDERS . self::DELETE_FOR_USER;

    private const SITES = 'sites';
    const SITES_CREATE = self::SITES . self::CREATE;
    const SITES_UPDATE_FOR_USER = self::SITES . self::UPDATE_FOR_USER;
    const SITES_DELETE_FOR_USER = self::SITES . self::DELETE_FOR_USER;

    private const SITE_IMAGES = 'site_images';
    const SITE_IMAGES_VIEW_FOR_USER = self::SITE_IMAGES . self::VIEW_FOR_USER;
    const SITE_IMAGES_CREATE = self::SITE_IMAGES . self::CREATE;

    private const CLIENT = 'client_';
    const VIEW_USERS_PAGE = self::CLIENT . 'view_users_page';
    const VIEW_GOALS_PAGE = self::CLIENT . 'view_goals_page';
    const VIEW_BACKUPS_PAGE = self::CLIENT . 'view_backups_page';
    const VIEW_DASHBOARD_PAGE = self::CLIENT . 'view_dashboard_page';

}
