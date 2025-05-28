import React from 'react';
import { Textarea } from "@material-tailwind/react";

export default function JawabanEsai({ soalId, jawabanUser, onPilihJawaban }) {
  return (
    <Textarea
      label="Ketik Jawaban Anda di Sini..."
      value={jawabanUser || ""} // Pastikan value tidak undefined
      onChange={(e) => onPilihJawaban(soalId, e.target.value)}
      rows={6}
      className="text-sm"
    />
  );
}