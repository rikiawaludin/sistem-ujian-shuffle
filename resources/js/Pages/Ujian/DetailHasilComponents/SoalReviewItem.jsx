import React from 'react';
import { Typography, Card, CardBody, Chip } from "@material-tailwind/react";
import { CheckIcon, XMarkIcon, InformationCircleIcon } from "@heroicons/react/24/solid";

const getOptionLetter = (index) => String.fromCharCode(65 + index);

export default function SoalReviewItem({ soal, isDosen, onScoreChange, essayScoreData }) {
    const {
        pertanyaan,
        tipeSoal,
        opsiJawaban, // Ini sekarang adalah array objek dari relasi
        jawabanPengguna, // Ini adalah ID Opsi Jawaban yang dipilih user
        isBenar,
        penjelasan,
        nomorSoal,
        jawabanPesertaDetailId, // Kita butuh ini untuk update skor esai
    } = soal;
    
    // Temukan objek opsi yang merupakan kunci jawaban
    const kunciJawabanObj = Array.isArray(opsiJawaban)
        ? opsiJawaban.find(opsi => opsi.is_kunci_jawaban)
        : null;
    
    const kunciJawabanId = kunciJawabanObj ? kunciJawabanObj.id : null;

    return (
        <Card className="mb-6 border border-blue-gray-100 shadow-sm">
            <CardBody className="p-5">
                <div className="flex justify-between items-start mb-3">
                    <Typography variant="h6" color="blue-gray" className="font-semibold">
                        Soal No. {nomorSoal}
                    </Typography>
                    {/* ... (logika chip status jawaban tidak berubah) ... */}
                </div>

                <div
                    className="mb-6 prose max-w-none text-blue-gray-800"
                    dangerouslySetInnerHTML={{ __html: pertanyaan }}
                />

                {/* Opsi untuk Pilihan Ganda & Benar/Salah */}
                {(tipeSoal === "pilihan_ganda" || tipeSoal === "benar_salah") && Array.isArray(opsiJawaban) && (
                    <div className="space-y-2 mb-4">
                        <Typography variant="small" className="font-semibold text-blue-gray-700">Opsi Jawaban:</Typography>
                        {opsiJawaban.map((opsi, index) => {
                            const isJawabanUser = String(jawabanPengguna) === String(opsi.id);
                            const isKunci = opsi.is_kunci_jawaban;

                            let chipUntukOpsi = null;
                            let bgColor = "bg-blue-gray-50/50";
                            let borderColor = "border-blue-gray-200";

                            // Jika ini adalah kunci jawaban yang benar
                            if (isKunci) {
                                bgColor = "bg-green-50"; borderColor = "border-green-300";
                                chipUntukOpsi = <Chip value="Kunci Jawaban" size="sm" color="green" variant="ghost" />;
                            }
                            // Jika ini adalah jawaban user DAN salah
                            if (isJawabanUser && !isBenar) {
                                bgColor = "bg-red-50"; borderColor = "border-red-300";
                                chipUntukOpsi = <Chip value="Pilihan Anda" size="sm" color="red" variant="ghost" />;
                            }
                             // Jika ini adalah jawaban user DAN benar
                            if (isJawabanUser && isBenar) {
                                bgColor = "bg-green-50"; borderColor = "border-green-300";
                                chipUntukOpsi = <Chip value="Pilihan Anda (Benar)" size="sm" color="green" />;
                            }

                            return (
                                <div key={opsi.id} className={`p-3 rounded-lg text-sm border flex items-center justify-between gap-2 ${bgColor} ${borderColor}`}>
                                    <div className="flex items-start gap-2">
                                        <span className="font-medium mr-1">
                                            {tipeSoal === "pilihan_ganda" ? `${getOptionLetter(index)}.` : ''}
                                        </span>
                                        <span className="flex-1">{opsi.teks_opsi}</span>
                                    </div>
                                    {chipUntukOpsi}
                                </div>
                            );
                        })}
                    </div>
                )}
                
                {/* Jawaban untuk Esai */}
                {tipeSoal === "esai" && (
                    <div className="mb-3">
                        <Typography variant="small" className="font-semibold text-blue-gray-700 mb-1">Jawaban Mahasiswa:</Typography>
                        <Card shadow={false} className="p-3 bg-blue-gray-50/70 border border-blue-gray-200 rounded-lg">
                            <Typography variant="paragraph" className="text-sm text-blue-gray-800 whitespace-pre-line">
                                {jawabanPengguna || "- Tidak Dijawab -"}
                            </Typography>
                        </Card>
                    </div>
                )}

                {/* Penjelasan Soal (jika ada) */}
                {penjelasan && (
                    <div className="mt-4 p-4 bg-sky-50 border border-sky-200 rounded-lg">
                        <div className="flex items-center gap-2 mb-1">
                            <InformationCircleIcon className="h-5 w-5 text-sky-700"/>
                            <Typography variant="small" color="blue-gray" className="font-semibold">Penjelasan:</Typography>
                        </div>
                        <div
                            className="prose prose-sm max-w-none text-blue-gray-800"
                            dangerouslySetInnerHTML={{ __html: penjelasan }}
                        />
                    </div>
                )}
            </CardBody>
        </Card>
    );
}