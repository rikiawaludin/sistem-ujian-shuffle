import React from 'react';
import { Typography, Card, CardBody, Chip } from "@material-tailwind/react";
import { CheckIcon, XMarkIcon, InformationCircleIcon } from "@heroicons/react/24/solid";

const getOptionLetter = (index) => String.fromCharCode(65 + index);

export default function SoalReviewItem({ soal, nomorUrut }) {
    const {
        pertanyaan,
        tipeSoal,
        opsiJawaban,
        jawabanPengguna,
        isBenar,
        penjelasan,
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
                    {isBenar === true && <Chip value="Benar" color="green" size="sm" icon={<CheckIcon />} />}
                    {isBenar === false && <Chip value="Salah" color="red" size="sm" icon={<XMarkIcon />} />}
                    {isBenar === null && <Chip value="Perlu Dinilai" color="amber" size="sm" icon={<InformationCircleIcon />} />}
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
                            const jawabanUserArr = Array.isArray(jawabanPengguna) ? jawabanPengguna.map(String) : [];
                            const isJawabanUser = jawabanUserArr.includes(String(opsi.id));
                            const isKunci = kunciJawabanIds.includes(String(opsi.id));

                            let chip = null;
                            if (isKunci && isJawabanUser) chip = <Chip icon={<CheckIcon />} color="green" variant="ghost" value="Pilihan Benar" size="sm" />;
                            else if (!isKunci && isJawabanUser) chip = <Chip icon={<XMarkIcon />} color="red" variant="ghost" value="Pilihan Salah" size="sm" />;
                            else if (isKunci && !isJawabanUser) chip = <Chip icon={<CheckIcon />} color="green" variant="outlined" value="Kunci Jawaban" size="sm" />;

                            return (
                                <div key={opsi.id} className="p-3 rounded-lg text-sm border flex items-center justify-between gap-2 bg-blue-gray-50/50">
                                    <div className="flex items-start gap-2">
                                        <span className="font-medium mr-1">{getOptionLetter(index)}.</span>
                                        <span className="flex-1">{opsi.teks_opsi}</span>
                                    </div>
                                    {chip}
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
                            // 1. Buat map untuk lookup text dari ID opsi
                            const opsiMap = new Map(opsiJawaban.map(o => [String(o.id), o]));

                            // 2. Parse string jawaban pengguna menjadi map
                            const jawabanPenggunaMap = new Map(
                                (jawabanPengguna || "").split(',').map(pair => {
                                    const [leftId, rightId] = pair.split(':');
                                    return [leftId, rightId];
                                })
                            );

                            // 3. Tampilkan setiap baris perjodohan
                            return opsiJawaban.map(opsiKiri => {
                                const idKiri = String(opsiKiri.id);
                                const idKananPilihanUser = jawabanPenggunaMap.get(idKiri);
                                const isPairCorrect = idKiri === idKananPilihanUser;

                                const teksPilihanUser = idKananPilihanUser
                                    ? opsiMap.get(idKananPilihanUser)?.pasangan_teks
                                    : "- Tidak Dijawab -";

                                const teksKunciJawaban = opsiKiri.pasangan_teks;

                                let bgColor = "bg-blue-gray-50/50";
                                if (isBenar === true) bgColor = "bg-green-50";
                                else if (isBenar === false) bgColor = "bg-red-50";

                                return (
                                    <div key={idKiri} className={`p-3 rounded-lg text-sm border ${bgColor}`}>
                                        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                            {/* Item Soal (Kiri) */}
                                            <div className="flex-1">
                                                <span className="font-medium">{opsiKiri.teks_opsi}</span>
                                            </div>

                                            {/* Jawaban Pengguna (Kanan) */}
                                            <div className="flex-1 flex items-center gap-2">
                                                <span className="font-semibold">Pasangan Pilihan:</span>
                                                <span>{teksPilihanUser}</span>
                                                {idKananPilihanUser && (
                                                    isPairCorrect
                                                        ? <CheckIcon className="h-5 w-5 text-green-600" />
                                                        : <XMarkIcon className="h-5 w-5 text-red-600" />
                                                )}
                                            </div>
                                        </div>

                                        {/* Tampilkan kunci jawaban jika pasangan yang dipilih salah */}
                                        {!isPairCorrect && idKananPilihanUser && (
                                            <div className="mt-2 pt-2 border-t border-gray-200">
                                                <p className="text-xs text-green-700 font-semibold">
                                                    Kunci Jawaban: {teksKunciJawaban}
                                                </p>
                                            </div>
                                        )}
                                    </div>
                                );
                            });
                        })()}
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