import React from 'react';
import { Typography, Button, Card, Chip } from "@material-tailwind/react";
import { ClockIcon, ClipboardDocumentListIcon, CalendarDaysIcon, PlayCircleIcon, EyeIcon, ExclamationTriangleIcon } from "@heroicons/react/24/solid";
import { router } from '@inertiajs/react'; // Hanya impor router

// Fungsi getStatusInfo bisa tetap di sini atau dijadikan utilitas terpisah jika digunakan di banyak tempat
function getStatusInfo(status) {
  switch (status) {
    case "Belum Dikerjakan": return { chipColor: "light-blue", buttonText: "Mulai Ujian", buttonColor: "blue", buttonIcon: <PlayCircleIcon className="h-5 w-5 mr-1" />, disabled: false, variant: "filled" };
    case "Sedang Dikerjakan": return { chipColor: "amber", buttonText: "Lanjutkan Ujian", buttonColor: "amber", buttonIcon: <PlayCircleIcon className="h-5 w-5 mr-1" />, disabled: false, variant: "filled" };
    case "Selesai": return { chipColor: "green", buttonText: "Lihat Hasil", buttonColor: "green", variant: "outlined", buttonIcon: <EyeIcon className="h-5 w-5 mr-1" />, disabled: false };
    default: return { chipColor: "blue-gray", buttonText: "Tidak Tersedia", buttonColor: "blue-gray", disabled: true, buttonIcon: <ExclamationTriangleIcon className="h-5 w-5 mr-1" />, variant: "outlined" };
  }
}

export default function KartuUjian({ ujian }) {
  const statusInfo = getStatusInfo(ujian.status);

  const handleAksiUjian = () => {
    if (ujian.status === "Belum Dikerjakan" || ujian.status === "Sedang Dikerjakan") {
      router.get(route('ujian.kerjakan', { id_ujian: ujian.id }));
    } else if (ujian.status === "Selesai") {
      // Pastikan ujian.id_pengerjaan_terakhir ada dan valid
      if (ujian.id_pengerjaan_terakhir) {
        router.get(route('ujian.hasil.detail', { id_attempt: ujian.id_pengerjaan_terakhir }));
      } else {
        console.warn("Tidak ada ID pengerjaan terakhir untuk ujian yang sudah selesai:", ujian);
        // Bisa tampilkan notifikasi atau disable tombol jika tidak ada id_pengerjaan_terakhir
      }
    }
  };

  return (
    <Card className="w-full p-5 shadow-md hover:shadow-lg transition-shadow duration-300 border border-blue-gray-50">
      <div className="flex flex-col md:flex-row justify-between gap-4">
        <div className="flex-grow">
          <Typography variant="h5" color="blue-gray" className="mb-1 font-semibold">{ujian.nama}</Typography>
          {ujian.deskripsi && <Typography variant="paragraph" color="gray" className="font-normal mb-3 text-sm">{ujian.deskripsi}</Typography>}
          <div className="flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-blue-gray-600 mb-3">
            <div className="flex items-center gap-1" title="Durasi Ujian"><ClockIcon className="h-4 w-4 opacity-80" /><span>{ujian.durasi || "N/A"}</span></div>
            <div className="flex items-center gap-1" title="Jumlah Soal"><ClipboardDocumentListIcon className="h-4 w-4 opacity-80" /><span>{ujian.jumlahSoal || "N/A"} Soal</span></div>
            {ujian.batasWaktuPengerjaan && <div className="flex items-center gap-1" title="Batas Waktu Pengerjaan"><CalendarDaysIcon className="h-4 w-4 opacity-80" /><span>Batas: {ujian.batasWaktuPengerjaan}</span></div>}
          </div>
          {ujian.kkm && <Typography variant="small" color="blue-gray" className="font-medium text-xs">KKM: {ujian.kkm}</Typography>}
        </div>
        <div className="flex flex-col items-start md:items-end justify-between flex-shrink-0 md:w-auto md:min-w-[160px] gap-2 pt-2 md:pt-0">
          <Chip value={ujian.status} color={statusInfo.chipColor} size="sm" className="capitalize"/>
          {ujian.status === "Selesai" && ujian.skor !== undefined && ujian.skor !== null && (
             <Typography variant="h6" color={ujian.skor >= (ujian.kkm || 0) ? "green" : "red"} className="mt-1 md:mt-0 font-semibold">Skor: {ujian.skor}</Typography>
          )}
          <Button 
            size="sm" 
            color={statusInfo.buttonColor} 
            variant={statusInfo.variant} 
            onClick={handleAksiUjian} 
            disabled={statusInfo.disabled || (ujian.status === "Selesai" && !ujian.id_pengerjaan_terakhir)} // Disable lihat hasil jika tidak ada id pengerjaan
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