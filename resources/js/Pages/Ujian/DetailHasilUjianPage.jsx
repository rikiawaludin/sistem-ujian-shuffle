import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Typography, Card, Button, IconButton } from "@material-tailwind/react";
import { ArrowLeftIcon } from "@heroicons/react/24/solid";
import { Link, usePage, Head, router } from '@inertiajs/react';

// Impor komponen baru
import SoalReviewItem from './DetailHasilComponents/SoalReviewItem';
import RingkasanHasilUjian from './DetailHasilComponents/RingkasanHasilUjian';

export default function DetailHasilUjianPage() {
  const { hasilUjian, auth } = usePage().props; // Ambil auth jika diperlukan oleh AuthenticatedLayout

  const handleKembali = () => {
    if (route().has('ujian.riwayat')) {
      router.get(route('ujian.riwayat'));
    } else if (route().has('dashboard')) {
      router.get(route('dashboard'));
    } else {
      window.history.back(); // Fallback jika rute tidak ada
    }
  };

  if (!hasilUjian || !hasilUjian.detailSoalJawaban) {
    return (
      <AuthenticatedLayout 
        user={auth.user} // Asumsi AuthenticatedLayout memerlukan user
        title="Error Hasil Ujian"
      >
        <Head title="Error Hasil Ujian" />
        <div className="flex flex-col items-center justify-center min-h-[calc(100vh-200px)] p-4">
          <Card className="p-8 text-center shadow-md">
            <Typography variant="h4" color="red" className="mb-4">Error Memuat Detail Ujian</Typography>
            <Typography color="blue-gray" className="mb-6">Maaf, detail hasil ujian tidak dapat ditemukan atau tidak lengkap.</Typography>
            <Button onClick={() => router.get(route('dashboard'))} color="blue">
              Kembali ke Dashboard
            </Button>
          </Card>
        </div>
      </AuthenticatedLayout>
    );
  }

  return (
    <AuthenticatedLayout 
        user={auth.user} // Asumsi AuthenticatedLayout memerlukan user
        title={`Hasil Ujian: ${hasilUjian.judulUjian}`}
    >
      <Head title={`Hasil Ujian: ${hasilUjian.judulUjian}`} />

      {/* Header Halaman */}
      <div className="flex items-center justify-between mb-6">
        <div className="flex items-center">
          <IconButton variant="text" color="blue-gray" onClick={handleKembali} className="mr-3" aria-label="Kembali">
            <ArrowLeftIcon strokeWidth={2.5} className="h-5 w-5" />
          </IconButton>
          <Typography variant="h4" color="blue-gray" className="font-bold">
            Detail Hasil Ujian
          </Typography>
        </div>
      </div>

      {/* Komponen Ringkasan Hasil Ujian */}
      <RingkasanHasilUjian hasilUjian={hasilUjian} />

      {/* Pembahasan Jawaban */}
      <div>
        <Typography variant="h5" color="blue-gray" className="mb-5 font-semibold">
          Pembahasan Jawaban
        </Typography>
        {hasilUjian.detailSoalJawaban && hasilUjian.detailSoalJawaban.length > 0 ? (
          <div className="space-y-6">
            {hasilUjian.detailSoalJawaban.map((soal, index) => (
              <SoalReviewItem key={soal.idSoal || `soal-review-${index}`} soal={soal} nomorUrut={index + 1} />
            ))}
          </div>
        ) : (
          <Card className="p-6 text-center shadow">
            <Typography color="gray">Detail pembahasan jawaban tidak tersedia.</Typography>
          </Card>
        )}
      </div>

      {/* Tombol Kembali di Bagian Bawah */}
      <div className="mt-10 flex justify-center">
        <Button color="blue-gray" variant='outlined' onClick={handleKembali} className="flex items-center gap-2">
          <ArrowLeftIcon className="h-5 w-5"/>
          Kembali
        </Button>
      </div>
    </AuthenticatedLayout>
  );
}