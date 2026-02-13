<?php
declare(strict_types=1);

namespace Database\Seeders\Traits;

trait DisablesForeignKeyChecking
{
    protected function disableForeignKeyChecking(): void
    {
        // SQLite does not support FOREIGN KEY CHECKS
        if (\DB::connection()->getDriverName() === 'sqlite') {
            return;
        }
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
    }

    protected function enableForeignKeyChecking(): void
    {
        // SQLite does not support FOREIGN KEY CHECKS
        if (\DB::connection()->getDriverName() === 'sqlite') {
            return;
        }
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
