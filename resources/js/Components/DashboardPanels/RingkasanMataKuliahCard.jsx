import React from 'react';
import {
  Card,
  CardHeader,
  CardBody,
  CardFooter,
  Typography,
  Button,
  Tooltip,
  Avatar, // Jika ingin menampilkan avatar dosen seperti di profile.jsx
} from "@material-tailwind/react";
import { Link } from '@inertiajs/react'; // Menggunakan Link dari Inertia
import { DocumentTextIcon, UserGroupIcon } from "@heroicons/react/24/solid"; // Contoh ikon

export function RingkasanMataKuliahCard({ mataKuliah }) {
  // Asumsi mataKuliah memiliki properti: id, nama, deskripsi_singkat, img (path gambar), jumlah_ujian_tersedia, dosen (objek dengan nama & img)
  const dosenDefault = {
    nama: mataKuliah.dosen?.nama || "Belum ada dosen",
    img: mataKuliah.dosen?.img || "/images/default-avatar.png" // Sediakan avatar default
  };

  return (
    <Card className="w-full shadow-lg hover:shadow-xl transition-shadow duration-300 border border-blue-gray-50 overflow-hidden flex flex-col">
      <CardHeader
        floated={false}
        shadow={false}
        color="transparent"
        className="m-0 rounded-none h-48" // Sesuaikan tinggi gambar (h-48 atau h-40)
      >
        <img
          src={mataKuliah.img || "/images/fisika.jpg"} // Sediakan placeholder jika gambar mata kuliah tidak ada
          alt={mataKuliah.nama}
          className="h-full w-full object-cover"
        />
      </CardHeader>
      <CardBody className="flex-grow p-4">
        <Typography variant="small" color="blue-gray" className="font-normal opacity-75 mb-1">
          Mata Kuliah
        </Typography>
        <Typography variant="h5" color="blue-gray" className="mb-2 font-semibold">
          {mataKuliah.nama}
        </Typography>
        <Typography variant="small" className="font-normal text-blue-gray-500 mb-3 h-12 overflow-hidden text-ellipsis">
          {/* Batasi panjang deskripsi */}
          {mataKuliah.deskripsi_singkat && mataKuliah.deskripsi_singkat.length > 70
            ? mataKuliah.deskripsi_singkat.substring(0, 67) + "..."
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

export default RingkasanMataKuliahCard;