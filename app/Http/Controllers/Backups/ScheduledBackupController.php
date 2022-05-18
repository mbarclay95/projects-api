<?php

namespace App\Http\Controllers\Backups;

use App\Http\Controllers\Controller;
use App\Models\Backups\ScheduledBackup;
use Illuminate\Http\Request;

class ScheduledBackupController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(ScheduledBackup::class, 'scheduled-backup');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Backups\ScheduledBackup  $scheduledBackup
     * @return \Illuminate\Http\Response
     */
    public function show(ScheduledBackup $scheduledBackup)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Backups\ScheduledBackup  $scheduledBackup
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ScheduledBackup $scheduledBackup)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Backups\ScheduledBackup  $scheduledBackup
     * @return \Illuminate\Http\Response
     */
    public function destroy(ScheduledBackup $scheduledBackup)
    {
        //
    }
}
