import React, { useMemo } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, usePage, router } from '@inertiajs/react';
import { Button, Card, Typography } from '@material-tailwind/react';
import AdvancedTable from '@/Components/Tables/AdvancedTable';

export default function Index() {
    const { ujianList } = usePage().props;

    const columns = useMemo(() => [
        { header: 'Judul Ujian', accessorKey: 'judul_ujian' },
        { header: 'Mata Kuliah', cell: ({row}) => row.original.mata_kuliah?.nama || 'N/A' },
        { header: 'Durasi', cell: ({row}) => `${row.original.durasi} menit` },
        { header: 'Jumlah Soal', cell: ({row}) => `${row.original.soal_count || 0} Soal` }, // Perlu 'withCount' di controller
        { header: 'Aksi', id: 'aksi', cell: ({ row }) => (
            <Link href={route('dosen.ujian.edit', row.original.id)}>
                <Button size="sm" variant="outlined">Atur Soal</Button>
            </Link>
        )}
    ], []);

    return (
        <AuthenticatedLayout title="Manajemen Ujian">
            <div className="flex justify-between items-center mb-6">
                <Typography variant="h4" color="blue-gray">Manajemen Ujian</Typography>
                <Link href={route('dosen.ujian.create')}>
                    <Button color="blue">Buat Ujian Baru</Button>
                </Link>
            </div>
            <Card className="p-4">
                <AdvancedTable columns={columns} data={ujianList.data} />
            </Card>
        </AuthenticatedLayout>
    );
}