import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Typography, IconButton, Card } from "@material-tailwind/react";
import { ArrowLeftIcon, AcademicCapIcon } from "@heroicons/react/24/solid";
import { usePage, Head, router  } from '@inertiajs/react';
import KartuUjian from './DaftarUjianComponents/KartuUjian';

export default function DaftarUjianPage() {
  // 1. Terima prop 'auth' dari controller
  const { auth, mataKuliah, daftarUjian } = usePage().props;
  
  const handleKembali = () => {
    router.get(route('home'));
  };

  const ujianList = Array.isArray(daftarUjian) ? daftarUjian : [];

  return (
    // 2. Berikan prop 'user' ke AuthenticatedLayout
    <AuthenticatedLayout 
      user={auth.user} 
      title={mataKuliah ? `Ujian: ${mataKuliah.nama}` : 'Daftar Ujian'}
    >
      <Head title={mataKuliah ? `Ujian ${mataKuliah.nama}` : 'Daftar Ujian'} />
      
      {/* Bagian Header Halaman */}
      <div className="flex items-center justify-between mb-6">
        <div className="flex items-center">
          <IconButton 
            variant="text" 
            color="blue-gray" 
            onClick={handleKembali} 
            className="mr-2"
            aria-label="Kembali"
          >
            <ArrowLeftIcon strokeWidth={2.5} className="h-5 w-5" />
          </IconButton>
          <div className="flex items-center gap-3">
            <AcademicCapIcon className="h-8 w-8 text-blue-gray-700" />
            <Typography variant="h4" color="blue-gray" className="flex-wrap font-semibold">
              <span className="font-bold">{mataKuliah ? mataKuliah.nama : "Mata Kuliah Tidak Ditemukan"}</span>
            </Typography>
          </div>
        </div>
      </div>

      {/* Daftar Ujian */}
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