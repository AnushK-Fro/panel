import { lazy } from 'react'

interface RouteDefinition {
  path: string
  name: string
  component: React.ComponentType
}

interface Routes {
  // All of the routes available under "/servers/:id"
  server: RouteDefinition[]
  admin: {
    dashboard: RouteDefinition[]
    node: RouteDefinition[]
  }
}

/*
|--------------------------------------------------------------------------
| Server Routes
|--------------------------------------------------------------------------
|
| Route: /servers/<id>
|
*/

const routes: Routes = {
  server: [
    {
      path: '/',
      name: 'Overview',
      component: lazy(() => import('@/components/servers/overview/ServerOverviewContainer')),
    },
    {
      path: '/backups',
      name: 'Backups',
      component: lazy(() => import('@/components/servers/backups/BackupsContainer')),
    },
    {
      path: '/settings',
      name: 'Settings',
      component: lazy(() => import('@/components/servers/settings/ServerSettingsContainer')),
    },
    {
      path: '/terminal',
      name: 'Terminal',
      component: lazy(() => import('@/components/servers/terminal/ServerTerminalContainer')),
    },
  ],
  admin: {
    dashboard: [
      {
        path: '/',
        name: 'Overview',
        component: lazy(() => import('@/components/admin/overview/OverviewContainer')),
      },
      {
        path: '/locations',
        name: 'Locations',
        component: lazy(() => import('@/components/admin/locations/LocationsContainer')),
      },
      {
        path: '/nodes',
        name: 'Nodes',
        component: lazy(() => import('@/components/admin/nodes/NodesContainer')),
      },
      {
        path: '/servers',
        name: 'Servers',
        component: lazy(() => import('@/components/admin/servers/ServersContainer')),
      }
    ],
    node: [
      {
        path: '/',
        name: 'Overview',
        component: lazy(() => import('@/components/admin/nodes/overview/NodeOverviewContainer')),
      },
      {
        path: '/servers',
        name: 'Servers',
        component: lazy(() => import('@/components/admin/nodes/servers/NodeServersContainer')),
      },
      {
        path: '/isos',
        name: 'ISOs',
        component: lazy(() => import('@/components/admin/nodes/isoLibrary/IsoLibraryContainer')),
      },
      {
        path: '/templates',
        name: 'Templates',
        component: lazy(() => import('@/components/admin/nodes/templates/NodeTemplatesContainer')),
      },
      {
        path: '/addresses',
        name: 'Addresses',
        component: lazy(() => import('@/components/admin/nodes/addresses/NodeAddressesContainer')),
      },
    ]
  }
}

export default routes
