<?php

namespace Convoy\Repositories\Proxmox\Server;

use Convoy\Data\Server\Proxmox\ServerStateData;
use Convoy\Enums\Node\Access\RealmType;
use Convoy\Exceptions\Repository\Proxmox\ProxmoxConnectionException;
use Convoy\Models\Server;
use Convoy\Models\Template;
use Convoy\Repositories\Proxmox\ProxmoxRepository;
use Webmozart\Assert\Assert;

class ProxmoxServerRepository extends ProxmoxRepository
{
    /**
     * @throws ProxmoxConnectionException
     */
    public function getState()
    {
        Assert::isInstanceOf($this->server, Server::class);

        $response = $this->getHttpClient()
            ->withUrlParameters([
                'node' => $this->node->cluster,
                'server' => $this->server->vmid
            ])
            ->get('/api2/json/nodes/{node}/qemu/{server}/status/current')
            ->json();

        return ServerStateData::fromRaw($this->getData($response));
    }

    public function create(Template $template)
    {
        Assert::isInstanceOf($this->server, Server::class);

        $response = $this->getHttpClient()
            ->withUrlParameters([
                'node' => $this->node->cluster,
                'template' => $template->vmid
            ])
            ->post('/api2/json/nodes/{node}/qemu/{template}/clone', [
                'target' => $this->node->cluster,
                'newid' => $this->server->vmid,
                'full' => true,
            ])
            ->json();

        return $this->getData($response);
    }

    public function delete()
    {
        Assert::isInstanceOf($this->server, Server::class);

        $response = $this->getHttpClient(options: [
            'query' => [
                'destroy-unreferenced-disks' => true,
                'purge' => true,
            ]
        ])
            ->withUrlParameters([
                'node' => $this->node->cluster,
                'server' => $this->server->vmid
            ])
            ->delete('/api2/json/nodes/{node}/qemu/{server}')
            ->json();

        return $this->getData($response);
    }

    public function addUser(RealmType $realmType, string $userId, string $roleId)
    {
        Assert::isInstanceOf($this->server, Server::class);

        $response = $this->getHttpClient()
            ->put('/api2/json/access/acl', [
                'path' => '/vms/' . $this->server->vmid,
                'users' => $userId . '@' . $realmType->value,
                'roles' => $roleId,
            ])
            ->json();

        return $this->getData($response);
    }
}
