<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

import { type BreadcrumbItem } from '@/types';
import type { ColumnFiltersState, SortingState, VisibilityState } from '@tanstack/vue-table';
import { FlexRender, getCoreRowModel, getFilteredRowModel, getPaginationRowModel, getSortedRowModel, useVueTable } from '@tanstack/vue-table';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import DataTablePagination from '@/Custom/components/DataTablePagination.vue';
import DataTableViewOptions from '@/Custom/components/DataTableViewOptions.vue';
import { valueUpdater } from '@/lib/utils';
import { Cross2Icon, PlusCircledIcon } from '@radix-icons/vue';
import { computed, ref, shallowRef } from 'vue';
import type { Role } from './data/schema';
import Form from './partials/Form.vue';

import { columns } from './data/columns';

const props = defineProps({
    roles: {
        type: Array,
    },
    filters: {
        type: Object,
    },
    perPage: {
        type: Number,
    },
});

const data: shallowRef<Role[]> = props.roles;

const sorting = ref<SortingState>([]);
const columnFilters = ref<ColumnFiltersState>([]);
const columnVisibility = ref<VisibilityState>({});
const rowSelection = ref({});

const table = useVueTable({
    data,
    columns,
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    onSortingChange: (updaterOrValue) => valueUpdater(updaterOrValue, sorting),
    onColumnFiltersChange: (updaterOrValue) => valueUpdater(updaterOrValue, columnFilters),
    onColumnVisibilityChange: (updaterOrValue) => valueUpdater(updaterOrValue, columnVisibility),
    onRowSelectionChange: (updaterOrValue) => valueUpdater(updaterOrValue, rowSelection),
    state: {
        get sorting() {
            return sorting.value;
        },
        get columnFilters() {
            return columnFilters.value;
        },
        get columnVisibility() {
            return columnVisibility.value;
        },
        get rowSelection() {
            return rowSelection.value;
        },
    },
});

const isFiltered = computed(() => table.getState().columnFilters.length > 0);

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Roles',
        href: '/iam/roles',
    },
];

const addDialog = ref(false);
</script>

<template>
    <Head title="Roles" />

    <AppLayout :breadcrumbs="breadcrumbItems">
        <Form v-model="addDialog" />

        <div class="w-full space-y-4 p-4">
            <div class="flex items-center gap-2">
                <Input
                    placeholder="Filter name..."
                    :model-value="(table.getColumn('name')?.getFilterValue() as string) ?? ''"
                    class="h-8 w-[150px] lg:w-[250px]"
                    @input="table.getColumn('name')?.setFilterValue($event.target.value)"
                />
                <Button class="h-8 px-2 lg:px-3" @click="addDialog = true">
                    <PlusCircledIcon class="h-4 w-4" />
                    Add
                </Button>

                <Button v-if="isFiltered" variant="ghost" class="h-8 px-2 lg:px-3" @click="table.resetColumnFilters()">
                    Reset
                    <Cross2Icon class="ml-2 h-4 w-4" />
                </Button>
                <DataTableViewOptions :table="table" />
            </div>
            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                            <TableHead v-for="header in headerGroup.headers" :key="header.id">
                                <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
                            </TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <template v-if="table.getRowModel().rows?.length">
                            <TableRow v-for="row in table.getRowModel().rows" :key="row.id" :data-state="row.getIsSelected() && 'selected'">
                                <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                                    <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                                </TableCell>
                            </TableRow>
                        </template>

                        <TableRow v-else>
                            <TableCell col-span="{columns.length}" class="h-24 text-center"> No results. </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <DataTablePagination :table="table" />
        </div>
    </AppLayout>
</template>
