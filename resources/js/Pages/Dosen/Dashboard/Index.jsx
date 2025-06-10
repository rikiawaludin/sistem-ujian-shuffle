import React, { useMemo } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, usePage } from '@inertiajs/react';
import { Button, Card, Typography } from '@material-tailwind/react';
import AdvancedTable from '@/Components/Tables/AdvancedTable';

export default function Index() {
    const { auth, pengerjaanList } = usePage().props;

    const columns = useMemo(() => [
        { header: 'Email Mahasiswa', accessorKey: 'user.email' },
        { header: 'Judul Ujian', accessorKey: 'ujian.judul_ujian' },
        { 
            header: 'Mata Kuliah', 
            // TanStack Table bisa mengakses data nested dengan dot notation
            accessorKey: 'ujian.mata_kuliah.nama',
            // Fallback jika mata kuliah tidak ada
            cell: info => info.getValue() || 'N/A'
        },
        { header: 'Skor', accessorKey: 'skor_total' },
        { 
            header: 'Waktu Selesai', 
            accessorKey: 'waktu_selesai',
            cell: info => new Date(info.getValue()).toLocaleString('id-ID')
        }
    ], []);

    return (
        <AuthenticatedLayout user={auth.user} title="Dashboard Dosen">
            <Head title="Dashboard Dosen" />

            <div className="mb-6">
                <Typography variant="h4" color="blue-gray">Dashboard Pelaporan</Typography>
                <Typography color="gray" className="mt-1 font-normal">
                    Berikut adalah daftar semua pengerjaan ujian yang telah diselesaikan oleh mahasiswa.
                </Typography>
            </div>

            <Card className="p-4 shadow-lg border border-blue-gray-50">
                <AdvancedTable
                    columns={columns}
                    data={pengerjaanList.data}
                />
            </Card>
        </AuthenticatedLayout>
    );
}