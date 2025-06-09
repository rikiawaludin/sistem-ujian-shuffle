import React, { useMemo } from 'react';
import { Head, Link, usePage, router } from '@inertiajs/react';
import { Button, Typography, IconButton, Tooltip, Card } from '@material-tailwind/react';
import { PencilIcon, TrashIcon } from '@heroicons/react/24/solid';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import AdvancedTable from '@/Components/Tables/AdvancedTable';

export default function Index() {
    const { auth, soalList } = usePage().props;

    const handleDelete = (soal) => {
        if (confirm(`Apakah Anda yakin ingin menghapus soal "${soal.pertanyaan}"?`)) {
            router.delete(route('dosen.bank-soal.destroy', soal.id), { preserveScroll: true });
        }
    };

    const columns = useMemo(() => [
        { header: 'Pertanyaan', accessorKey: 'pertanyaan', cell: ({ row }) => (<p className="font-normal line-clamp-2">{row.original.pertanyaan}</p>) },
        { header: 'Tipe', accessorKey: 'tipe_soal', cell: info => <span className="capitalize">{info.getValue().replace('_', ' ')}</span> },
        { header: 'Kategori', accessorKey: 'kategori_soal' },
        { header: 'Aksi', id: 'aksi', cell: ({ row }) => (
            <div className="flex items-center gap-2">
                <Tooltip content="Edit Soal"><Link href={route('dosen.bank-soal.edit', row.original.id)}><IconButton variant="text" size="sm"><PencilIcon className="h-4 w-4 text-blue-gray-600" /></IconButton></Link></Tooltip>
                <Tooltip content="Hapus Soal"><IconButton variant="text" size="sm" onClick={() => handleDelete(row.original)}><TrashIcon className="h-4 w-4 text-red-600" /></IconButton></Tooltip>
            </div>
        )}
    ], []);

    return (
        <AuthenticatedLayout title="Bank Soal">
            <div className="flex justify-between items-center mb-8">
                <div>
                    <Typography variant="h4" color="blue-gray">Bank Soal</Typography>
                    <Typography color="gray" className="mt-1 font-normal">
                        Dikelola oleh: {auth.user.name}
                    </Typography>
                </div>
                <Link href={route('dosen.bank-soal.create')}>
                    <Button color="blue">Tambah Soal Baru</Button>
                </Link>
            </div>
            <Card className="w-full shadow-lg p-6">
                <AdvancedTable
                    columns={columns}
                    data={soalList || []} // Langsung gunakan soalList karena sudah berupa array
                    isLoading={false}
                />
            </Card>
        </AuthenticatedLayout>
    );
}