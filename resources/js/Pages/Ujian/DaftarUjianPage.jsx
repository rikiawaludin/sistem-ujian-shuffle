import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'; // Pastikan path @/Layouts/ benar
import { Typography, Button, IconButton, Card, Chip } from "@material-tailwind/react";
import { ArrowLeftIcon, ClockIcon, ClipboardDocumentListIcon, CalendarDaysIcon, PlayCircleIcon, EyeIcon, ExclamationTriangleIcon } from "@heroicons/react/24/solid"; // Menggunakan ikon solid untuk konsistensi
import { Link, router, usePage } from '@inertiajs/react'; // Menggunakan router dari Inertia

// --- Komponen Kartu Ujian ---
// Sebaiknya ini ada di file terpisah: resources/js/Components/Ujian/KartuUjian.jsx
// dan diimpor ke sini: import KartuUjian from '@/Components/Ujian/KartuUjian';
function KartuUjian({ ujian }) {
  const getStatusInfo = (status) => {
    switch (status) {
      case "Belum Dikerjakan":
        return { chipColor: "light-blue", buttonText: "Mulai Ujian", buttonColor: "blue", buttonIcon: <PlayCircleIcon className="h-5 w-5 mr-1" />, disabled: false, variant: "filled" };
      case "Sedang Dikerjakan":
        return { chipColor: "amber", buttonText: "Lanjutkan Ujian", buttonColor: "amber", buttonIcon: <PlayCircleIcon className="h-5 w-5 mr-1" />, disabled: false, variant: "filled" };
      case "Selesai":
        return { chipColor: "green", buttonText: "Lihat Hasil", buttonColor: "green", variant: "outlined", buttonIcon: <EyeIcon className="h-5 w-5 mr-1" />, disabled: false };
      case "Waktu Habis":
        return { chipColor: "red", buttonText: "Waktu Habis", buttonColor: "red", disabled: true, buttonIcon: <ExclamationTriangleIcon className="h-5 w-5 mr-1" />, variant: "outlined" };
      case "Tidak Tersedia":
      default:
        return { chipColor: "blue-gray", buttonText: "Tidak Tersedia", buttonColor: "blue-gray", disabled: true, buttonIcon: <ExclamationTriangleIcon className="h-5 w-5 mr-1" />, variant: "outlined" };
    }
  };

  const statusInfo = getStatusInfo(ujian.status);

  const handleAksiUjian = () => {
    if (ujian.status === "Belum Dikerjakan") {
      // Ganti dengan nama rute dan parameter yang benar
      // router.get(route('ujian.kerjakan.show', { mataKuliah: ujian.mata_kuliah_id, ujian: ujian.id }));
      alert(`Akan memulai ujian: ${ujian.nama} (ID: ${ujian.id})`);
    } else if (ujian.status === "Sedang Dikerjakan") {
      // router.get(route('ujian.kerjakan.show', { mataKuliah: ujian.mata_kuliah_id, ujian: ujian.id }));
      alert(`Akan melanjutkan ujian: ${ujian.nama} (ID: ${ujian.id})`);
    } else if (ujian.status === "Selesai") {
      // router.get(route('ujian.hasil.show', { ujian: ujian.id }));
      alert(`Akan melihat hasil ujian: ${ujian.nama} (ID: ${ujian.id})`);
    }
  };

  return (
    <Card className="w-full p-5 shadow-md hover:shadow-lg transition-shadow duration-300 border border-blue-gray-50">
      <div className="flex flex-col md:flex-row justify-between gap-4">
        {/* Informasi Utama Ujian */}
        <div className="flex-grow">
          <Typography variant="h5" color="blue-gray" className="mb-1 font-semibold">
            {ujian.nama}
          </Typography>
          {ujian.deskripsi && (
            <Typography variant="paragraph" color="gray" className="font-normal mb-3 text-sm">
              {ujian.deskripsi}
            </Typography>
          )}
          <div className="flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-blue-gray-600 mb-3">
            <div className="flex items-center gap-1" title="Durasi Ujian">
              <ClockIcon className="h-4 w-4 opacity-80" />
              <span>{ujian.durasi || "N/A"}</span>
            </div>
            <div className="flex items-center gap-1" title="Jumlah Soal">
              <ClipboardDocumentListIcon className="h-4 w-4 opacity-80" />
              <span>{ujian.jumlahSoal || "N/A"} Soal</span>
            </div>
            {ujian.batasWaktuPengerjaan && (
              <div className="flex items-center gap-1" title="Batas Waktu Pengerjaan">
                <CalendarDaysIcon className="h-4 w-4 opacity-80" />
                <span>Batas: {ujian.batasWaktuPengerjaan}</span>
              </div>
            )}
          </div>
          {ujian.kkm && (
            <Typography variant="small" color="blue-gray" className="font-medium text-xs">
              KKM: {ujian.kkm}
            </Typography>
          )}
        </div>

        {/* Status dan Tombol Aksi */}
        <div className="flex flex-col items-start md:items-end justify-between flex-shrink-0 md:w-auto md:min-w-[160px] gap-2 pt-2 md:pt-0">
          <Chip 
            value={ujian.status} 
            color={statusInfo.chipColor} 
            size="sm" 
            className="capitalize"
          />
          {ujian.status === "Selesai" && ujian.skor !== undefined && ujian.skor !== null && (
             <Typography variant="h6" color={statusInfo.chipColor} className="mt-1 md:mt-0 font-semibold">
                Skor: {ujian.skor}
             </Typography>
          )}
          <Button
            size="sm"
            color={statusInfo.buttonColor}
            variant={statusInfo.variant}
            onClick={handleAksiUjian}
            disabled={statusInfo.disabled}
            className="w-full md:w-auto flex items-center justify-center gap-1.5 capitalize"
          >
            {statusInfo.buttonIcon}
            {statusInfo.buttonText}
          </Button>
        </div>
      </div>
    </Card>
  );
}
// --- Akhir Komponen Kartu Ujian ---


