// resources/js/Pages/Ujian/DaftarUjianPage.jsx

import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Typography, IconButton, Card } from "@material-tailwind/react";
import { ArrowLeftIcon, AcademicCapIcon } from "@heroicons/react/24/solid";
import { usePage, Head, router } from '@inertiajs/react';
import KartuUjian from './DaftarUjianComponents/KartuUjian';

// DITAMBAHKAN: Impor komponen header baru
import PageHeader from '@/Layouts/PageHeader';

export default function DaftarUjianPage() {
  const { auth, mataKuliah, daftarUjian } = usePage().props;

  const handleKembali = () => {
    router.get(route('home'));
  };

  const ujianList = Array.isArray(daftarUjian) ? daftarUjian : [];

  return (
    <AuthenticatedLayout
      user={auth.user}
      title={mataKuliah ? `Ujian: ${mataKuliah.nama}` : 'Daftar Ujian'}
    >
      <Head title={mataKuliah ? `Ujian ${mataKuliah.nama}` : 'Daftar Ujian'} />

      {/* DIUBAH: Header lama diganti dengan komponen PageHeader */}
      <PageHeader
        title={mataKuliah ? mataKuliah.nama : "Mata Kuliah Tidak Ditemukan"}
        subtitle="Daftar Ujian Tersedia"
        icon={<AcademicCapIcon />}
      >
        {/* Tombol 'Kembali' sekarang menjadi children dari PageHeader */}
        <IconButton
          variant="text"
          color="white"
          onClick={handleKembali}
          aria-label="Kembali"
        >
          <ArrowLeftIcon strokeWidth={2.5} className="h-5 w-5" />
        </IconButton>
      </PageHeader>

      {/* Daftar Ujian (Tidak ada perubahan) */}
      {ujianList.length > 0 ? (
        <div className="space-y-5">
          {ujianList.map((ujian) => (
            <KartuUjian key={ujian.id} ujian={ujian} />
          ))}
        </div>
      ) : (
        <Card className="p-8 text-center shadow-lg border border-blue-gray-50">
          <Typography color="blue-gray" className="opacity-80">
            Belum ada ujian yang tersedia untuk mata kuliah ini.
          </Typography>
        </Card>
      )}
    </AuthenticatedLayout>
  );
}