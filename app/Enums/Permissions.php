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

    private const CLIENT = 'client_';
    const VIEW_USERS_PAGE = self::CLIENT . 'view_users_page';
    const VIEW_GOALS_PAGE = self::CLIENT . 'view_goals_page';
    const VIEW_BACKUPS_PAGE = self::CLIENT . 'view_backups_page';
    const VIEW_DASHBOARD_PAGE = self::CLIENT . 'view_dashboard_page';
    const VIEW_TASKS_PAGE = self::CLIENT . 'view_tasks_page';
    const VIEW_FAMILIES_TAB = self::CLIENT . 'view_families_tab';
    const VIEW_EVENTS_PAGE = self::CLIENT . 'view_events_page';
    const VIEW_FILE_EXPLORER_PAGE = self::CLIENT . 'view_file_explorer_page';
    const VIEW_MONEY_APP_PAGE = self::CLIENT . 'view_money_app_page';
    const VIEW_LOGGING_PAGE = self::CLIENT . 'view_logging_page';
    const VIEW_GAMING_SESSION_ADMIN_PAGE = self::CLIENT . 'view_gaming_session_admin_page';
    const LISTEN_TO_UPTIME_KUMA_WEBSOCKET = self::CLIENT . 'listen_to_uptime_kuma_websocket';

}
