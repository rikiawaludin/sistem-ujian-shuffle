import React from 'react';
import { Button } from "@material-tailwind/react";
import { ArrowLeftIcon, ArrowRightIcon, FlagIcon, CheckCircleIcon } from "@heroicons/react/24/solid";

export default function NavigasiBawah({
  soalSekarangIndex,
  totalSoal,
  isRagu, // Status ragu-ragu untuk soal saat ini
  onSoalSebelumnya,
  onSoalBerikutnya,
  onTandaiRagu,
  onSelesaiUjian
}) {
  return (
    <div className="flex flex-col sm:flex-row items-center justify-between gap-3 mt-8">
      <Button 
        variant="text" 
        color="blue-gray" 
        onClick={onSoalSebelumnya} 
        disabled={soalSekarangIndex === 0} 
        className="flex items-center gap-2"
      >
        <ArrowLeftIcon className="h-5 w-5" /> Soal Sebelumnya
      </Button>
      <Button 
        variant={isRagu ? "filled" : "outlined"} 
        color="amber" 
        onClick={onTandaiRagu} 
        className="flex items-center gap-2"
      >
        <FlagIcon className="h-5 w-5"/> {isRagu ? "Batal Ragu" : "Ragu-ragu"}
      </Button>
      {soalSekarangIndex === totalSoal - 1 ? (
        <Button 
          color="green" 
          onClick={onSelesaiUjian} 
          className="flex items-center gap-2"
        >
          <CheckCircleIcon className="h-5 w-5"/> Selesai Ujian
        </Button>
      ) : (
        <Button 
          color="blue" 
          onClick={onSoalBerikutnya} 
          className="flex items-center gap-2"
        >
          Soal Berikutnya <ArrowRightIcon className="h-5 w-5" />
        </Button>
      )}
    </div>
  );
}