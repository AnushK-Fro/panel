<?php

namespace Convoy\Repositories\Proxmox\Server;

use Convoy\Enums\Server\PowerAction;
use Convoy\Exceptions\Repository\Proxmox\ProxmoxConnectionException;
use Convoy\Models\Server;
use Convoy\Repositories\Proxmox\ProxmoxRepository;
use GuzzleHttp\Exception\GuzzleException;
use Webmozart\Assert\Assert;

class ProxmoxPowerRepository extends ProxmoxRepository
{
    public function send(PowerAction $action)
    {
        Assert::isInstanceOf($this->server, Server::class);

        // I added this because I don't like the naming scheme Proxmox has
        switch ($action) {
            case PowerAction::RESTART:
                $parsedAction = 'reboot';
                break;
            case PowerAction::RESET:
                $parsedAction = 'reset';
                break;
            case PowerAction::RESUME:
                $parsedAction = 'resume';
                break;
            case PowerAction::SHUTDOWN:
                $parsedAction = 'shutdown';
                break;
            case PowerAction::START:
                $parsedAction = 'start';
                break;
            case PowerAction::KILL:
                $parsedAction = 'stop';
                break;
            case PowerAction::SUSPEND:
                $parsedAction = 'suspend';
                break;
        }

        $response = $this->getHttpClient()
            ->withUrlParameters([
                'node' => $this->node->cluster,
                'server' => $this->server->vmid,
                'action' => $parsedAction,
            ])
            ->post('/api2/json/nodes/{node}/qemu/{server}/status/{action}', [
                ...($parsedAction !== 'suspend' ? ['timeout' => 30] : ['skiplock' => false])
            ])
            ->json();

        return $this->getData($response);
    }
}
