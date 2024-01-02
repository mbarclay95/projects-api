select
	u.name,
	rt.name,
	ranked.count
from
	(
	select
		completed_by_id ,
		recurring_task_id,
		count(*),
		row_number() over (partition by completed_by_id
	order by
		count(*) desc) as rank
	from
		tasks t
	where
		completed_at is not null
		and completed_at > '2023-01-01'
		and completed_at < '2024-01-01'
		and owner_type = 'App\Models\Tasks\Family'
		and owner_id = 1
		and recurring_task_id is not null
	group by
		completed_by_id,
		recurring_task_id
) as ranked
join users u on
	u.id = ranked.completed_by_id
join recurring_tasks rt on
	rt.id = ranked.recurring_task_id
where
	(ranked.rank <= 3
		or ranked.count = 1)

select
	u.name,
	count(*),
	sum(task_point)
from
	tasks t
join users u on
	u.id = t.completed_by_id
where
	completed_at is not null
	and completed_at > '2023-01-01'
	and completed_at < '2024-01-01'
	and owner_type = 'App\Models\Tasks\Family'
	and owner_id = 1
	and recurring_task_id is not null
group by
	u.name