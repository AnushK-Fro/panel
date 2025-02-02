<?php

namespace Convoy\Services\Servers;

use Convoy\Data\Server\Proxmox\Config\DiskData;
use Convoy\Enums\Server\DiskInterface;
use Convoy\Enums\Server\PowerAction;
use Convoy\Models\Server;
use Convoy\Repositories\Proxmox\Server\ProxmoxConfigRepository;
use Convoy\Repositories\Proxmox\Server\ProxmoxDiskRepository;
use Convoy\Repositories\Proxmox\Server\ProxmoxPowerRepository;
use Illuminate\Support\Arr;

class SyncBuildService
{
    public function __construct(
        private AllocationService       $allocationService,
        private CloudinitService        $cloudinitService,
        private NetworkService          $networkService,
        private ServerDetailService     $detailService,
        private ProxmoxPowerRepository  $powerRepository,
        private ProxmoxConfigRepository $allocationRepository,
        private ProxmoxDiskRepository   $diskRepository,
    )
    {
    }

    public function handle(Server $server)
    {
        $this->allocationRepository->setServer($server);

        $eloquentDetails = $this->detailService->getByEloquent($server);
        $disks = $this->allocationService->getDisks($server);
        $bootOrder = $this->allocationService->getBootOrder($server);

        $this->allocationService->syncSettings($server);

        /* Sync metadata */
        $this->cloudinitService->updateHostname($server, $eloquentDetails->hostname);

        /* Sync network configuration */
        $this->networkService->syncSettings($server);

        // find a disk that has a corresponding disk in the deployment
        $disksArray = collect($disks->toArray())->pluck('interface')->all();
        $bootOrder = array_filter(collect($bootOrder->filter(fn(DiskData $disk) => !$disk->is_media)->toArray())->pluck('interface')->toArray(), fn($disk) => in_array($disk, $disksArray));

        if (count($bootOrder) > 0) {
            /** @var DiskData $disk */
            $disk = $disks->where('interface', '=', DiskInterface::from(Arr::first($bootOrder)))->first();

            $diff = $server->disk - $disk->size;

            if ($diff > 0) {
                $this->diskRepository->setServer($server)->resizeDisk($disk->interface, $diff);
            }
        }
    }
}
