import React from 'react';
import { Button, Tooltip } from "@material-tailwind/react";
import { ArrowLeftIcon, ArrowRightIcon, FlagIcon, CheckCircleIcon } from "@heroicons/react/24/solid";

export default function NavigasiBawah({
  soalSekarangIndex,
  totalSoal,
  isRagu, // Status ragu-ragu untuk soal saat ini
  onSoalSebelumnya,
  onSoalBerikutnya,
  onTandaiRagu,
  onSelesaiUjian,
  semuaSoalTerjawab
}) {
  const tombolSelesai = (
    <Button
      color="green"
      onClick={onSelesaiUjian}
      className="flex items-center gap-2"
      // Gunakan prop baru untuk menonaktifkan tombol
      disabled={!semuaSoalTerjawab}
    >
      <CheckCircleIcon className="h-5 w-5" /> Selesai Ujian
    </Button>
  );

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
        <FlagIcon className="h-5 w-5" /> {isRagu ? "Batal Ragu" : "Ragu-ragu"}
      </Button>

      {soalSekarangIndex === totalSoal - 1 ? (
        // Jika semua soal belum terjawab, bungkus tombol dengan Tooltip
        // untuk memberi tahu pengguna mengapa tombol tidak aktif.
        !semuaSoalTerjawab ? (
          <Tooltip content="Anda harus menjawab semua soal terlebih dahulu">
            {/* Tooltip memerlukan elemen child, jadi kita bungkus tombol dengan span */}
            {/* Ini penting agar tooltip tetap muncul bahkan saat tombol disabled */}
            <span className="cursor-not-allowed">
              {tombolSelesai}
            </span>
          </Tooltip>
        ) : (
          tombolSelesai // Jika sudah terjawab semua, tampilkan tombol seperti biasa
        )
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