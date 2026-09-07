--
-- PostgreSQL database dump
--

\restrict PwbIkXPKOBBkJeWzu9J2u6y9Bm8iz186UwKdMPWOqriQCXUO9MeKgQrOWLBGlzF

-- Dumped from database version 16.14 (Debian 16.14-1.pgdg13+1)
-- Dumped by pg_dump version 17.11 (Debian 17.11-0+deb13u1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: backup_jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.backup_jobs (
    id bigint NOT NULL,
    started_at timestamp(0) without time zone,
    completed_at timestamp(0) without time zone,
    errored_at timestamp(0) without time zone,
    user_id integer NOT NULL,
    backup_id integer NOT NULL,
    schedule_id integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: backup_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.backup_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: backup_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.backup_jobs_id_seq OWNED BY public.backup_jobs.id;


--
-- Name: backup_schedule; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.backup_schedule (
    id bigint NOT NULL,
    backup_id integer NOT NULL,
    schedule_id integer NOT NULL
);


--
-- Name: backup_schedule_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.backup_schedule_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: backup_schedule_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.backup_schedule_id_seq OWNED BY public.backup_schedule.id;


--
-- Name: backup_step_jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.backup_step_jobs (
    id bigint NOT NULL,
    started_at timestamp(0) without time zone,
    completed_at timestamp(0) without time zone,
    errored_at timestamp(0) without time zone,
    error_message text,
    user_id integer NOT NULL,
    backup_step_id integer NOT NULL,
    backup_job_id integer NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    sort integer NOT NULL
);


--
-- Name: backup_step_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.backup_step_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: backup_step_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.backup_step_jobs_id_seq OWNED BY public.backup_step_jobs.id;


--
-- Name: backup_steps; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.backup_steps (
    id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    name character varying(255) NOT NULL,
    user_id integer NOT NULL,
    backup_id integer NOT NULL,
    sort integer NOT NULL,
    backup_step_type character varying(255) NOT NULL,
    config jsonb NOT NULL,
    deleted_at timestamp(0) without time zone
);


--
-- Name: backup_steps_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.backup_steps_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: backup_steps_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.backup_steps_id_seq OWNED BY public.backup_steps.id;


--
-- Name: backups; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.backups (
    id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    name character varying(255) NOT NULL,
    user_id integer NOT NULL,
    deleted_at timestamp(0) without time zone
);


--
-- Name: backups_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.backups_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: backups_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.backups_id_seq OWNED BY public.backups.id;


--
-- Name: draft_admins; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.draft_admins (
    id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    draft_id integer NOT NULL,
    user_id integer NOT NULL
);


--
-- Name: draft_admins_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.draft_admins_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: draft_admins_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.draft_admins_id_seq OWNED BY public.draft_admins.id;


--
-- Name: draft_images; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.draft_images (
    id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    s3_path character varying(255) NOT NULL,
    original_file_name character varying(255) NOT NULL,
    created_by_id integer NOT NULL
);


--
-- Name: draft_images_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.draft_images_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: draft_images_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.draft_images_id_seq OWNED BY public.draft_images.id;


--
-- Name: draft_members; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.draft_members (
    id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    draft_id integer NOT NULL,
    name character varying(255) NOT NULL,
    secret character varying(255) NOT NULL,
    pick_position integer,
    claimed_at timestamp(0) without time zone
);


--
-- Name: draft_members_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.draft_members_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: draft_members_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.draft_members_id_seq OWNED BY public.draft_members.id;


--
-- Name: draft_picks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.draft_picks (
    id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    draft_id integer NOT NULL,
    draft_member_id integer NOT NULL,
    draft_team_id integer NOT NULL,
    pick_number integer NOT NULL,
    made_by_admin boolean DEFAULT false NOT NULL
);


--
-- Name: draft_picks_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.draft_picks_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: draft_picks_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.draft_picks_id_seq OWNED BY public.draft_picks.id;


--
-- Name: draft_teams; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.draft_teams (
    id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    draft_id integer NOT NULL,
    name character varying(255) NOT NULL,
    s3_path character varying(255),
    sort_order integer DEFAULT 0 NOT NULL
);


--
-- Name: draft_teams_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.draft_teams_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: draft_teams_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.draft_teams_id_seq OWNED BY public.draft_teams.id;


--
-- Name: drafts; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.drafts (
    id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    name character varying(255) NOT NULL,
    notes text,
    draft_date timestamp(0) without time zone,
    token character varying(255) NOT NULL,
    status character varying(255) DEFAULT 'signup'::character varying NOT NULL,
    total_rounds integer DEFAULT 1 NOT NULL,
    max_participants integer,
    created_by_id integer NOT NULL,
    draft_image_id integer
);


--
-- Name: drafts_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.drafts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: drafts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.drafts_id_seq OWNED BY public.drafts.id;


--
-- Name: event_participants; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.event_participants (
    id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    name character varying(255) NOT NULL,
    event_id integer NOT NULL,
    is_going boolean DEFAULT true NOT NULL,
    notification_email character varying(255)
);


--
-- Name: event_participants_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.event_participants_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: event_participants_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.event_participants_id_seq OWNED BY public.event_participants.id;


--
-- Name: events; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.events (
    id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    name character varying(255) NOT NULL,
    notes text,
    event_date timestamp(0) without time zone NOT NULL,
    user_id integer NOT NULL,
    num_of_people integer NOT NULL,
    token character varying(255) NOT NULL,
    limit_participants boolean DEFAULT false NOT NULL,
    notification_email character varying(255)
);


--
-- Name: events_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.events_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: events_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.events_id_seq OWNED BY public.events.id;


--
-- Name: families; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.families (
    id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    name character varying(255) NOT NULL,
    task_strategy character varying(255) DEFAULT 'per task'::character varying NOT NULL,
    task_points jsonb
);


--
-- Name: families_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.families_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: families_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.families_id_seq OWNED BY public.families.id;


--
-- Name: folders; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.folders (
    id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    sort integer NOT NULL,
    name character varying(255) NOT NULL,
    show boolean NOT NULL,
    user_id integer NOT NULL
);


--
-- Name: folders_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.folders_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: folders_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.folders_id_seq OWNED BY public.folders.id;


--
-- Name: gaming_devices; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.gaming_devices (
    id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    device_communication_id character varying(255) NOT NULL,
    last_seen timestamp(0) without time zone NOT NULL,
    button_color character varying(255) NOT NULL
);


--
-- Name: gaming_devices_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.gaming_devices_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: gaming_devices_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.gaming_devices_id_seq OWNED BY public.gaming_devices.id;


--
-- Name: gaming_session_devices; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.gaming_session_devices (
    id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    gaming_device_id integer NOT NULL,
    gaming_session_id integer NOT NULL,
    name character varying(255) NOT NULL,
    current_turn_order integer NOT NULL,
    next_turn_order integer,
    turn_time_display_mode character varying(255) NOT NULL,
    skip boolean NOT NULL,
    has_passed boolean NOT NULL
);


--
-- Name: gaming_session_devices_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.gaming_session_devices_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: gaming_session_devices_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.gaming_session_devices_id_seq OWNED BY public.gaming_session_devices.id;


--
-- Name: gaming_sessions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.gaming_sessions (
    id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    name character varying(255) NOT NULL,
    started_at timestamp(0) without time zone,
    ended_at timestamp(0) without time zone,
    turn_order_type character varying(255) NOT NULL,
    current_turn integer NOT NULL,
    allow_turn_passing boolean NOT NULL,
    skip_after_passing boolean NOT NULL,
    pause_at_beginning_of_round boolean NOT NULL,
    is_paused boolean NOT NULL,
    turn_limit_seconds integer NOT NULL
);


--
-- Name: gaming_sessions_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.gaming_sessions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: gaming_sessions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.gaming_sessions_id_seq OWNED BY public.gaming_sessions.id;


--
-- Name: goal_days; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.goal_days (
    id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    goal_id integer NOT NULL,
    date date NOT NULL,
    amount integer NOT NULL,
    user_id integer NOT NULL
);


--
-- Name: goal_days_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.goal_days_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: goal_days_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.goal_days_id_seq OWNED BY public.goal_days.id;


--
-- Name: goals; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.goals (
    id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    user_id integer NOT NULL,
    title character varying(255) NOT NULL,
    verb character varying(255) NOT NULL,
    expected_amount integer NOT NULL,
    unit character varying(255) NOT NULL,
    length_of_time character varying(255) NOT NULL,
    equality character varying(255) NOT NULL
);


--
-- Name: goals_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.goals_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: goals_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.goals_id_seq OWNED BY public.goals.id;


--
-- Name: log_events; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.log_events (
    id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    source character varying(255) NOT NULL
);


--
-- Name: log_events_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.log_events_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: log_events_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.log_events_id_seq OWNED BY public.log_events.id;


--
-- Name: log_items; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.log_items (
    id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    log_event_id integer NOT NULL,
    payload jsonb NOT NULL
);


--
-- Name: log_items_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.log_items_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: log_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.log_items_id_seq OWNED BY public.log_items.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: model_has_permissions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.model_has_permissions (
    permission_id bigint NOT NULL,
    model_type character varying(255) NOT NULL,
    model_id bigint NOT NULL
);


--
-- Name: model_has_roles; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.model_has_roles (
    role_id bigint NOT NULL,
    model_type character varying(255) NOT NULL,
    model_id bigint NOT NULL
);


--
-- Name: permissions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.permissions (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    guard_name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: permissions_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.permissions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: permissions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.permissions_id_seq OWNED BY public.permissions.id;


--
-- Name: recurring_tasks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.recurring_tasks (
    id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    name character varying(255) NOT NULL,
    description character varying(255),
    owner_type character varying(255) NOT NULL,
    owner_id bigint NOT NULL,
    frequency_unit character varying(255) NOT NULL,
    frequency_amount integer NOT NULL,
    is_active boolean NOT NULL,
    priority integer NOT NULL,
    task_point integer
);


--
-- Name: recurring_tasks_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.recurring_tasks_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: recurring_tasks_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.recurring_tasks_id_seq OWNED BY public.recurring_tasks.id;


--
-- Name: role_has_permissions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.role_has_permissions (
    permission_id bigint NOT NULL,
    role_id bigint NOT NULL
);


--
-- Name: roles; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.roles (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    guard_name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: roles_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.roles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: roles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.roles_id_seq OWNED BY public.roles.id;


--
-- Name: schedules; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.schedules (
    id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    name character varying(255) NOT NULL,
    user_id integer NOT NULL,
    schedule jsonb NOT NULL,
    enabled boolean NOT NULL
);


--
-- Name: scheduled_backups_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.scheduled_backups_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: scheduled_backups_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.scheduled_backups_id_seq OWNED BY public.schedules.id;


--
-- Name: site_images; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.site_images (
    id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    original_file_name character varying(255) NOT NULL,
    user_id integer NOT NULL,
    s3_path character varying(255) NOT NULL
);


--
-- Name: site_images_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.site_images_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: site_images_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.site_images_id_seq OWNED BY public.site_images.id;


--
-- Name: sites; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.sites (
    id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    url character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    description character varying(255),
    show boolean NOT NULL,
    sort integer,
    folder_id integer NOT NULL,
    user_id integer NOT NULL,
    site_image_id integer
);


--
-- Name: sites_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.sites_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: sites_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.sites_id_seq OWNED BY public.sites.id;


--
-- Name: taggables; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.taggables (
    id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    tag_id integer NOT NULL,
    taggable_type character varying(255) NOT NULL,
    taggable_id bigint NOT NULL
);


--
-- Name: taggables_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.taggables_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: taggables_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.taggables_id_seq OWNED BY public.taggables.id;


--
-- Name: tags; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.tags (
    id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    tag character varying(255) NOT NULL
);


--
-- Name: tags_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.tags_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: tags_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.tags_id_seq OWNED BY public.tags.id;


--
-- Name: targets; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.targets (
    id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    name character varying(255) NOT NULL,
    user_id integer NOT NULL,
    target_url character varying(255) NOT NULL,
    host_name character varying(255)
);


--
-- Name: targets_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.targets_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: targets_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.targets_id_seq OWNED BY public.targets.id;


--
-- Name: task_user_configs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.task_user_configs (
    id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    user_id integer NOT NULL,
    family_id integer NOT NULL,
    tasks_per_week integer NOT NULL,
    start_date date NOT NULL,
    end_date date NOT NULL,
    default_tasks_per_week integer NOT NULL
);


--
-- Name: task_user_configs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.task_user_configs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: task_user_configs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.task_user_configs_id_seq OWNED BY public.task_user_configs.id;


--
-- Name: tasks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.tasks (
    id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    due_date date NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    recurring_task_id integer,
    owner_type character varying(255) NOT NULL,
    owner_id bigint NOT NULL,
    completed_by_id integer,
    completed_at timestamp(0) without time zone,
    cleared_at timestamp(0) without time zone,
    priority integer NOT NULL,
    task_point integer
);


--
-- Name: tasks_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.tasks_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: tasks_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.tasks_id_seq OWNED BY public.tasks.id;


--
-- Name: user_configs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.user_configs (
    id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    user_id integer NOT NULL,
    side_menu_open boolean NOT NULL,
    home_page_role character varying(255),
    money_app_token text
);


--
-- Name: user_configs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.user_configs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: user_configs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.user_configs_id_seq OWNED BY public.user_configs.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    username character varying(255) NOT NULL,
    password character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    last_logged_in_at timestamp(0) without time zone
);


--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: backup_jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.backup_jobs ALTER COLUMN id SET DEFAULT nextval('public.backup_jobs_id_seq'::regclass);


--
-- Name: backup_schedule id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.backup_schedule ALTER COLUMN id SET DEFAULT nextval('public.backup_schedule_id_seq'::regclass);


--
-- Name: backup_step_jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.backup_step_jobs ALTER COLUMN id SET DEFAULT nextval('public.backup_step_jobs_id_seq'::regclass);


--
-- Name: backup_steps id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.backup_steps ALTER COLUMN id SET DEFAULT nextval('public.backup_steps_id_seq'::regclass);


--
-- Name: backups id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.backups ALTER COLUMN id SET DEFAULT nextval('public.backups_id_seq'::regclass);


--
-- Name: draft_admins id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.draft_admins ALTER COLUMN id SET DEFAULT nextval('public.draft_admins_id_seq'::regclass);


--
-- Name: draft_images id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.draft_images ALTER COLUMN id SET DEFAULT nextval('public.draft_images_id_seq'::regclass);


--
-- Name: draft_members id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.draft_members ALTER COLUMN id SET DEFAULT nextval('public.draft_members_id_seq'::regclass);


--
-- Name: draft_picks id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.draft_picks ALTER COLUMN id SET DEFAULT nextval('public.draft_picks_id_seq'::regclass);


--
-- Name: draft_teams id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.draft_teams ALTER COLUMN id SET DEFAULT nextval('public.draft_teams_id_seq'::regclass);


--
-- Name: drafts id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.drafts ALTER COLUMN id SET DEFAULT nextval('public.drafts_id_seq'::regclass);


--
-- Name: event_participants id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.event_participants ALTER COLUMN id SET DEFAULT nextval('public.event_participants_id_seq'::regclass);


--
-- Name: events id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.events ALTER COLUMN id SET DEFAULT nextval('public.events_id_seq'::regclass);


--
-- Name: families id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.families ALTER COLUMN id SET DEFAULT nextval('public.families_id_seq'::regclass);


--
-- Name: folders id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.folders ALTER COLUMN id SET DEFAULT nextval('public.folders_id_seq'::regclass);


--
-- Name: gaming_devices id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.gaming_devices ALTER COLUMN id SET DEFAULT nextval('public.gaming_devices_id_seq'::regclass);


--
-- Name: gaming_session_devices id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.gaming_session_devices ALTER COLUMN id SET DEFAULT nextval('public.gaming_session_devices_id_seq'::regclass);


--
-- Name: gaming_sessions id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.gaming_sessions ALTER COLUMN id SET DEFAULT nextval('public.gaming_sessions_id_seq'::regclass);


--
-- Name: goal_days id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.goal_days ALTER COLUMN id SET DEFAULT nextval('public.goal_days_id_seq'::regclass);


--
-- Name: goals id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.goals ALTER COLUMN id SET DEFAULT nextval('public.goals_id_seq'::regclass);


--
-- Name: log_events id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.log_events ALTER COLUMN id SET DEFAULT nextval('public.log_events_id_seq'::regclass);


--
-- Name: log_items id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.log_items ALTER COLUMN id SET DEFAULT nextval('public.log_items_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: permissions id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.permissions ALTER COLUMN id SET DEFAULT nextval('public.permissions_id_seq'::regclass);


--
-- Name: recurring_tasks id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.recurring_tasks ALTER COLUMN id SET DEFAULT nextval('public.recurring_tasks_id_seq'::regclass);


--
-- Name: roles id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.roles ALTER COLUMN id SET DEFAULT nextval('public.roles_id_seq'::regclass);


--
-- Name: schedules id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.schedules ALTER COLUMN id SET DEFAULT nextval('public.scheduled_backups_id_seq'::regclass);


--
-- Name: site_images id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.site_images ALTER COLUMN id SET DEFAULT nextval('public.site_images_id_seq'::regclass);


--
-- Name: sites id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sites ALTER COLUMN id SET DEFAULT nextval('public.sites_id_seq'::regclass);


--
-- Name: taggables id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.taggables ALTER COLUMN id SET DEFAULT nextval('public.taggables_id_seq'::regclass);


--
-- Name: tags id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tags ALTER COLUMN id SET DEFAULT nextval('public.tags_id_seq'::regclass);


--
-- Name: targets id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.targets ALTER COLUMN id SET DEFAULT nextval('public.targets_id_seq'::regclass);


--
-- Name: task_user_configs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.task_user_configs ALTER COLUMN id SET DEFAULT nextval('public.task_user_configs_id_seq'::regclass);


--
-- Name: tasks id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tasks ALTER COLUMN id SET DEFAULT nextval('public.tasks_id_seq'::regclass);


--
-- Name: user_configs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_configs ALTER COLUMN id SET DEFAULT nextval('public.user_configs_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Name: backup_jobs backup_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.backup_jobs
    ADD CONSTRAINT backup_jobs_pkey PRIMARY KEY (id);


--
-- Name: backup_schedule backup_schedule_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.backup_schedule
    ADD CONSTRAINT backup_schedule_pkey PRIMARY KEY (id);


--
-- Name: backup_step_jobs backup_step_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.backup_step_jobs
    ADD CONSTRAINT backup_step_jobs_pkey PRIMARY KEY (id);


--
-- Name: backup_steps backup_steps_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.backup_steps
    ADD CONSTRAINT backup_steps_pkey PRIMARY KEY (id);


--
-- Name: backups backups_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.backups
    ADD CONSTRAINT backups_pkey PRIMARY KEY (id);


--
-- Name: draft_admins draft_admins_draft_id_user_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.draft_admins
    ADD CONSTRAINT draft_admins_draft_id_user_id_unique UNIQUE (draft_id, user_id);


--
-- Name: draft_admins draft_admins_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.draft_admins
    ADD CONSTRAINT draft_admins_pkey PRIMARY KEY (id);


--
-- Name: draft_images draft_images_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.draft_images
    ADD CONSTRAINT draft_images_pkey PRIMARY KEY (id);


--
-- Name: draft_members draft_members_draft_id_pick_position_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.draft_members
    ADD CONSTRAINT draft_members_draft_id_pick_position_unique UNIQUE (draft_id, pick_position);


--
-- Name: draft_members draft_members_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.draft_members
    ADD CONSTRAINT draft_members_pkey PRIMARY KEY (id);


--
-- Name: draft_picks draft_picks_draft_id_draft_team_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.draft_picks
    ADD CONSTRAINT draft_picks_draft_id_draft_team_id_unique UNIQUE (draft_id, draft_team_id);


--
-- Name: draft_picks draft_picks_draft_id_pick_number_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.draft_picks
    ADD CONSTRAINT draft_picks_draft_id_pick_number_unique UNIQUE (draft_id, pick_number);


--
-- Name: draft_picks draft_picks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.draft_picks
    ADD CONSTRAINT draft_picks_pkey PRIMARY KEY (id);


--
-- Name: draft_teams draft_teams_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.draft_teams
    ADD CONSTRAINT draft_teams_pkey PRIMARY KEY (id);


--
-- Name: drafts drafts_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.drafts
    ADD CONSTRAINT drafts_pkey PRIMARY KEY (id);


--
-- Name: drafts drafts_token_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.drafts
    ADD CONSTRAINT drafts_token_unique UNIQUE (token);


--
-- Name: event_participants event_participants_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.event_participants
    ADD CONSTRAINT event_participants_pkey PRIMARY KEY (id);


--
-- Name: events events_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.events
    ADD CONSTRAINT events_pkey PRIMARY KEY (id);


--
-- Name: families families_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.families
    ADD CONSTRAINT families_pkey PRIMARY KEY (id);


--
-- Name: folders folders_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.folders
    ADD CONSTRAINT folders_pkey PRIMARY KEY (id);


--
-- Name: gaming_devices gaming_devices_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.gaming_devices
    ADD CONSTRAINT gaming_devices_pkey PRIMARY KEY (id);


--
-- Name: gaming_session_devices gaming_session_devices_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.gaming_session_devices
    ADD CONSTRAINT gaming_session_devices_pkey PRIMARY KEY (id);


--
-- Name: gaming_sessions gaming_sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.gaming_sessions
    ADD CONSTRAINT gaming_sessions_pkey PRIMARY KEY (id);


--
-- Name: goal_days goal_days_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.goal_days
    ADD CONSTRAINT goal_days_pkey PRIMARY KEY (id);


--
-- Name: goals goals_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.goals
    ADD CONSTRAINT goals_pkey PRIMARY KEY (id);


--
-- Name: log_events log_events_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.log_events
    ADD CONSTRAINT log_events_pkey PRIMARY KEY (id);


--
-- Name: log_items log_items_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.log_items
    ADD CONSTRAINT log_items_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: model_has_permissions model_has_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.model_has_permissions
    ADD CONSTRAINT model_has_permissions_pkey PRIMARY KEY (permission_id, model_id, model_type);


--
-- Name: model_has_roles model_has_roles_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.model_has_roles
    ADD CONSTRAINT model_has_roles_pkey PRIMARY KEY (role_id, model_id, model_type);


--
-- Name: permissions permissions_name_guard_name_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_name_guard_name_unique UNIQUE (name, guard_name);


--
-- Name: permissions permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_pkey PRIMARY KEY (id);


--
-- Name: recurring_tasks recurring_tasks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.recurring_tasks
    ADD CONSTRAINT recurring_tasks_pkey PRIMARY KEY (id);


--
-- Name: role_has_permissions role_has_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_pkey PRIMARY KEY (permission_id, role_id);


--
-- Name: roles roles_name_guard_name_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_name_guard_name_unique UNIQUE (name, guard_name);


--
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- Name: schedules scheduled_backups_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.schedules
    ADD CONSTRAINT scheduled_backups_pkey PRIMARY KEY (id);


--
-- Name: site_images site_images_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.site_images
    ADD CONSTRAINT site_images_pkey PRIMARY KEY (id);


--
-- Name: sites sites_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sites
    ADD CONSTRAINT sites_pkey PRIMARY KEY (id);


--
-- Name: taggables taggables_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.taggables
    ADD CONSTRAINT taggables_pkey PRIMARY KEY (id);


--
-- Name: tags tags_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tags
    ADD CONSTRAINT tags_pkey PRIMARY KEY (id);


--
-- Name: targets targets_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.targets
    ADD CONSTRAINT targets_pkey PRIMARY KEY (id);


--
-- Name: task_user_configs task_user_configs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.task_user_configs
    ADD CONSTRAINT task_user_configs_pkey PRIMARY KEY (id);


--
-- Name: tasks tasks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tasks
    ADD CONSTRAINT tasks_pkey PRIMARY KEY (id);


--
-- Name: user_configs user_configs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_configs
    ADD CONSTRAINT user_configs_pkey PRIMARY KEY (id);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: users users_username_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_username_unique UNIQUE (username);


--
-- Name: backup_jobs_backup_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX backup_jobs_backup_id_index ON public.backup_jobs USING btree (backup_id);


--
-- Name: backup_jobs_schedule_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX backup_jobs_schedule_id_index ON public.backup_jobs USING btree (schedule_id);


--
-- Name: backup_jobs_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX backup_jobs_user_id_index ON public.backup_jobs USING btree (user_id);


--
-- Name: backup_schedule_backup_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX backup_schedule_backup_id_index ON public.backup_schedule USING btree (backup_id);


--
-- Name: backup_schedule_schedule_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX backup_schedule_schedule_id_index ON public.backup_schedule USING btree (schedule_id);


--
-- Name: backup_step_jobs_backup_job_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX backup_step_jobs_backup_job_id_index ON public.backup_step_jobs USING btree (backup_job_id);


--
-- Name: backup_step_jobs_backup_step_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX backup_step_jobs_backup_step_id_index ON public.backup_step_jobs USING btree (backup_step_id);


--
-- Name: backup_step_jobs_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX backup_step_jobs_user_id_index ON public.backup_step_jobs USING btree (user_id);


--
-- Name: backup_steps_backup_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX backup_steps_backup_id_index ON public.backup_steps USING btree (backup_id);


--
-- Name: backup_steps_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX backup_steps_user_id_index ON public.backup_steps USING btree (user_id);


--
-- Name: backups_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX backups_user_id_index ON public.backups USING btree (user_id);


--
-- Name: draft_admins_draft_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX draft_admins_draft_id_index ON public.draft_admins USING btree (draft_id);


--
-- Name: draft_admins_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX draft_admins_user_id_index ON public.draft_admins USING btree (user_id);


--
-- Name: draft_images_created_by_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX draft_images_created_by_id_index ON public.draft_images USING btree (created_by_id);


--
-- Name: draft_members_draft_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX draft_members_draft_id_index ON public.draft_members USING btree (draft_id);


--
-- Name: draft_members_secret_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX draft_members_secret_index ON public.draft_members USING btree (secret);


--
-- Name: draft_picks_draft_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX draft_picks_draft_id_index ON public.draft_picks USING btree (draft_id);


--
-- Name: draft_picks_draft_member_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX draft_picks_draft_member_id_index ON public.draft_picks USING btree (draft_member_id);


--
-- Name: draft_picks_draft_team_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX draft_picks_draft_team_id_index ON public.draft_picks USING btree (draft_team_id);


--
-- Name: draft_teams_draft_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX draft_teams_draft_id_index ON public.draft_teams USING btree (draft_id);


--
-- Name: drafts_created_by_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX drafts_created_by_id_index ON public.drafts USING btree (created_by_id);


--
-- Name: drafts_draft_image_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX drafts_draft_image_id_index ON public.drafts USING btree (draft_image_id);


--
-- Name: event_participants_event_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX event_participants_event_id_index ON public.event_participants USING btree (event_id);


--
-- Name: events_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX events_user_id_index ON public.events USING btree (user_id);


--
-- Name: folders_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX folders_user_id_index ON public.folders USING btree (user_id);


--
-- Name: gaming_session_devices_gaming_device_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX gaming_session_devices_gaming_device_id_index ON public.gaming_session_devices USING btree (gaming_device_id);


--
-- Name: gaming_session_devices_gaming_session_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX gaming_session_devices_gaming_session_id_index ON public.gaming_session_devices USING btree (gaming_session_id);


--
-- Name: goal_days_goal_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX goal_days_goal_id_index ON public.goal_days USING btree (goal_id);


--
-- Name: goal_days_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX goal_days_user_id_index ON public.goal_days USING btree (user_id);


--
-- Name: goals_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX goals_user_id_index ON public.goals USING btree (user_id);


--
-- Name: log_items_log_event_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX log_items_log_event_id_index ON public.log_items USING btree (log_event_id);


--
-- Name: model_has_permissions_model_id_model_type_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX model_has_permissions_model_id_model_type_index ON public.model_has_permissions USING btree (model_id, model_type);


--
-- Name: model_has_roles_model_id_model_type_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX model_has_roles_model_id_model_type_index ON public.model_has_roles USING btree (model_id, model_type);


--
-- Name: recurring_tasks_owner_type_owner_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX recurring_tasks_owner_type_owner_id_index ON public.recurring_tasks USING btree (owner_type, owner_id);


--
-- Name: scheduled_backups_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX scheduled_backups_user_id_index ON public.schedules USING btree (user_id);


--
-- Name: site_images_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX site_images_user_id_index ON public.site_images USING btree (user_id);


--
-- Name: sites_folder_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sites_folder_id_index ON public.sites USING btree (folder_id);


--
-- Name: sites_site_image_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sites_site_image_id_index ON public.sites USING btree (site_image_id);


--
-- Name: sites_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sites_user_id_index ON public.sites USING btree (user_id);


--
-- Name: taggables_tag_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX taggables_tag_id_index ON public.taggables USING btree (tag_id);


--
-- Name: taggables_taggable_type_taggable_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX taggables_taggable_type_taggable_id_index ON public.taggables USING btree (taggable_type, taggable_id);


--
-- Name: tags_tag_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX tags_tag_index ON public.tags USING btree (tag);


--
-- Name: targets_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX targets_user_id_index ON public.targets USING btree (user_id);


--
-- Name: task_user_configs_family_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX task_user_configs_family_id_index ON public.task_user_configs USING btree (family_id);


--
-- Name: task_user_configs_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX task_user_configs_user_id_index ON public.task_user_configs USING btree (user_id);


--
-- Name: tasks_completed_by_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX tasks_completed_by_id_index ON public.tasks USING btree (completed_by_id);


--
-- Name: tasks_owner_type_owner_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX tasks_owner_type_owner_id_index ON public.tasks USING btree (owner_type, owner_id);


--
-- Name: tasks_recurring_task_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX tasks_recurring_task_id_index ON public.tasks USING btree (recurring_task_id);


--
-- Name: user_configs_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX user_configs_user_id_index ON public.user_configs USING btree (user_id);


--
-- Name: model_has_permissions model_has_permissions_permission_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.model_has_permissions
    ADD CONSTRAINT model_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- Name: model_has_roles model_has_roles_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.model_has_roles
    ADD CONSTRAINT model_has_roles_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- Name: role_has_permissions role_has_permissions_permission_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- Name: role_has_permissions role_has_permissions_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

\unrestrict PwbIkXPKOBBkJeWzu9J2u6y9Bm8iz186UwKdMPWOqriQCXUO9MeKgQrOWLBGlzF

--
-- PostgreSQL database dump
--

\restrict Xf4gIuVudk4LLpaWb0YG8vnSPcvHI0OkBbkGEewP4hXyttyAmyWbIpGpMQkG7vK

-- Dumped from database version 16.14 (Debian 16.14-1.pgdg13+1)
-- Dumped by pg_dump version 17.11 (Debian 17.11-0+deb13u1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	2014_10_12_000000_create_users_table	1
2	2022_02_25_012352_create_permission_tables	1
3	2022_02_26_234719_add_last_logged_in_to_users	1
4	2022_03_08_033304_create_goals_table	1
5	2022_03_08_033313_create_goal_days_table	1
6	2022_04_20_030927_create_targets_table	1
7	2022_04_20_031013_create_scheduled_backups_table	1
8	2022_04_20_031035_create_scheduled_backup_steps_table	1
9	2022_04_20_031110_create_backups_table	1
10	2022_04_20_031122_create_backup_steps_table	1
11	2022_05_08_002233_add_target_host_config	1
12	2022_05_13_022216_create_folders_table	1
13	2022_05_13_022224_create_sites_table	1
14	2022_05_13_022327_create_images_table	1
15	2022_05_13_224726_fix_s3_path_on_images	1
16	2022_05_14_002926_create_user_configs_table	1
17	2022_05_19_040447_change_scheduled_backups_dates	1
18	2022_05_20_070947_change_scheduled_backups_full_every	1
19	2022_05_23_023642_create_families_table	1
20	2022_05_23_023750_create_tasks_table	1
21	2022_05_23_023823_create_recurring_tasks_table	1
22	2022_05_24_003152_create_tags_table	1
23	2022_05_24_003209_create_tag_entity_table	1
24	2022_05_24_005251_create_task_user_configs_table	1
25	2022_06_07_015828_add_completed_by_to_tasks	1
26	2022_06_07_225817_add_colors_to_families	1
27	2022_06_08_031102_remove_nullable_from_colors	1
28	2022_06_08_041133_change_dates_to_timestamp_on_tasks	1
29	2022_06_12_202751_create_events_table	1
30	2022_06_12_202806_create_event_participants_table	1
31	2022_07_22_224251_change_event_tables	1
32	2022_07_25_181522_add_home_page_role_to_user_configs	1
33	2022_07_31_190806_add_emails_for_events	1
34	2022_09_18_185102_create_task_points_table	1
35	2022_10_27_223213_add_column_to_recurring_tasks	1
36	2022_11_23_001630_add_priority_to_tasks	1
37	2023_01_21_080211_create_family_settings_table	1
38	2023_02_24_224902_create_logging_tables	1
39	2023_03_14_235811_add_task_points_to_families	1
40	2023_03_20_053300_add_task_point_to_tasks	1
41	2023_03_20_062107_remove_task_points_table	1
42	2023_03_20_212105_update_family_settings	1
43	2023_03_20_234350_remove_nullable_for_task_user_configs	1
44	2023_08_26_184443_add_money_api_token_to_user_config	1
45	2024_02_21_234448_allow_sites_sort_to_be_nullable	1
46	2024_05_29_222752_add_default_task_points_to_task_user_config	1
47	2024_08_06_215019_backup-changes	1
48	2024_08_06_233551_changes-to-targets	1
49	2024_10_02_031014_create_gaming_sessions_table	1
50	2024_10_02_031448_create_gaming_devices_table	1
51	2024_10_02_031459_create_gaming_session_devices_table	1
52	2024_10_11_205842_session_dates_nullable	1
53	2025_01_28_221546_backup_table_changes	1
54	2025_01_29_204111_fix_backup_jobs_column_name	1
55	2025_02_13_212740_add_soft_deletes_to_backup_steps	1
56	2025_02_14_195843_add_sort_to_backup_step_jobs	1
57	2026_08_10_061700_create_drafts_table	1
58	2026_08_10_061701_create_draft_admins_table	1
59	2026_08_10_061702_create_draft_teams_table	1
60	2026_08_10_061703_create_draft_members_table	1
61	2026_08_10_061704_create_draft_picks_table	1
62	2026_08_11_000001_add_claimed_at_to_draft_members_table	1
63	2026_08_13_000001_create_draft_images_table	1
64	2026_08_13_000002_add_draft_image_id_to_drafts_table	1
\.


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.migrations_id_seq', 64, true);


--
-- PostgreSQL database dump complete
--

\unrestrict Xf4gIuVudk4LLpaWb0YG8vnSPcvHI0OkBbkGEewP4hXyttyAmyWbIpGpMQkG7vK

