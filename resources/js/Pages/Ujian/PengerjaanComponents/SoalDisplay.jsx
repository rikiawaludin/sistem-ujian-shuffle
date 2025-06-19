import React from 'react';
import { Typography, Card, Chip } from "@material-tailwind/react";
import { FlagIcon } from "@heroicons/react/24/solid";

// Impor semua komponen soal
import OpsiPilihanGanda from './OpsiPilihanGanda';
import OpsiBenarSalah from './OpsiBenarSalah';
import JawabanEsai from './JawabanEsai';
import OpsiMultiJawaban from './OpsiMultiJawaban';
import JawabanIsianSingkat from './JawabanIsianSingkat';
import OpsiMenjodohkan from './OpsiMenjodohkan';

export default function SoalDisplay({ soal, jawabanUserSoalIni, statusRaguSoalIni, onPilihJawaban, nomorTampil }) {
  if (!soal) {
    return <Card className="p-6 mb-6"><Typography>Memuat soal...</Typography></Card>;
  }

  return (
    <Card className="p-6 mb-6 shadow-md border border-blue-gray-100">
      <div className="flex justify-between items-center">
        <Typography variant="h6" color="blue-gray" className="mb-1">Soal No. {nomorTampil}</Typography>
        {statusRaguSoalIni && <Chip value="Ragu-ragu" color="amber" size="sm" icon={<FlagIcon className="h-3 w-3" />} />}
      </div>
      <hr className="my-3 border-blue-gray-100" />
      <div className="mb-6 prose max-w-none text-blue-gray-800" dangerouslySetInnerHTML={{ __html: soal.pertanyaan }} />

      {/* --- BLOK KONDISIONAL LENGKAP --- */}
      {soal.tipe === "pilihan_ganda" && <OpsiPilihanGanda soalId={soal.id} opsiList={soal.opsi} jawabanUser={jawabanUserSoalIni} onPilihJawaban={onPilihJawaban} />}
      {soal.tipe === "pilihan_jawaban_ganda" && <OpsiMultiJawaban soalId={soal.id} opsiList={soal.opsi} jawabanUser={jawabanUserSoalIni} onPilihJawaban={onPilihJawaban} />}
      {soal.tipe === "benar_salah" && <OpsiBenarSalah soalId={soal.id} opsiList={soal.opsi} jawabanUser={jawabanUserSoalIni} onPilihJawaban={onPilihJawaban} />}
      {soal.tipe === "isian_singkat" && <JawabanIsianSingkat soalId={soal.id} jawabanUser={jawabanUserSoalIni} onPilihJawaban={onPilihJawaban} />}
      {soal.tipe === "menjodohkan" && <OpsiMenjodohkan soalId={soal.id} opsiList={soal.opsi} pasanganList={soal.pasangan} jawabanUser={jawabanUserSoalIni} onPilihJawaban={onPilihJawaban} />}
      {soal.tipe === "esai" && <JawabanEsai soalId={soal.id} jawabanUser={jawabanUserSoalIni} onPilihJawaban={onPilihJawaban} />}
    </Card>
  );
}