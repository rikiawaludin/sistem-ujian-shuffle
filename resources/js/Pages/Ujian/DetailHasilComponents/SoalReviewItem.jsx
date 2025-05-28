import React from 'react';
import { Typography, Card, CardBody, Chip } from "@material-tailwind/react";
import { CheckIcon, XMarkIcon, InformationCircleIcon } from "@heroicons/react/24/solid";

const getOptionLetter = (index) => String.fromCharCode(65 + index);

export default function SoalReviewItem({ soal, nomorUrut }) {
  // Menentukan apakah jawaban pengguna adalah jawaban yang benar
  const isJawabanPenggunaBenar = soal.jawabanPengguna === soal.kunciJawaban;

  return (
    <Card className="mb-6 border border-blue-gray-100 shadow-sm">
      <CardBody className="p-5">
        <div className="flex justify-between items-start mb-3">
          <Typography variant="h6" color="blue-gray" className="font-semibold">
            Soal No. {soal.nomorSoal || nomorUrut}
          </Typography>
          {soal.tipeSoal !== "esai" && ( // Hanya tampilkan status benar/salah untuk non-esai di sini
            <>
              {soal.isBenar === true && <Chip color="green" value="Benar" size="sm" icon={<CheckIcon className="h-4 w-4 stroke-white stroke-2"/>} className="text-white"/>}
              {soal.isBenar === false && <Chip color="red" value="Salah" size="sm" icon={<XMarkIcon className="h-4 w-4 stroke-white stroke-2"/>} className="text-white"/>}
            </>
          )}
          {/* Untuk esai, status penilaian bisa berbeda */}
          {soal.tipeSoal === "esai" && soal.isBenar === null && <Chip color="amber" value="Perlu Penilaian" size="sm" />}
          {soal.tipeSoal === "esai" && soal.isBenar === true && <Chip color="green" value="Dinilai Benar" size="sm" icon={<CheckIcon className="h-4 w-4 stroke-white stroke-2"/>} className="text-white"/>}
          {soal.tipeSoal === "esai" && soal.isBenar === false && <Chip color="red" value="Dinilai Salah" size="sm" icon={<XMarkIcon className="h-4 w-4 stroke-white stroke-2"/>} className="text-white"/>}
        </div>
        <Typography variant="paragraph" color="blue-gray" className="mb-4 whitespace-pre-line leading-relaxed">
          {soal.pertanyaan}
        </Typography>

        {/* Opsi untuk Pilihan Ganda & Benar/Salah */}
        {(soal.tipeSoal === "pilihan_ganda" || soal.tipeSoal === "benar_salah") && soal.opsiJawaban && Array.isArray(soal.opsiJawaban) && (
          <div className="space-y-2 mb-4">
            <Typography variant="small" className="font-semibold text-blue-gray-700">Opsi Jawaban:</Typography>
            {soal.opsiJawaban.map((opsi, index) => {
              const opsiTeks = typeof opsi === 'object' && opsi !== null ? opsi.teks : opsi;
              const opsiValue = typeof opsi === 'object' && opsi !== null ? (opsi.id !== undefined ? opsi.id : opsi.value) : opsi;

              const isKunciJawaban = String(opsiValue) === String(soal.kunciJawaban);
              const isJawabanPengguna = String(opsiValue) === String(soal.jawabanPengguna);

              let chipLabel = null;
              let chipColor = "blue-gray";
              let bgColor = "bg-blue-gray-50/50";
              let borderColor = "border-blue-gray-200";
              let textColor = "text-blue-gray-700";
              let fontWeight = "";

              if (isKunciJawaban) {
                bgColor = "bg-green-50";
                borderColor = "border-green-300";
                textColor = "text-green-700";
                if (isJawabanPengguna) { // Jawaban pengguna benar
                    chipLabel = "Jawaban Anda (Benar)";
                    chipColor = "green";
                    fontWeight = "font-bold";
                } else { // Ini kunci jawaban, tapi bukan jawaban pengguna
                    chipLabel = "Kunci Jawaban";
                    chipColor = "green";
                }
              } else if (isJawabanPengguna) { // Jawaban pengguna, tapi salah
                bgColor = "bg-red-50";
                borderColor = "border-red-300";
                textColor = "text-red-700";
                chipLabel = "Jawaban Anda (Salah)";
                chipColor = "red";
                fontWeight = "font-bold";
              }

              return (
                <div 
                  key={index} 
                  className={`p-3 rounded-lg text-sm border flex items-start gap-2 ${bgColor} ${borderColor} ${textColor} ${fontWeight}`}
                >
                  <span className={`font-medium mr-1 ${fontWeight ? 'text-inherit' : 'text-blue-gray-800'}`}>
                    {soal.tipeSoal === "pilihan_ganda" ? `${getOptionLetter(index)}.` : ''}
                  </span>
                  <span className="flex-1">{opsiTeks}</span>
                  {chipLabel && <Chip value={chipLabel} size="sm" variant="ghost" color={chipColor} className={`ml-auto !bg-opacity-50`} />}
                </div>
              );
            })}
          </div>
        )}

        {/* Jawaban untuk Esai */}
        {soal.tipeSoal === "esai" && (
          <>
            <div className="mb-3">
              <Typography variant="small" className="font-semibold text-blue-gray-700 mb-1">Jawaban Anda:</Typography>
              <Card variant="outlined" className="p-3 bg-blue-gray-50/70 border-blue-gray-200">
                <Typography variant="paragraph" className="text-sm text-blue-gray-800 whitespace-pre-line">
                  {soal.jawabanPengguna || (soal.isBenar === null && soal.jawabanPengguna === "" ? "- Belum Dinilai & Tidak Dijawab -" : (soal.jawabanPengguna === "" ? "- Tidak Dijawab -" : soal.jawabanPengguna))}
                </Typography>
              </Card>
            </div>
            {soal.kunciJawaban && (
              <div className="mb-4">
                  <Typography variant="small" className="font-semibold text-green-700 mb-1">Kriteria/Kunci Jawaban Esai:</Typography>
                  <Card variant="outlined" className="p-3 bg-green-50 border-green-200">
                    <Typography variant="paragraph" className="text-sm text-green-800 whitespace-pre-line">
                      {soal.kunciJawaban}
                    </Typography>
                  </Card>
              </div>
            )}
          </>
        )}

        {/* Penjelasan Soal */}
        {soal.penjelasan && (
          <div className="mt-4 p-4 bg-sky-50 border border-sky-200 rounded-lg">
            <div className="flex items-center gap-2 mb-1">
              <InformationCircleIcon className="h-5 w-5 text-sky-700"/>
              <Typography variant="small" color="blue-gray" className="font-semibold">Penjelasan:</Typography>
            </div>
            <Typography variant="small" color="blue-gray" className="font-normal whitespace-pre-line">
              {soal.penjelasan}
            </Typography>
          </div>
        )}
      </CardBody>
    </Card>
  );
}