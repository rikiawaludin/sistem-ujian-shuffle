import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'; // Asumsi Anda menggunakan layout ini
import { Typography, IconButton, Card } from "@material-tailwind/react";
import { ArrowLeftIcon } from "@heroicons/react/24/solid";
import { usePage, Head } from '@inertiajs/react';
import KartuUjian from './DaftarUjianComponents/KartuUjian'; // <-- Impor komponen KartuUjian

export default function DaftarUjianPage() {
  const { mataKuliah, daftarUjian } = usePage().props;
  
  // Tombol kembali bisa menggunakan Link Inertia atau window.history.back()
  // Menggunakan window.history.back() lebih sederhana jika tidak ada logika khusus
  const handleKembali = () => {
    window.history.back();
  };

  // Pastikan daftarUjian adalah array, default ke array kosong jika tidak
  const ujianList = Array.isArray(daftarUjian) ? daftarUjian : [];

  return (
    // Asumsi AuthenticatedLayout menerima prop 'user' dan 'header'
    // Sesuaikan dengan implementasi AuthenticatedLayout Anda
    <AuthenticatedLayout 
      // user={auth.user} // Jika AuthenticatedLayout butuh user prop
      // header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">{`Ujian: ${mataKuliah ? mataKuliah.nama : "Tidak Ditemukan"}`}</h2>}
      title={mataKuliah ? `Ujian: ${mataKuliah.nama}` : 'Daftar Ujian'} // Prop title untuk layout jika ada
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
          <Typography variant="h4" color="blue-gray" className="flex-wrap font-semibold">
            Ujian untuk: <span className="font-bold">{mataKuliah ? mataKuliah.nama : "Mata Kuliah Tidak Ditemukan"}</span>
          </Typography>
        </div>
        {/* Bisa tambahkan tombol aksi lain di sini jika perlu */}
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