import React from 'react';
import { Typography, Card, CardBody, Chip } from "@material-tailwind/react";
import { AcademicCapIcon, CheckIcon, XMarkIcon } from "@heroicons/react/24/solid";

export default function RingkasanHasilUjian({ hasilUjian }) {
  if (!hasilUjian) return null;

  const lulus = hasilUjian.skorTotal >= hasilUjian.kkm;

  return (
    <Card className="mb-8 shadow-lg border border-blue-gray-100">
      <CardBody className="p-6">
        <Typography variant="h5" color="blue-gray" className="mb-1 font-semibold">{hasilUjian.judulUjian}</Typography>
        <div className="flex items-center text-sm text-blue-gray-600 mb-4">
          <AcademicCapIcon className="h-5 w-5 mr-1.5 opacity-80" /> {hasilUjian.namaMataKuliah}
        </div>

        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-x-6 gap-y-4 text-sm mb-4 border-t border-blue-gray-100 pt-4">
          <div>
            <Typography color="gray" className="font-normal text-xs uppercase">Tanggal:</Typography>
            <Typography color="blue-gray" className="font-medium">{hasilUjian.tanggalPengerjaan}</Typography>
          </div>
          <div>
            <Typography color="gray" className="font-normal text-xs uppercase">Durasi Pengerjaan:</Typography>
            <Typography color="blue-gray" className="font-medium">{hasilUjian.waktuDihabiskan}</Typography>
          </div>
          <div>
            <Typography color="gray" className="font-normal text-xs uppercase">KKM:</Typography>
            <Typography color="blue-gray" className="font-medium">{hasilUjian.kkm}</Typography>
          </div>
          <div>
            <Typography color="gray" className="font-normal text-xs uppercase">Skor Anda:</Typography>
            <Typography variant="h5" color={lulus ? "green" : "red"} className="font-bold">
              {hasilUjian.skorTotal}
            </Typography>
          </div>
          <div className="col-span-2 sm:col-span-1">
            <Typography color="gray" className="font-normal text-xs uppercase">Status Kelulusan:</Typography>
            <Chip
              value={hasilUjian.statusKelulusan}
              color={lulus ? "green" : "red"}
              size="sm"
              className="mt-1 capitalize"
              icon={lulus ? <CheckIcon className="h-4 w-4 stroke-white stroke-2" /> : <XMarkIcon className="h-4 w-4 stroke-white stroke-2" />}
            />
          </div>
          <div className="col-span-full sm:col-span-2 md:col-span-3 lg:col-span-2 border-t border-blue-gray-50 pt-3 mt-3 md:border-t-0 md:pt-0 md:mt-0">
            <Typography color="gray" className="font-normal text-xs uppercase">Rincian Jawaban:</Typography>
            <div className="flex gap-x-4">
              <Typography color="green" className="font-medium">Benar: {hasilUjian.jumlahSoalBenar}</Typography>
              <Typography color="red" className="font-medium">Salah: {hasilUjian.jumlahSoalSalah}</Typography>
              {hasilUjian.jumlahEsai > 0 && (
                <Typography color="blue-gray" className="font-medium">Esai: {hasilUjian.jumlahEsai}</Typography>
              )}
              <Typography color="blue-gray" className="font-medium">Tidak Dijawab: {hasilUjian.jumlahSoalTidakDijawab}</Typography>
            </div>
          </div>
        </div>
      </CardBody>
    </Card>
  );
}