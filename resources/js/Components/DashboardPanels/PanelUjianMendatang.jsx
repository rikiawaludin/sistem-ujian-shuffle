import React from 'react';
import { Card, Typography, Button } from "@material-tailwind/react";

export default function PanelUjianMendatang() {
  const ujianMendatang = { mataKuliah: "Algoritma & Pemrograman", dosen: "Dr. Indah Kurniawati, S.Kom., M.Kom.", jadwal: "27 Mei 2025, 09:00 WIB", durasi: "90 Menit" };
  return (
    <Card className="p-6 shadow-lg h-full w-full">
      <Typography variant="h5" color="blue-gray" className="mb-3 border-b border-blue-gray-200 pb-2">Ujian Mendatang</Typography>
      {ujianMendatang ? (
        <div className="space-y-1">
          <Typography variant="h6" color="blue-gray">{ujianMendatang.mataKuliah}</Typography>
          <Typography variant="small" color="gray">Dosen: {ujianMendatang.dosen}</Typography>
          <Typography variant="small" color="gray">Jadwal: {ujianMendatang.jadwal}</Typography>
          <Typography variant="small" color="gray">Durasi: {ujianMendatang.durasi}</Typography>
          <Button size="sm" variant="outlined" className="mt-3 w-full">Lihat Detail Riwayat</Button>
        </div>
      ) : (<Typography color="gray">Belum ada ujian yang dijadwalkan.</Typography>)}
    </Card>
  );
}