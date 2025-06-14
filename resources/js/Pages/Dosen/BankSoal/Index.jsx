// File: Index.jsx

import React, { useMemo, useState } from 'react';
import { Head, Link, usePage, router } from '@inertiajs/react';
// Perbaikan: Tambahkan Select dan Option di sini
import { Button, Typography, IconButton, Tooltip, Card } from '@material-tailwind/react';
import { PencilIcon, TrashIcon } from '@heroicons/react/24/solid';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import AdvancedTable from '@/Components/Tables/AdvancedTable';
import Select from 'react-select';


export default function Index() {
    const { auth, soalList, mataKuliahOptions, filters } = usePage().props;

    const [selectedMk, setSelectedMk] = useState(filters.filter_mk || '');

    const handleDelete = (soal) => {
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = soal.pertanyaan;
        const pertanyaanTeks = tempDiv.textContent || tempDiv.innerText || "soal ini";

        if (confirm(`Apakah Anda yakin ingin menghapus "${pertanyaanTeks}"?`)) {
            router.delete(route('dosen.bank-soal.destroy', soal.id), { preserveScroll: true });
        }
    };

    const columns = useMemo(() => [
        {
            header: 'Pertanyaan', accessorKey: 'pertanyaan', cell: ({ row }) => (
                <div
                    className="font-normal text-sm text-blue-gray-800 line-clamp-3"
                    dangerouslySetInnerHTML={{ __html: row.original.pertanyaan }}
                />
            )
        },
        { header: 'Tipe', accessorKey: 'tipe_soal', cell: info => <span className="capitalize">{info.getValue().replace(/_/g, ' ')}</span> },
        {
            header: 'Mata Kuliah',
            accessorKey: 'mata_kuliah.nama',
            cell: ({ row }) => (
                <span>{row.original.mata_kuliah ? row.original.mata_kuliah.nama : 'Tanpa Mata Kuliah'}</span>
            )
        },
        {
            header: 'Aksi', id: 'aksi', cell: ({ row }) => (
                <div className="flex items-center gap-2">
                    <Tooltip content="Edit Soal"><Link href={route('dosen.bank-soal.edit', row.original.id)}><IconButton variant="text" size="sm"><PencilIcon className="h-4 w-4 text-blue-gray-600" /></IconButton></Link></Tooltip>
                    <Tooltip content="Hapus Soal"><IconButton variant="text" size="sm" onClick={() => handleDelete(row.original)}><TrashIcon className="h-4 w-4 text-red-600" /></IconButton></Tooltip>
                </div>
            )
        }
    ], []);

    const mkOptions = [
        { value: '', label: 'Semua Mata Kuliah' },
        ...(mataKuliahOptions || []),
    ];

    return (
        // Pastikan Anda meneruskan prop 'user' ke AuthenticatedLayout jika diperlukan
        <AuthenticatedLayout user={auth.user} title="Bank Soal">
            <Head title="Bank Soal" />

            {/* Perbaikan: Tata letak header yang lebih rapi */}
            <div className="flex flex-wrap justify-between items-center mb-8 gap-4">
                <div>
                    <Typography variant="h4" color="blue-gray">Bank Soal</Typography>
                    <Typography color="gray" className="mt-1 font-normal">
                        Dikelola oleh: {auth.user.name}
                    </Typography>
                </div>

                <div className="flex flex-wrap items-center gap-4">
                    <div className="w-full sm:w-72">
                        <Select
                            options={mkOptions}
                            value={mkOptions.find(opt => opt.value === selectedMk)}
                            onChange={(selectedOption) => {
                                const value = selectedOption?.value || '';
                                setSelectedMk(value);
                                router.get(route('dosen.bank-soal.index'), { filter_mk: value }, {
                                    preserveState: true,
                                    replace: true,
                                });
                            }}
                        />

                    </div>
                    <Link href={route('dosen.bank-soal.create')}>
                        <Button color="blue">Tambah Soal Baru</Button>
                    </Link>
                </div>
            </div>

            <Card className="w-full shadow-lg p-6">
                <AdvancedTable
                    columns={columns}
                    data={soalList || []}
                    isLoading={!soalList}
                />
            </Card>
        </AuthenticatedLayout>
    );
}