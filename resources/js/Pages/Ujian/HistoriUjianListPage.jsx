import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Typography, Card } from "@material-tailwind/react";
import { usePage, Head, Link } from '@inertiajs/react';

// Impor komponen card yang sudah Anda buat
import HistoriUjianRingkasanCard from '@/Components/DashboardPanels/HistoriUjianRingkasanCard'; // Pastikan path ini benar

export default function HistoriUjianListPage() {
  // Ambil props 'semuaHistoriUjian' yang dikirim dari ListUjianController
  const { auth, semuaHistoriUjian } = usePage().props;

  return (
    <AuthenticatedLayout
      user={auth.user}
      title="Riwayat Ujian Anda"
    >
      <Head title="Riwayat Ujian" />

      {/* Header Halaman */}
      <div className="mb-8">
        <Typography variant="h4" color="blue-gray" className="font-bold">
          Riwayat Ujian Anda
        </Typography>
        <Typography color="gray" className="mt-1 font-normal">
          Lihat kembali semua ujian yang pernah Anda kerjakan.
        </Typography>
      </div>

      {/* Konten Halaman: Menampilkan daftar riwayat atau pesan jika kosong */}
      {semuaHistoriUjian && semuaHistoriUjian.length > 0 ? (
        // Tampilkan grid kartu jika ada data riwayat
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          {semuaHistoriUjian.map((histori) => (
            <HistoriUjianRingkasanCard 
              key={histori.id_pengerjaan} 
              histori={histori} 
            />
          ))}
        </div>
      ) : (
        // Tampilkan pesan jika tidak ada riwayat
        <Card className="p-8 mt-6 text-center shadow-lg border border-blue-gray-50">
          <Typography variant="h6" color="blue-gray">
            Belum Ada Riwayat
          </Typography>
          <Typography color="blue-gray" className="mt-2 opacity-80">
            Anda belum memiliki riwayat pengerjaan ujian. Silakan kerjakan ujian terlebih dahulu.
          </Typography>
        </Card>
      )}
    </AuthenticatedLayout>
  );
}