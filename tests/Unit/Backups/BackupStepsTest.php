<?php

namespace Tests\Unit\Backups;

use App\Models\Backups\Backup;
use App\Models\Backups\BackupStep;
use App\Models\Backups\Target;
use App\Models\Users\User;
use App\Services\Backups\BackupStepTypes\S3UploadBackupStepType;
use App\Services\Backups\BackupStepTypes\TarZipBackupStepType;
use App\Services\Backups\RunBackupService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BackupStepsTest extends TestCase
{

    public function testUnknownBackupStepType(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $backup = Backup::factory()->create([
            'user_id' => $user->id
        ]);

        BackupStep::factory()->create([
            'backup_step_type' => 'testing',
            'user_id' => $user->id,
            'backup_id' => $backup->id,
        ]);
        $backupJob = (new RunBackupService($backup))->run();
        $backupStepJob = $backupJob->backupStepJobs()->first();

        $this->assertNotNull($backupStepJob->started_at);
        $this->assertNotNull($backupStepJob->errored_at);
        $this->assertNotNull($backupStepJob->error_message);
        $this->assertNull($backupStepJob->completed_at);
    }

    public function testTarZipBackupStepType(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $base = base_path();
        /** @var Target $badSourceTarget */
        $badSourceTarget = Target::factory()->create([
            'target_url' => "{$base}/tests/Data/doesntExist/",
            'user_id' => $user->id
        ]);
        /** @var Target $sourceTarget */
        $sourceTarget = Target::factory()->create([
            'target_url' => "{$base}/tests/Data/tarTestData/",
            'user_id' => $user->id
        ]);
        /** @var Target $destinationTarget */
        $destinationTarget = Target::factory()->create([
            'target_url' => "{$base}/tests/Data/",
            'user_id' => $user->id
        ]);
        /** @var Backup $backup */
        $backup = Backup::factory()->create([
            'user_id' => $user->id
        ]);

        // BAD CONFIG, SHOULD ERROR
        $backupStep = BackupStep::factory()->create([
            'backup_step_type' => TarZipBackupStepType::$BACKUP_STEP_TYPE,
            'user_id' => $user->id,
            'backup_id' => $backup->id,
            'config' => []
        ]);

        $backupJob = (new RunBackupService($backup))->run();
        $backupStepJob = $backupJob->backupStepJobs()->first();

        $this->assertNotNull($backupStepJob->started_at);
        $this->assertNotNull($backupStepJob->errored_at);
        $this->assertNotNull($backupStepJob->error_message);
        $this->assertNull($backupStepJob->completed_at);


        //GOOD CONFIG USING BAD SOURCE, SHOULD ERROR
        $fileName = 'tarTest.tar.gz';
        $backupStep->config = [
            'sourceTargetId' => $badSourceTarget->id,
            'destinationTargetId' => $destinationTarget->id,
            'fileName' => $fileName
        ];
        $backupStep->save();

        $backupJob = (new RunBackupService($backup))->run();
        $backupStepJob = $backupJob->backupStepJobs()->first();

        $this->assertNotNull($backupStepJob->started_at);
        $this->assertNotNull($backupStepJob->errored_at);
        $this->assertNotNull($backupStepJob->error_message);
        $this->assertNull($backupStepJob->completed_at);

        //GOOD CONFIG, SHOULD PASS
        $backupStep->config = [
            'sourceTargetId' => $sourceTarget->id,
            'destinationTargetId' => $destinationTarget->id,
            'fileName' => $fileName
        ];
        $backupStep->save();

        $backupJob = (new RunBackupService($backup))->run();
        $backupStepJob = $backupJob->backupStepJobs()->first();

        $this->assertNotNull($backupStepJob->started_at);
        $this->assertNull($backupStepJob->errored_at);
        $this->assertNull($backupStepJob->error_message);
        $this->assertNotNull($backupStepJob->completed_at);
        $this->assertFileExists("$base/tests/Data/$fileName");
        // delete the file when the test is done
        `rm $base/tests/Data/$fileName`;
    }

    public function testMinioS3BackupStepType()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $base = base_path();
        /** @var Target $badSourceTarget */
        $badSourceTarget = Target::factory()->create([
            'target_url' => "{$base}/tests/Data/doesntExist/",
            'user_id' => $user->id
        ]);
        /** @var Target $sourceTarget */
        $sourceTarget = Target::factory()->create([
            'target_url' => "{$base}/tests/Data/s3UploadTestData/",
            'user_id' => $user->id
        ]);
        /** @var Target $destinationTarget */
        $destinationTarget = Target::factory()->create([
            'target_url' => "testing/",
            'user_id' => $user->id
        ]);
        /** @var Backup $backup */
        $backup = Backup::factory()->create([
            'user_id' => $user->id
        ]);

        // BAD CONFIG, SHOULD ERROR
        $backupStep = BackupStep::factory()->create([
            'backup_step_type' => S3UploadBackupStepType::$BACKUP_STEP_TYPE,
            'user_id' => $user->id,
            'backup_id' => $backup->id,
            'config' => []
        ]);

        $backupJob = (new RunBackupService($backup))->run();
        $backupStepJob = $backupJob->backupStepJobs()->first();

        $this->assertNotNull($backupStepJob->started_at);
        $this->assertNotNull($backupStepJob->errored_at);
        $this->assertNotNull($backupStepJob->error_message);
        $this->assertNull($backupStepJob->completed_at);

        //GOOD CONFIG USING BAD SOURCE, SHOULD ERROR
        $fileName = 'test.txt';
        $backupStep->config = [
            'sourceTargetId' => $badSourceTarget->id,
            'destinationTargetId' => $destinationTarget->id,
            'fileName' => $fileName,
            's3Driver' => 'minio-s3'
        ];
        $backupStep->save();

        $backupJob = (new RunBackupService($backup))->run();
        $backupStepJob = $backupJob->backupStepJobs()->first();

        $this->assertNotNull($backupStepJob->started_at);
        $this->assertNotNull($backupStepJob->errored_at);
        $this->assertNotNull($backupStepJob->error_message);
        $this->assertNull($backupStepJob->completed_at);


        //GOOD CONFIG, SHOULD PASS
        $backupStep->config = [
            'sourceTargetId' => $sourceTarget->id,
            'destinationTargetId' => $destinationTarget->id,
            'fileName' => $fileName,
            's3Driver' => 'minio-s3'
        ];
        $backupStep->save();

        $backupJob = (new RunBackupService($backup))->run();
        $backupStepJob = $backupJob->backupStepJobs()->first();

        $this->assertNotNull($backupStepJob->started_at);
        $this->assertNull($backupStepJob->errored_at);
        $this->assertNull($backupStepJob->error_message);
        $this->assertNotNull($backupStepJob->completed_at);
        $this->assertTrue(Storage::disk('minio-s3')->has("testing/$fileName"));

        Storage::disk('minio-s3')->delete("testing/$fileName");
    }
}
