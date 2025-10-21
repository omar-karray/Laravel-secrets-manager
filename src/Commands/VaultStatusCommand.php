<?php

namespace Deepdigs\LaravelVaultSuite\Commands;

use Deepdigs\LaravelVaultSuite\LaravelVaultSuite;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class VaultStatusCommand extends Command
{
    protected $signature = 'vault:status {--driver=vault : Secrets backend driver to target}';

    protected $description = 'Display the seal status for the configured vault backend';

    public function handle(LaravelVaultSuite $vault): int
    {
        $status = $vault->sealStatus([], $this->option('driver'));

        $this->components->twoColumnDetail('Driver', $this->option('driver'));
        $this->components->twoColumnDetail('Sealed', Arr::get($status, 'sealed') ? 'yes' : 'no');
        $this->components->twoColumnDetail('Progress', sprintf('%s/%s', Arr::get($status, 'progress', '?'), Arr::get($status, 't', '?')));
        $this->components->twoColumnDetail('Initialized', Arr::get($status, 'initialized') ? 'yes' : 'no');
        $this->components->twoColumnDetail('Cluster', Arr::get($status, 'cluster_name', 'n/a'));
        $this->components->twoColumnDetail('Version', Arr::get($status, 'version', 'n/a'));

        return self::SUCCESS;
    }
}
