import React from 'react';
import { Typography, Card, CardBody, Chip } from "@material-tailwind/react";
import { CheckIcon, XMarkIcon, InformationCircleIcon } from "@heroicons/react/24/solid";

const getOptionLetter = (index) => String.fromCharCode(65 + index);

export default function SoalReviewItem({ soal, nomorUrut,  }) {
    const {
        pertanyaan,
        tipeSoal,
        opsiJawaban,
        jawabanPengguna,
        isBenar,
        penjelasan,
        skorDiperoleh, 
        skorMaksimal,
    } = soal;

    // Ambil semua ID kunci jawaban untuk perbandingan
    const kunciJawabanIds = React.useMemo(() =>
        Array.isArray(opsiJawaban)
            ? opsiJawaban.filter(opsi => opsi.is_kunci_jawaban).map(opsi => String(opsi.id))
            : [],
        [opsiJawaban]
    );

    return (
        <Card className="mb-6 border border-blue-gray-100 shadow-sm">
            <CardBody className="p-5">
                <div className="flex justify-between items-start mb-3">
                    <Typography variant="h6" color="blue-gray" className="font-semibold">
                        Soal No. {nomorUrut}
                    </Typography>
                    {tipeSoal === 'esai' ? (
                        // Jika tipe soal adalah esai
                        skorDiperoleh !== null ? (
                            // Jika sudah dinilai (skor tidak null), tampilkan skornya
                            <Chip
                                value={`Skor: ${skorDiperoleh} / ${skorMaksimal}`}
                                color="blue"
                                size="sm"
                                icon={<CheckIcon />}
                            />
                        ) : (
                            // Jika belum dinilai, tampilkan "Perlu Dinilai"
                            <Chip
                                value="Perlu Dinilai"
                                color="amber"
                                size="sm"
                                icon={<InformationCircleIcon />}
                            />
                        )
                    ) : (
                        // Jika bukan esai, gunakan logika lama (Benar/Salah)
                        <>
                            {isBenar === true && <Chip value="Benar" color="green" size="sm" icon={<CheckIcon />} />}
                            {isBenar === false && <Chip value="Salah" color="red" size="sm" icon={<XMarkIcon />} />}
                        </>
                    )}
                </div>

                <div
                    className="mb-6 prose max-w-none text-blue-gray-800"
                    dangerouslySetInnerHTML={{ __html: pertanyaan }}
                />

                {/* Opsi untuk Pilihan Ganda & Benar/Salah (SINGLE ANSWER) */}
                {(tipeSoal === "pilihan_ganda" || tipeSoal === "benar_salah") && Array.isArray(opsiJawaban) && (
                    <div className="space-y-2 mb-4">
                        <Typography variant="small" className="font-semibold text-blue-gray-700">Opsi Jawaban:</Typography>
                        {opsiJawaban.map((opsi, index) => {
                            const isJawabanUser = String(jawabanPengguna) === String(opsi.id);
                            const isKunci = kunciJawabanIds.includes(String(opsi.id));

                            let bgColor = "bg-blue-gray-50/50";
                            if (isKunci) bgColor = "bg-green-50";
                            if (isJawabanUser && !isBenar) bgColor = "bg-red-50";

                            return (
                                <div key={opsi.id} className={`p-3 rounded-lg text-sm border flex items-center justify-between gap-2 ${bgColor}`}>
                                    <div className="flex items-start gap-2">
                                        <span className="font-medium mr-1">{getOptionLetter(index)}.</span>
                                        <span className="flex-1">{opsi.teks_opsi}</span>
                                    </div>
                                    {isKunci && <Chip value="Kunci Jawaban" size="sm" color="green" variant="ghost" />}
                                    {isJawabanUser && !isBenar && <Chip value="Pilihan Anda" size="sm" color="red" variant="ghost" />}
                                </div>
                            );
                        })}
                    </div>
                )}

                {/* BARU: Tampilan untuk Pilihan Jawaban Ganda (MULTIPLE ANSWERS) */}
                {tipeSoal === "pilihan_jawaban_ganda" && Array.isArray(opsiJawaban) && (
                    <div className="space-y-2 mb-4">
                        <Typography variant="small" className="font-semibold text-blue-gray-700">Opsi Jawaban (Bisa lebih dari satu):</Typography>
                        {opsiJawaban.map((opsi, index) => {
                            const jawabanUserArr = typeof jawabanPengguna === 'string' ? jawabanPengguna.split(',').map(s => s.trim()) : [];
                            const isJawabanUser = jawabanUserArr.includes(String(opsi.id));
                            const isKunci = kunciJawabanIds.includes(String(opsi.id));

                            let bgColor = "bg-blue-gray-50/50"; // Default
                            if (isKunci && isJawabanUser) bgColor = "bg-green-50"; // Pilihan benar
                            else if (!isKunci && isJawabanUser) bgColor = "bg-red-50"; // Pilihan salah
                            else if (isKunci && !isJawabanUser) bgColor = "bg-green-50/70"; // Kunci jawaban yang terlewat

                            return (
                                <div key={opsi.id} className={`p-3 rounded-lg text-sm border flex items-center justify-between gap-2 ${bgColor}`}>
                                    <div className="flex items-start gap-2">
                                        <span className="font-medium mr-1">{getOptionLetter(index)}.</span>
                                        <span className="flex-1">{opsi.teks_opsi}</span>
                                    </div>
                                    <div className="flex gap-2">
                                        {isJawabanUser && <Chip value="Pilihan Anda" size="sm" color={isKunci ? "green" : "red"} variant="ghost" />}
                                        {isKunci && !isJawabanUser && <Chip value="Kunci Jawaban" size="sm" color="green" variant="ghost" />}
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                )}

                {/* BARU: Tampilan untuk Isian Singkat */}
                {tipeSoal === "isian_singkat" && (
                    <div className="space-y-3 mb-4">
                        <div>
                            <Typography variant="small" className="font-semibold text-blue-gray-700 mb-1">Jawaban Anda:</Typography>
                            <Card shadow={false} className={`p-3 border ${isBenar ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'}`}>
                                <Typography className="text-sm text-blue-gray-800">{jawabanPengguna || "- Tidak Dijawab -"}</Typography>
                            </Card>
                        </div>
                        <div>
                            <Typography variant="small" className="font-semibold text-blue-gray-700 mb-1">Kunci Jawaban yang Diterima:</Typography>
                            <Card shadow={false} className="p-3 bg-blue-gray-50/70 border border-blue-gray-200">
                                <Typography className="text-sm text-blue-gray-800">
                                    {opsiJawaban.map(o => o.teks_opsi).join(' / ')}
                                </Typography>
                            </Card>
                        </div>
                    </div>
                )}

                {/* BARU: Tampilan untuk Menjodohkan */}
                {tipeSoal === "menjodohkan" && Array.isArray(opsiJawaban) && (
                    <div className="space-y-3 mb-4">
                        <Typography variant="small" className="font-semibold text-blue-gray-700">Detail Jawaban Menjodohkan:</Typography>
                        {(() => {
                            const opsiMap = new Map(opsiJawaban.map(o => [String(o.id), o]));
                            // jawabanPengguna adalah string 'id1:id1,id2:id2', ubah jadi Map
                            const jawabanPenggunaMap = new Map(
                                (jawabanPengguna || "").split(',').filter(Boolean).map(pair => {
                                    const [leftId, rightId] = pair.split(':');
                                    return [leftId, rightId];
                                })
                            );

                            return opsiJawaban.map(opsiKiri => {
                                const idKiri = String(opsiKiri.id);
                                const idKananPilihanUser = jawabanPenggunaMap.get(idKiri);
                                const isPairCorrect = idKiri === idKananPilihanUser;

                                const teksPilihanUser = idKananPilihanUser
                                    ? opsiMap.get(idKananPilihanUser)?.pasangan_teks
                                    : <span className="italic text-gray-500">- Tidak Dijawab -</span>;

                                return (
                                    <div key={idKiri} className={`p-3 rounded-lg text-sm border ${isPairCorrect ? 'bg-green-50' : 'bg-red-50'}`}>
                                        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                            <div className="flex-1 font-medium">{opsiKiri.teks_opsi}</div>
                                            <div className="flex-1 flex items-center gap-2">
                                                <span className="font-semibold">Dijodohkan dengan:</span>
                                                <span>{teksPilihanUser}</span>
                                                {idKananPilihanUser && (
                                                    isPairCorrect
                                                        ? <CheckIcon className="h-5 w-5 text-green-600" />
                                                        : <XMarkIcon className="h-5 w-5 text-red-600" />
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                );
                            });
                        })()}
                    </div>
                )}

                {/* Jawaban untuk Esai */}
                {tipeSoal === "esai" && (
                    <div className="mb-3">
                        <Typography variant="small" className="font-semibold text-blue-gray-700 mb-1">Jawaban Kamu:</Typography>
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
                            <InformationCircleIcon className="h-5 w-5 text-sky-700" />
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