<?php

namespace Convoy\Jobs\Server;

use Convoy\Enums\Server\Status;
use Convoy\Models\Server;
use Convoy\Repositories\Proxmox\Server\ProxmoxServerRepository;
use Convoy\Services\Servers\ServerBuildService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\Middleware\SkipIfBatchCancelled;
use Throwable;

class DeleteServerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public $timeout = 20;

    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(protected int $serverId)
    {
        //
    }

    public function middleware(): array
    {
        return [new SkipIfBatchCancelled, new WithoutOverlapping("server.delete-{$this->serverId}")];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ServerBuildService $service)
    {
        $server = Server::findOrFail($this->serverId);

        $service->delete($server);
    }
}
