// resources/js/Pages/Ujian/DetailHasilComponents/SoalReviewItem.jsx
import React from 'react';
import { Typography, Card, CardBody, Chip } from "@material-tailwind/react";
import { CheckIcon, XMarkIcon, InformationCircleIcon } from "@heroicons/react/24/solid";

const getOptionLetter = (index) => String.fromCharCode(65 + index);

// Fungsi helper untuk parse JSON dengan aman
const parseJsonSafe = (jsonString, defaultValue = null) => {
  if (typeof jsonString !== 'string') {
    // Jika sudah objek/array (mungkin casting backend berhasil sebagian), kembalikan apa adanya
    // atau jika memang diharapkan string tapi bukan, return default.
    // Untuk kasus ini, kita asumsikan jika bukan string, itu sudah tipe yang benar atau null.
    return jsonString;
  }
  try {
    return JSON.parse(jsonString);
  } catch (e) {
    console.error("Gagal parse JSON string:", jsonString, e);
    return defaultValue; // Kembalikan default jika parse gagal
  }
};

export default function SoalReviewItem({ soal, nomorUrut }) {
  // Parse data yang mungkin masih berupa string JSON
  const opsiJawabanArray = parseJsonSafe(soal.opsiJawaban, []); // Default ke array kosong jika gagal parse
  const jawabanPenggunaParsed = parseJsonSafe(soal.jawabanPengguna);
  const kunciJawabanParsed = parseJsonSafe(soal.kunciJawaban);

  // Normalisasi kunci jawaban dan jawaban pengguna untuk perbandingan yang konsisten
  let kunciJawabanComparableValue = kunciJawabanParsed;
  if (soal.tipeSoal === "pilihan_ganda" || soal.tipeSoal === "benar_salah") {
    if (Array.isArray(kunciJawabanParsed) && kunciJawabanParsed.length === 1) {
      kunciJawabanComparableValue = kunciJawabanParsed[0]; // Ambil elemen pertama jika array ["B"]
    } else if (typeof kunciJawabanParsed === 'object' && kunciJawabanParsed !== null && !Array.isArray(kunciJawabanParsed)) {
      kunciJawabanComparableValue = kunciJawabanParsed.id !== undefined ? kunciJawabanParsed.id : kunciJawabanParsed.teks;
    }
    // Jika sudah string (misalnya dari "\"B\"" menjadi "B"), biarkan
  }

  const jawabanPenggunaNormalized = jawabanPenggunaParsed !== null && jawabanPenggunaParsed !== undefined ? String(jawabanPenggunaParsed) : null;
  const kunciJawabanNormalizedForComparison = kunciJawabanComparableValue !== null && kunciJawabanComparableValue !== undefined ? String(kunciJawabanComparableValue) : null;


  // console.log(`Soal ID: ${soal.idSoal}, Tipe: ${soal.tipeSoal}`);
  // console.log("  Opsi (setelah parse):", opsiJawabanArray);
  // console.log("  Jawaban Pengguna (setelah parse):", jawabanPenggunaParsed, `(Normalized: "${jawabanPenggunaNormalized}")`);
  // console.log("  Kunci Jawaban (setelah parse):", kunciJawabanParsed, `(Comparable: "${kunciJawabanNormalizedForComparison}")`);


  return (
    <Card className="mb-6 border border-blue-gray-100 shadow-sm">
      <CardBody className="p-5">
        <div className="flex justify-between items-start mb-3">
          <Typography variant="h6" color="blue-gray" className="font-semibold">
            Soal No. {soal.nomorSoal || nomorUrut}
          </Typography>
          {/* Indikator Status Jawaban Pengguna di atas soal */}
          {soal.tipeSoal !== "esai" && soal.isBenar === true && <Chip color="green" value="Jawaban Anda Benar" size="sm" icon={<CheckIcon className="h-4 w-4 stroke-white stroke-2" />} className="text-white" />}
          {soal.tipeSoal !== "esai" && soal.isBenar === false && <Chip color="red" value="Jawaban Anda Salah" size="sm" icon={<XMarkIcon className="h-4 w-4 stroke-white stroke-2" />} className="text-white" />}
          {soal.tipeSoal !== "esai" && soal.isBenar === null && (jawabanPenggunaNormalized === null || jawabanPenggunaNormalized === "") && <Chip color="blue-gray" value="Tidak Dijawab" size="sm" />}

          {soal.tipeSoal === "esai" && soal.isBenar === null && <Chip color="amber" value="Perlu Penilaian" size="sm" />}
          {soal.tipeSoal === "esai" && soal.isBenar === true && <Chip color="green" value="Dinilai Benar" size="sm" icon={<CheckIcon className="h-4 w-4 stroke-white stroke-2" />} className="text-white" />}
          {soal.tipeSoal === "esai" && soal.isBenar === false && <Chip color="red" value="Dinilai Salah" size="sm" icon={<XMarkIcon className="h-4 w-4 stroke-white stroke-2" />} className="text-white" />}
        </div>
        <div
          className="mb-6 prose max-w-none text-blue-gray-800"
          dangerouslySetInnerHTML={{ __html: soal.pertanyaan }}
        />

        {/* Opsi untuk Pilihan Ganda & Benar/Salah */}
        {(soal.tipeSoal === "pilihan_ganda" || soal.tipeSoal === "benar_salah") && Array.isArray(opsiJawabanArray) && opsiJawabanArray.length > 0 && (
          <div className="space-y-2 mb-4">
            <Typography variant="small" className="font-semibold text-blue-gray-700">Opsi Jawaban:</Typography>
            {opsiJawabanArray.map((opsi, index) => { // Gunakan opsiJawabanArray yang sudah di-parse
              const opsiTeks = typeof opsi === 'object' && opsi !== null ? opsi.teks : opsi;
              const opsiValueNormalized = String(typeof opsi === 'object' && opsi !== null ? (opsi.id !== undefined ? opsi.id : opsi.value) : opsi);

              const isKunci = kunciJawabanNormalizedForComparison !== null && opsiValueNormalized === kunciJawabanNormalizedForComparison;
              const isJawabanUser = jawabanPenggunaNormalized !== null && opsiValueNormalized === jawabanPenggunaNormalized;

              let chipUntukOpsi = null;
              let bgColor = "bg-blue-gray-50/50";
              let borderColor = "border-blue-gray-200";
              let textColor = "text-blue-gray-700";
              let fontWeight = "";

              if (isJawabanUser) {
                fontWeight = "font-semibold";
                if (soal.isBenar === true) {
                  bgColor = "bg-green-50"; borderColor = "border-green-300"; textColor = "text-green-700";
                  chipUntukOpsi = <Chip value="Pilihan Anda" size="sm" color="green" variant="ghost" className="!bg-opacity-60" />;
                } else if (soal.isBenar === false) {
                  bgColor = "bg-red-50"; borderColor = "border-red-300"; textColor = "text-red-700";
                  chipUntukOpsi = <Chip value="Pilihan Anda" size="sm" color="red" variant="ghost" className="!bg-opacity-60" />;
                }
              }

              if (isKunci && (!isJawabanUser || (isJawabanUser && soal.isBenar === false))) {
                bgColor = "bg-green-100"; borderColor = "border-green-500"; textColor = "text-green-800"; fontWeight = "font-bold";
                if (!isJawabanUser) { // Hanya tampilkan "Kunci Jawaban" jika ini bukan pilihan user yang salah
                  chipUntukOpsi = <Chip value="Kunci Jawaban" size="sm" color="green" variant="filled" className="bg-green-600 text-white" />;
                }
              }

              return (
                <div
                  key={`${soal.idSoal}-opt-${index}`}
                  className={`p-3 rounded-lg text-sm border flex items-center justify-between gap-2 ${bgColor} ${borderColor} ${textColor} ${fontWeight}`}
                >
                  <div className="flex items-start gap-2">
                    <span className={`font-medium mr-1`}>
                      {(soal.tipeSoal === "pilihan_ganda") ? `${getOptionLetter(index)}.` : ''}
                    </span>
                    <span className="flex-1">{opsiTeks}</span>
                  </div>
                  {chipUntukOpsi}
                </div>
              );
            })}
          </div>
        )}

        {/* Jawaban dan Kunci Jawaban untuk Esai */}
        {soal.tipeSoal === "esai" && (
          <>
            <div className="mb-3">
              <Typography variant="small" className="font-semibold text-blue-gray-700 mb-1">Jawaban Anda:</Typography>
              <Card shadow={false} className="p-3 bg-blue-gray-50/70 border border-blue-gray-200 rounded-lg">
                <Typography variant="paragraph" className="text-sm text-blue-gray-800 whitespace-pre-line">
                  {jawabanPenggunaParsed || "- Tidak Dijawab -"} {/* Gunakan jawabanPenggunaParsed */}
                </Typography>
              </Card>
            </div>
            {/* kunciJawabanParsed untuk esai seharusnya string deskriptif */}
            {typeof kunciJawabanParsed === 'string' && kunciJawabanParsed.trim() !== "" && (
              <div className="mb-4">
                <Typography variant="small" className="font-semibold text-green-700 mb-1">Kriteria/Kunci Jawaban Esai:</Typography>
                <Card shadow={false} className="p-3 bg-green-50 border border-green-200 rounded-lg">
                  <Typography variant="paragraph" className="text-sm text-green-800 whitespace-pre-line">
                    {kunciJawabanParsed}
                  </Typography>
                </Card>
              </div>
            )}
          </>
        )}

        {/* Penjelasan Soal */}
        {soal.penjelasan && ( /* soal.penjelasan tidak perlu di-parse jika sudah string biasa */
          <div className="mt-4 p-4 bg-sky-50 border border-sky-200 rounded-lg">
            <div className="flex items-center gap-2 mb-1">
              <InformationCircleIcon className="h-5 w-5 text-sky-700" />
              <Typography variant="small" color="blue-gray" className="font-semibold">Penjelasan:</Typography>
            </div>
            <div
              className="mb-6 prose max-w-none text-blue-gray-800"
              dangerouslySetInnerHTML={{ __html: soal.penjelasan }}
            />
          </div>
        )}
      </CardBody>
    </Card>
  );
}