<?php

namespace App\Console\Commands;

use App\Services\MaterialAhspSyncService;
use Illuminate\Console\Command;

class SyncMaterialAhsp extends Command
{
    protected $signature = 'ahsp:sync-materials 
                            {--direction=both : Sync direction: materials-to-ahsp, ahsp-to-materials, or both}
                            {--region=ID-JK : Region code for AHSP prices}
                            {--link-only : Only auto-link existing records without creating new ones}';

    protected $description = 'Synchronize materials with AHSP base prices';

    public function handle(MaterialAhspSyncService $syncService): int
    {
        $direction = $this->option('direction');
        $region = $this->option('region');
        $linkOnly = $this->option('link-only');

        if ($linkOnly) {
            $this->info('Auto-linking AHSP prices to existing materials...');
            $linked = $syncService->autoLinkAhspToMaterials();
            $this->info("✓ Linked {$linked} AHSP prices to existing materials");
            return Command::SUCCESS;
        }

        if ($direction === 'materials-to-ahsp' || $direction === 'both') {
            $this->info("Syncing Materials → AHSP Base Prices (Region: {$region})...");
            $synced = $syncService->syncAllMaterialsToAhsp($region);
            $this->info("✓ Synced {$synced->count()} materials to AHSP");
        }

        if ($direction === 'ahsp-to-materials' || $direction === 'both') {
            $this->info('Syncing AHSP Base Prices → Materials...');
            $synced = $syncService->syncAllAhspToMaterials();
            $this->info("✓ Synced {$synced->count()} AHSP prices to materials");
        }

        $this->newLine();
        $this->info('Sync completed successfully!');

        return Command::SUCCESS;
    }
}
