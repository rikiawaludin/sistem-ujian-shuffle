import React from 'react';
import { Typography, Card, Chip } from "@material-tailwind/react";
import { FlagIcon } from "@heroicons/react/24/solid";
import OpsiPilihanGanda from './OpsiPilihanGanda';
import OpsiBenarSalah from './OpsiBenarSalah';
import JawabanEsai from './JawabanEsai';
// Import komponen untuk tipe soal lain jika ada, misal Menjodohkan

export default function SoalDisplay({ soal, jawabanUserSoalIni, statusRaguSoalIni, onPilihJawaban, nomorTampil }) {
  if (!soal) {
    // Bisa return fallback UI atau null jika memang soal belum ada
    return (
      <Card className="p-6 mb-6 shadow-md border border-blue-gray-100">
        <Typography color="blue-gray">Memuat soal...</Typography>
      </Card>
    );
  }

  return (
    <Card className="p-6 mb-6 shadow-md border border-blue-gray-100">
      <div className="flex justify-between items-center">
        <Typography variant="h6" color="blue-gray" className="mb-1">
          Soal No. {nomorTampil}
        </Typography>
        {statusRaguSoalIni && <Chip value="Ragu-ragu" color="amber" size="sm" icon={<FlagIcon className="h-3 w-3" />} />}
      </div>
      <hr className="my-3 border-blue-gray-100" />
      <div
        className="mb-6 prose max-w-none text-blue-gray-800"
        dangerouslySetInnerHTML={{ __html: soal.pertanyaan }}
      />

      {soal.tipe === "pilihan_ganda" && (
        <OpsiPilihanGanda
          soalId={soal.id}
          opsiList={soal.opsi}
          jawabanUser={jawabanUserSoalIni}
          onPilihJawaban={onPilihJawaban}
        />
      )}

      {soal.tipe === "benar_salah" && (
        <OpsiBenarSalah
          soalId={soal.id}
          opsiList={soal.opsi}
          jawabanUser={jawabanUserSoalIni}
          onPilihJawaban={onPilihJawaban}
        />
      )}

      {soal.tipe === "esai" && ( // atau "uraian" sesuai tipe data Anda
        <JawabanEsai
          soalId={soal.id}
          jawabanUser={jawabanUserSoalIni}
          onPilihJawaban={onPilihJawaban}
        />
      )}

      {/* TODO: Tambahkan blok kondisional untuk tipe soal lain seperti "menjodohkan" jika ada.
        Misalnya:
        {soal.tipe === "menjodohkan" && (
          <OpsiMenjodohkan
            soalId={soal.id}
            pasanganList={soal.pasangan} // Asumsi data pasangan ada di soal.pasangan
            jawabanUser={jawabanUserSoalIni}
            onPilihJawaban={onPilihJawaban} // Handler mungkin perlu disesuaikan untuk menjodohkan
          />
        )}
      */}
    </Card>
  );
}