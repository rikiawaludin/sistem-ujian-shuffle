import React from 'react';
import { Card, CardHeader, CardBody, CardFooter, Typography, Button } from "@material-tailwind/react";
import { Link } from '@inertiajs/react';
import { DocumentTextIcon, UserGroupIcon, AcademicCapIcon, TagIcon } from "@heroicons/react/24/solid"; // Tambahkan AcademicCapIcon & TagIcon

export default function RingkasanMataKuliahCard({ mataKuliah }) {
  const dosenDefault = {
    nama: mataKuliah.dosen?.nama || "Belum ada dosen",
  };

  return (
    <Card className="w-full shadow-lg hover:shadow-xl transition-shadow duration-300 border border-blue-gray-50 overflow-hidden flex flex-col h-full">
      <CardHeader
        floated={false}
        shadow={false}
        color="transparent"
        className="m-0 rounded-none h-40 sm:h-48" // Sesuaikan tinggi gambar
      >
        <img
          src={mataKuliah.img || "/images/placeholder-matakuliah.png"}
          alt={mataKuliah.nama || "Gambar Mata Kuliah"}
          className="h-full w-full object-cover"
        />
      </CardHeader>
      <CardBody className="flex-grow p-4">
        <Typography variant="small" color="blue" className="font-semibold opacity-75 mb-1 flex items-center">
            <AcademicCapIcon className="h-4 w-4 mr-1" /> Mata Kuliah
        </Typography>
        <Typography variant="h5" color="blue-gray" className="mb-1 font-semibold leading-tight">
          {mataKuliah.nama}
        </Typography>
        
        {/* Tampilkan Semester dan Tahun Ajaran */}
        {(mataKuliah.semester || mataKuliah.tahun_ajaran) && (
            <div className="flex items-center text-xs text-blue-gray-600 mb-2">
                <TagIcon className="h-3.5 w-3.5 mr-1 opacity-70" />
                {mataKuliah.semester && <span>Semester {mataKuliah.semester}</span>}
                {mataKuliah.semester && mataKuliah.tahun_ajaran && <span className="mx-1 text-blue-gray-300">|</span>}
                {mataKuliah.tahun_ajaran && <span>{mataKuliah.tahun_ajaran}</span>}
            </div>
        )}

        <Typography variant="small" className="font-normal text-blue-gray-500 mb-3 h-10 overflow-hidden text-ellipsis" title={mataKuliah.deskripsi_singkat || "Tidak ada deskripsi."}>
          {mataKuliah.deskripsi_singkat && mataKuliah.deskripsi_singkat.length > 65
            ? mataKuliah.deskripsi_singkat.substring(0, 62) + "..."
            : mataKuliah.deskripsi_singkat || "Tidak ada deskripsi."}
        </Typography>
        <div className="flex items-center text-xs text-blue-gray-700 mb-1">
          <DocumentTextIcon className="h-4 w-4 mr-1 opacity-70" />
          <span>{mataKuliah.jumlah_ujian_tersedia || 0} Ujian Tersedia</span>
        </div>
        <div className="flex items-center text-xs text-blue-gray-700">
          <UserGroupIcon className="h-4 w-4 mr-1 opacity-70" />
          <span>Dosen: {dosenDefault.nama}</span>
        </div>
      </CardBody>
      <CardFooter className="pt-2 p-4">
        <Link href={route('ujian.daftarPerMataKuliah', { id_mata_kuliah: mataKuliah.id })}>
          <Button 
            size="sm" 
            variant="gradient" 
            color="blue" 
            fullWidth
            className="flex items-center justify-center gap-2"
          >
            Lihat Detail Ujian
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={2} stroke="currentColor" className="w-4 h-4">
              <path strokeLinecap="round" strokeLinejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
          </Button>
        </Link>
      </CardFooter>
    </Card>
  );
}