export default function DaftarUjianPage() {
  // Ambil props mataKuliah dan daftarUjian dari Inertia yang dikirim oleh controller Laravel
  const { mataKuliah, daftarUjian } = usePage().props;

  const handleKembali = () => {
    // Logika untuk kembali, misalnya ke halaman daftar semua mata kuliah
    // if (route().has('mata-kuliah.index')) { // Cek jika rute 'mata-kuliah.index' ada
    //   router.get(route('mata-kuliah.index'));
    // } else {
      window.history.back(); // Fallback paling sederhana
    // }
  };

  // Memastikan daftarUjian adalah array sebelum di-map, untuk menghindari error jika prop tidak ada atau bukan array
  const ujianList = Array.isArray(daftarUjian) ? daftarUjian : [];

  return (
    <AuthenticatedLayout title={mataKuliah ? `Ujian: ${mataKuliah.nama}` : 'Daftar Ujian'}>
      {/* Header Halaman */}
      <div className="flex items-center justify-between mb-6">
        <div className="flex items-center">
          <IconButton variant="text" color="blue-gray" onClick={handleKembali} className="mr-2">
            <ArrowLeftIcon strokeWidth={2.5} className="h-5 w-5" />
          </IconButton>
          <Typography variant="h4" color="blue-gray" className="flex-wrap font-semibold">
            Ujian untuk: <span className="font-bold">{mataKuliah ? mataKuliah.nama : "Mata Kuliah Tidak Ditemukan"}</span>
          </Typography>
        </div>
        {/* Bisa ditambahkan elemen lain di kanan header jika perlu */}
      </div>

      {/* Daftar Ujian */}
      {ujianList.length > 0 ? (
        <div className="space-y-5"> {/* Memberi jarak antar kartu ujian */}
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