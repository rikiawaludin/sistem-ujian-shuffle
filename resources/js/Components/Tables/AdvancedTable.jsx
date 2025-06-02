import React, { useState, useEffect, useMemo } from "react";
import {
    useReactTable,
    getCoreRowModel,
    getFilteredRowModel,
    getPaginationRowModel,
    getSortedRowModel,
    flexRender,
} from "@tanstack/react-table";
import {
    Button,
    IconButton,
    Input,
    Typography,
    Select,
    Option, // Import Option dari Material Tailwind
    Spinner,
} from "@material-tailwind/react";
import {
    ChevronUpDownIcon, // Pengganti NavArrowUp/Down untuk sorting
    ChevronLeftIcon,
    ChevronRightIcon,
} from "@heroicons/react/24/outline"; // Menggunakan Heroicons untuk ikon pagination

// Komponen untuk Debounced Input (Global Filter)
function DebouncedInput({ value: initialValue, onChange, debounce = 500, ...props }) {
    const [value, setValue] = useState(initialValue);

    useEffect(() => {
        setValue(initialValue);
    }, [initialValue]);

    useEffect(() => {
        const timeout = setTimeout(() => {
            onChange(value);
        }, debounce);
        return () => clearTimeout(timeout);
    }, [value, debounce, onChange]);

    return (
        <div className="w-full md:w-72">
            <Input
                {...props}
                value={value || ""}
                onChange={(e) => setValue(e.target.value)}
                label="Cari semua kolom..." // Menggunakan label Material Tailwind
            />
        </div>
    );
}


export default function AdvancedTable({ columns: propColumns, data: propData, isLoading }) {
    const [globalFilter, setGlobalFilter] = useState("");
    const [sorting, setSorting] = useState([]);

    // Memoize kolom agar tidak dibuat ulang setiap render kecuali propColumns berubah
    const columns = useMemo(() => propColumns, [propColumns]);
    // Memoize data agar tidak dibuat ulang setiap render kecuali propData berubah
    const data = useMemo(() => propData || [], [propData]);


    const table = useReactTable({
        data,
        columns,
        state: {
            globalFilter,
            sorting,
        },
        onGlobalFilterChange: setGlobalFilter,
        onSortingChange: setSorting,
        getCoreRowModel: getCoreRowModel(),
        getFilteredRowModel: getFilteredRowModel(),
        getPaginationRowModel: getPaginationRowModel(),
        getSortedRowModel: getSortedRowModel(),
        // debugTable: true, // Uncomment untuk debug
    });

    if (isLoading) {
        return (
            <div className="flex justify-center items-center h-32">
                <Spinner className="h-12 w-12" />
            </div>
        );
    }

    // if (data.length === 0 && !isLoading) { // Ini akan ditangani oleh table.getRowModel().rows.length di bawah
    //     return (
    //         <Typography variant="small" className="text-center p-4">
    //             Tidak ada data untuk ditampilkan.
    //         </Typography>
    //     );
    // }


    return (
        <div className="w-full">
            <div className="mb-4 flex flex-col md:flex-row justify-between items-center gap-4">
                <DebouncedInput
                    value={globalFilter ?? ""}
                    onChange={(value) => setGlobalFilter(String(value))}
                />
                {/* Opsi untuk memilih jumlah item per halaman */}
                <div className="w-full md:w-auto">
                    <Select
                        label="Baris per halaman"
                        value={String(table.getState().pagination.pageSize)} // Pastikan value adalah string
                        onChange={(value) => {
                            table.setPageSize(Number(value));
                        }}
                    >
                        {[10, 20, 30, 40, 50].map((pageSize) => (
                            <Option key={pageSize} value={String(pageSize)}> {/* Pastikan value Option adalah string */}
                                {pageSize}
                            </Option>
                        ))}
                    </Select>
                </div>
            </div>

            <div className="overflow-x-auto rounded-lg border border-blue-gray-100">
                <table className="w-full min-w-max table-auto text-left">
                    <thead className="bg-blue-gray-50">
                        {table.getHeaderGroups().map((headerGroup) => (
                            <tr key={headerGroup.id}>
                                {headerGroup.headers.map((header) => (
                                    <th
                                        key={header.id}
                                        colSpan={header.colSpan}
                                        className="cursor-pointer border-y border-blue-gray-100 bg-blue-gray-50/50 p-4 transition-colors hover:bg-blue-gray-50"
                                        onClick={header.column.getToggleSortingHandler()}
                                    >
                                        <Typography
                                            variant="small"
                                            color="blue-gray"
                                            className="flex items-center justify-between gap-2 font-normal leading-none opacity-70"
                                        >
                                            {header.isPlaceholder
                                                ? null
                                                : flexRender(
                                                    header.column.columnDef.header,
                                                    header.getContext()
                                                )}
                                            {header.column.getCanSort() && (
                                                <ChevronUpDownIcon strokeWidth={2} className="h-4 w-4" />
                                            )}
                                        </Typography>
                                    </th>
                                ))}
                            </tr>
                        ))}
                    </thead>
                    <tbody>
                        {table.getRowModel().rows.length > 0 ? (
                            table.getRowModel().rows.map((row) => (
                                <tr key={row.id} className="even:bg-blue-gray-50/50 hover:bg-blue-gray-100/50">
                                    {row.getVisibleCells().map((cell) => (
                                        <td key={cell.id} className="p-4 border-b border-blue-gray-50">
                                            <Typography variant="small" color="blue-gray" className="font-normal">
                                                {flexRender(cell.column.columnDef.cell, cell.getContext())}
                                            </Typography>
                                        </td>
                                    ))}
                                </tr>
                            ))
                        ) : (
                            <tr>
                                <td colSpan={columns.length} className="p-4 text-center">
                                    <Typography variant="small" color="blue-gray">
                                        Tidak ada data untuk ditampilkan.
                                    </Typography>
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            {/* Pagination Controls */}
            {table.getRowModel().rows.length > 0 && (
                <div className="mt-4 flex flex-col md:flex-row items-center justify-between gap-4">
                    <Typography variant="small" color="blue-gray" className="font-normal">
                        Halaman {table.getState().pagination.pageIndex + 1} dari {table.getPageCount()}
                    </Typography>
                    <div className="flex items-center gap-2">
                        <Button
                            variant="outlined"
                            size="sm"
                            onClick={() => table.previousPage()}
                            disabled={!table.getCanPreviousPage()}
                            className="flex items-center gap-1" // Tambahkan kelas flex dan gap
                        >
                            <ChevronLeftIcon strokeWidth={2} className="h-4 w-4" />
                            Previous
                        </Button>
                        <Button
                            variant="outlined"
                            size="sm"
                            onClick={() => table.nextPage()}
                            disabled={!table.getCanNextPage()}
                            className="flex items-center gap-1" // Tambahkan kelas flex dan gap
                        >
                            Next
                            <ChevronRightIcon strokeWidth={2} className="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            )}
        </div>
    );
}