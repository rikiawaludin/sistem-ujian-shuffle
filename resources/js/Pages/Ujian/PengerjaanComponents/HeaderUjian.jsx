import React from 'react';
import { Typography, IconButton, Progress, Chip } from "@material-tailwind/react";
import { ListBulletIcon, ClockIcon } from "@heroicons/react/24/solid";

// Fungsi format waktu bisa ditaruh di sini atau diimpor dari file utilitas
function formatWaktuHeader(totalDetik) {
  const jam = Math.floor(totalDetik / 3600);
  const menit = Math.floor((totalDetik % 3600) / 60);
  const detik = totalDetik % 60;
  return `${jam.toString().padStart(2, '0')}:${menit.toString().padStart(2, '0')}:${detik.toString().padStart(2, '0')}`;
}

export default function HeaderUjian({
  judulUjian,
  namaMataKuliah,
  nomorSoalSekarang,
  totalSoal,
  sisaWaktuDetik,
  onToggleNavigasiSoal,
  progresPersen,
  navigasiSoalOpen // Dibutuhkan untuk mengatur padding
}) {
  return (
    <header className={`
      sticky top-0 z-30 bg-white shadow px-4 py-3 w-full
      transition-all duration-300 ease-in-out
      ${navigasiSoalOpen ? 'md:pr-[20rem]' : 'pr-4'} 
    `}>
      <div className="flex items-center justify-between">
        <div>
          <Typography variant="small" color="blue-gray" className="font-normal opacity-75">
            {namaMataKuliah || "Mata Kuliah"}
          </Typography>
          <Typography variant="h6" color="blue-gray">
            {judulUjian || "Judul Ujian"}
          </Typography>
        </div>
        <div className="flex items-center gap-2 sm:gap-4">
          {totalSoal > 0 && (
            <Chip 
              value={`Soal ${nomorSoalSekarang}/${totalSoal}`} 
              variant="ghost" 
              className="hidden sm:inline-block"
            />
          )}
          <Chip 
            icon={<ClockIcon className="h-4 w-4"/>} 
            value={formatWaktuHeader(sisaWaktuDetik)} 
            color={sisaWaktuDetik < (5 * 60) ? "red" : (sisaWaktuDetik < (15*60) ? "amber" : "green")} 
            variant="ghost"
          />
          <IconButton variant="text" color="blue-gray" onClick={onToggleNavigasiSoal}>
            <ListBulletIcon className="h-6 w-6" />
          </IconButton>
        </div>
      </div>
      {totalSoal > 0 && (
        <Progress 
          value={progresPersen} 
          color="blue" 
          size="sm" 
          className="mt-2 absolute bottom-0 left-0 right-0 rounded-none" 
        />
      )}
    </header>
  );
}