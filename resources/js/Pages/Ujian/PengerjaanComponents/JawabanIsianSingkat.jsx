import React from 'react';
import { Input } from "@material-tailwind/react"; // Sesuaikan jika beda library

export default function JawabanIsianSingkat({ soalId, jawabanUser, onPilihJawaban }) {
    return (
        <div>
            <Input
                type="text"
                label="Ketik Jawaban Singkat Anda"
                value={jawabanUser || ""} // Pastikan value tidak undefined
                onChange={(e) => onPilihJawaban(soalId, e.target.value)}
                className="text-sm"
            />
            <p className="text-xs text-gray-500 mt-2">Jawaban tidak case-sensitive (tidak membedakan huruf besar/kecil).</p>
        </div>
    );
}