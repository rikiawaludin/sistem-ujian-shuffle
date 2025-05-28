import React from 'react';
import { Radio, Typography } from "@material-tailwind/react";

export default function OpsiBenarSalah({ soalId, opsiList, jawabanUser, onPilihJawaban }) {
  if (!Array.isArray(opsiList) || opsiList.length === 0) {
    return <Typography color="orange" className="text-sm my-4">Soal Benar/Salah ini tidak memiliki opsi jawaban yang valid.</Typography>;
  }

  return (
    <div className="flex flex-col gap-3">
      {opsiList.map((opsiItem, index) => {
        const optionText = typeof opsiItem === 'object' && opsiItem !== null ? opsiItem.teks : opsiItem;
        const optionValue = typeof opsiItem === 'object' && opsiItem !== null ? (opsiItem.id !== undefined ? opsiItem.id : opsiItem.value) : opsiItem;

        return (
          <Radio
            key={`${soalId}-bs-opsi-${index}`}
            id={`opsi-bs-${soalId}-${index}`}
            name={`soal-bs-${soalId}`}
            label={
              <Typography color="blue-gray" className="font-normal">
                {optionText}
              </Typography>
            }
            value={String(optionValue)}
            checked={String(jawabanUser) === String(optionValue)}
            onChange={() => onPilihJawaban(soalId, optionValue)}
            ripple={true}
            className="hover:before:opacity-0 border-blue-gray-300"
            containerProps={{ className: "p-0 -ml-0.5" }}
            labelProps={{className: "ml-2 text-sm"}}
          />
        );
      })}
    </div>
  );
}