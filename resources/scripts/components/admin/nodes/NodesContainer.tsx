import deleteNode from '@/api/admin/nodes/deleteNode'
import { Node, NodeResponse } from '@/api/admin/nodes/getNodes'
import useNodesSWR from '@/api/admin/nodes/useNodesSWR'
import CreateNodeModal from '@/components/admin/nodes/CreateNodeModal'
import SearchBar from '@/components/admin/SearchBar'
import Button from '@/components/elements/Button'
import Table, { Actions, Column, ColumnArray, RowActionsProps } from '@/components/elements/displays/Table'
import TextInput from '@/components/elements/inputs/TextInput'
import Menu from '@/components/elements/Menu'
import PageContentBlock from '@/components/elements/PageContentBlock'
import Pagination from '@/components/elements/Pagination'
import Spinner from '@/components/elements/Spinner'
import { bytesToString } from '@/util/helpers'
import useFlash from '@/util/useFlash'
import usePagination from '@/util/usePagination'
import { MagnifyingGlassIcon, PlusIcon } from '@heroicons/react/20/solid'
import { useDebouncedValue } from '@mantine/hooks'
import { useState } from 'react'
import { Link } from 'react-router-dom'

const columns: ColumnArray<Node> = [
    {
        accessor: 'name',
        header: 'Name',
        cell: ({ value, row }) => (
            <Link to={`/admin/nodes/${row.id}`} className='link text-foreground'>
                {value}
            </Link>
        ),
    },
    {
        accessor: 'fqdn',
        header: 'FQDN',
    },
    {
        accessor: 'memory',
        header: 'Memory',
        cell: ({ value }) => bytesToString(value),
    },
    {
        accessor: 'disk',
        header: 'Disk',
        cell: ({ value }) => bytesToString(value),
    },
    {
        accessor: 'serversCount',
        header: 'Servers',
        align: 'center',
    },
]

const NodesContainer = () => {
    const [page, setPage] = usePagination()
    const [open, setOpen] = useState(false)
    const { clearFlashes, clearAndAddHttpError } = useFlash()

    const [query, setQuery] = useState('')
    const [debouncedQuery] = useDebouncedValue(query, 200)
    const { data, mutate } = useNodesSWR({ page, query: debouncedQuery })

    const rowActions = ({ row: node }: RowActionsProps<Node>) => {
        const handleDelete = () => {
            clearFlashes('admin:nodes')

            deleteNode(node.id)
                .then(() => {
                    mutate(
                        mutateData =>
                            ({
                                ...mutateData,
                                items: mutateData!.items.filter(n => n.id !== node.id),
                            } as NodeResponse),
                        false
                    )
                })
                .catch(error => {
                    clearAndAddHttpError({ key: 'admin:nodes', error })
                })
        }

        return (
            <Actions>
                <Menu.Item color='red' disabled={node.serversCount > 0} onClick={handleDelete}>
                    Delete
                </Menu.Item>
            </Actions>
        )
    }

    return (
        <div className='bg-background min-h-screen'>
            <PageContentBlock title='Nodes' showFlashKey='admin:nodes'>
                <CreateNodeModal open={open} onClose={() => setOpen(false)} />
                <SearchBar value={query} onChange={e => setQuery(e.target.value)} buttonText='New Node' onClick={() => setOpen(true)} />
                {!data ? (
                    <Spinner />
                ) : (
                    <Pagination data={data} onPageSelect={setPage}>
                        {({ items }) => <Table rowActions={rowActions} columns={columns} data={items} />}
                    </Pagination>
                )}
            </PageContentBlock>
        </div>
    )
}

export default NodesContainer
