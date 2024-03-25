<?php

namespace App\Repositories\Tasks;

use App\Models\Tasks\Family;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskUserConfig;
use App\Models\Users\User;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Mbarclay36\LaravelCrud\DefaultRepository;

class FamilyMemberStatsRepository extends DefaultRepository
{
    /**
     * @param $request
     * @param Authenticatable $user
     * @param bool $viewOnlyForUser
     * @return Collection|array
     */
    public function getEntities($request, Authenticatable $user, bool $viewOnlyForUser): Collection|array
    {
        $familyId = $request['familyId'];
        $startDate = Carbon::today('America/Los_Angeles')->setDate($request['year'], 1, 1);
        $endDate = (clone $startDate)->addYear();
        /** @var User[] $familyMembers */
        $familyMembers = User::query()
                             ->whereHas('family', function ($query) use ($familyId) {
                                 $query->where('family_id', '=', $familyId);
                             })
                             ->orderBy('id')
                             ->get();

        $topThree = DB::table(function ($query) use ($startDate, $endDate, $familyId) {
            $query->selectRaw("completed_by_id, recurring_task_id, count(*), row_number() over (partition by completed_by_id order by count(*) desc) as rank")
                  ->from('tasks')
                  ->whereNotNull('completed_at')
                  ->whereNotNull('recurring_task_id')
                  ->where('completed_at', '>', $startDate->utc())
                  ->where('completed_at', '<', $endDate->utc())
                  ->where('owner_id', '=', $familyId)
                  ->where('owner_type', '=', Family::class)
                  ->groupByRaw("completed_by_id, recurring_task_id");
        }, 'ranked')
                      ->join('recurring_tasks', 'recurring_tasks.id', '=', 'ranked.recurring_task_id')
                      ->selectRaw("completed_by_id, recurring_tasks.name as task_name, ranked.count")
                      ->where('ranked.rank', '<=', '3')
                      ->get();

        $counts = Task::query()
                      ->selectRaw("completed_by_id, count(*), sum(task_point)")
                      ->whereNotNull('completed_at')
                      ->whereNotNull('recurring_task_id')
                      ->where('completed_at', '>', $startDate)
                      ->where('completed_at', '<', $endDate)
                      ->where('owner_id', '=', $familyId)
                      ->where('owner_type', '=', Family::class)
                      ->groupBy('completed_by_id')
                      ->get();

        $expectedEndDate = $endDate;
        $today = Carbon::today('America/Los_Angeles');
        if ($expectedEndDate->greaterThan($today)) {
            $expectedEndDate = $today;
        }
        $expectedPointsCount = TaskUserConfig::query()
                                             ->selectRaw("user_id, sum(tasks_per_week)")
                                             ->where('family_id', '=', $familyId)
                                             ->where('start_date', '>', $startDate)
                                             ->where('start_date', '<', $expectedEndDate)
                                             ->groupBy('user_id')
                                             ->get();

        foreach ($familyMembers as $member) {
            $topTasks = $topThree
                ->filter(function ($item) use ($member) {
                    return $item->completed_by_id == $member->id;
                })
                ->map(function ($item) {
                    return [
                        'taskName' => $item->task_name,
                        'count' => $item->count
                    ];
                })
                ->values();
            $member->setAttribute('topTasks', $topTasks);
            $membersCounts = $counts->where('completed_by_id', '=', $member->id)->first();
            $memberExpectedCounts = $expectedPointsCount->where('user_id', '=', $member->id)->first();
            if ($membersCounts) {
                $member->setAttribute('totalTasks', $membersCounts->count);
                $member->setAttribute('totalEarnedPoints', $membersCounts->sum);
                $member->setAttribute('totalExpectedPoints', $memberExpectedCounts->sum);
            }
        }

        return $familyMembers;
    }
}
