import React from 'react';
import { Card, CardHeader, CardBody, CardFooter, Typography, Button, Tooltip, Avatar } from "@material-tailwind/react";
import { Link } from '@inertiajs/react';
import defaultMatkulImage from '/public/images/placeholder-matakuliah.jpg'; // Menggunakan path absolut dari root
import defaultAvatar from '/public/images/default-avatar.png'; // Avatar default

export default function RingkasanMataKuliahCard({ mataKuliah }) {
  // Cek apakah data mata kuliah ini sudah ada di sistem ujian lokal
  const hasLocalData = !!mataKuliah.id_matakuliah_lokal;

  return (
    <Card color="transparent" shadow={false} className="flex flex-col h-full">
      <CardHeader
        floated={false}
        color="gray"
        className="mx-0 mt-0 mb-4 h-64 xl:h-40"
      >
        <img
          src={defaultMatkulImage}
          alt={mataKuliah.nama}
          className="h-full w-full object-cover"
        />
      </CardHeader>
      <CardBody className="py-0 px-1 flex-grow">
        <Typography
          variant="small"
          className="font-normal text-blue-gray-500"
        >
          {/* Tag diganti dengan Semester */}
          {mataKuliah.semester ? `Semester ${mataKuliah.semester}` : 'Semester Umum'}
        </Typography>
        <Typography
          variant="h5"
          color="blue-gray"
          className="mt-1 mb-2 font-bold" // Diberi font-bold agar lebih menonjol
        >
          {/* Title diganti dengan Nama Mata Kuliah */}
          {mataKuliah.nama}
        </Typography>
        <Typography
          variant="small"
          className="font-normal text-blue-gray-500"
        >
          {/* Description diganti dengan Nama Dosen */}
          {mataKuliah.dosen?.nama || 'Dosen Belum Ditentukan'}
        </Typography>
      </CardBody>
      <CardFooter className="mt-6 flex items-center justify-between py-0 px-1">
        {/* Tombol "View Project" diubah menjadi "Lihat Detail" */}
        <Link href={hasLocalData ? route('ujian.daftarPerMataKuliah', { id_mata_kuliah: mataKuliah.id_matakuliah_lokal }) : '#'}>
          <Button variant="outlined" size="sm" disabled={!hasLocalData}>
            {hasLocalData ? 'Lihat Detail' : 'Tidak Tersedia'}
          </Button>
        </Link>
        {/* Members diganti dengan Avatar Dosen */}
        <div>
          <Tooltip content={mataKuliah.dosen?.nama || 'Dosen'}>
            <Avatar
              src={defaultAvatar}
              alt={mataKuliah.dosen?.nama}
              size="xs"
              variant="circular"
              className="cursor-pointer border-2 border-white"
            />
          </Tooltip>
        </div>
      </CardFooter>
    </Card>
  );
}