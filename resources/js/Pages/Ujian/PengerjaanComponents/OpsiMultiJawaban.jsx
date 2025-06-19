import React from 'react';
import { Checkbox, Typography } from "@material-tailwind/react"; // Sesuaikan jika beda library

export default function OpsiMultiJawaban({ soalId, opsiList, jawabanUser, onPilihJawaban }) {
    // jawabanUser di sini adalah sebuah array, e.g., [101, 105]
    // Jika belum ada jawaban, defaultnya adalah array kosong
    const currentAnswers = jawabanUser ? String(jawabanUser).split(',').map(id => parseInt(id, 10)) : [];

    const handleCheckboxChange = (optionId) => {
        const newAnswersSet = new Set(currentAnswers);

        if (newAnswersSet.has(optionId)) {
            newAnswersSet.delete(optionId);
        } else {
            newAnswersSet.add(optionId);
        }

        const newAnswersArray = Array.from(newAnswersSet);

        // PERUBAHAN UTAMA:
        // 1. Urutkan ID secara numerik agar konsisten (misal: [38, 37] menjadi [37, 38])
        // 2. Gabungkan menjadi satu string yang dipisahkan koma.
        const jawabanString = newAnswersArray.sort((a, b) => a - b).join(',');

        // Kirim format string yang baru ke state utama
        onPilihJawaban(soalId, jawabanString);
    };

    if (!Array.isArray(opsiList) || opsiList.length === 0) {
        return <Typography color="orange">Soal ini tidak memiliki opsi jawaban.</Typography>;
    }

    return (
        <div className="flex flex-col gap-3">
            <Typography color="blue-gray" className="font-semibold text-sm mb-2">Pilih satu atau lebih jawaban yang benar:</Typography>
            {opsiList.map((opsi, index) => (
                <Checkbox
                    key={`${soalId}-opsi-${opsi.id}`}
                    id={`opsi-${soalId}-${opsi.id}`}
                    label={
                        <Typography color="blue-gray" className="font-normal flex items-center">
                            <span className="font-semibold mr-2">{String.fromCharCode(65 + index)}.</span> {opsi.teks}
                        </Typography>
                    }
                    checked={currentAnswers.includes(opsi.id)}
                    onChange={() => handleCheckboxChange(opsi.id)}
                    ripple={true}
                    className="hover:before:opacity-0 border-blue-gray-300"
                    containerProps={{ className: "p-0 -ml-0.5" }}
                    labelProps={{ className: "ml-2 text-sm" }}
                />
            ))}
        </div>
    );
}