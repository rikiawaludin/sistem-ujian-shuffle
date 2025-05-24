import React from 'react';
import { Card, Typography, Button } from "@material-tailwind/react";

export default function PanelUjianAktif() {
  const ujianAktif = { mataKuliah: "Fisika Mekanika", dosen: "Prof. Dr. Ir. Agus Hartono", sisaWaktu: "00:45:12" };
  return (
    <Card className="p-6 shadow-lg h-full w-full border-2 border-red-500">
      <Typography variant="h5" color="blue-gray" className="mb-3 border-b border-blue-gray-200 pb-2">Ujian Berlangsung</Typography>
      {ujianAktif ? (
        <div className="space-y-1">
          <Typography variant="h6" color="blue-gray">{ujianAktif.mataKuliah}</Typography>
          <Typography variant="small" color="gray">Dosen: {ujianAktif.dosen}</Typography>
          <Typography variant="h4" color="red" className="my-2">{ujianAktif.sisaWaktu}</Typography>
          <Button size="sm" color="red" className="mt-3 w-full">Lanjutkan Ujian</Button>
        </div>
      ) : (<Typography color="gray">Tidak ada ujian yang sedang berlangsung.</Typography>)}
    </Card>
  );
}