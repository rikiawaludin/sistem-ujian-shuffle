import React from 'react';
import { Card, Typography, Button } from "@material-tailwind/react";

export default function PanelRiwayatUjian() {
  const ujianTerakhir = { mataKuliah: "Kalkulus Dasar", dosen: "Dr. Retno Wulandari, M.Si.", status: "Selesai pada 20 Mei 2025", nilai: 85 };
  return (
    <Card className="p-6 shadow-lg h-full w-full">
      <Typography variant="h5" color="blue-gray" className="mb-3 border-b border-blue-gray-200 pb-2">Ujian Terakhir</Typography>
      {ujianTerakhir ? (
        <div className="space-y-1">
          <Typography variant="h6" color="blue-gray">{ujianTerakhir.mataKuliah}</Typography>
          <Typography variant="small" color="gray">Dosen: {ujianTerakhir.dosen}</Typography>
          <Typography variant="small" color="gray">Status: {ujianTerakhir.status}</Typography>
          {ujianTerakhir.nilai && <Typography variant="small" color="blue" className="font-semibold">Nilai: {ujianTerakhir.nilai}</Typography>}
          <Button size="sm" variant="outlined" className="mt-3 w-full">Lihat Detail Riwayat</Button>
        </div>
      ) : (<Typography color="gray">Belum ada riwayat ujian.</Typography>)}
    </Card>
  );
}