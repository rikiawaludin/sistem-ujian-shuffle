import React from 'react';
import { Button, Input, Typography } from '@material-tailwind/react';
import { TrashIcon, PlusIcon } from '@heroicons/react/24/solid';

export default function MenjodohkanForm({ opsiJawaban, onOptionChange, onAddOption, onRemoveOption }) {
    return (
        <div className="p-4 border border-blue-gray-100 rounded-lg bg-blue-gray-50/50 mt-6">
            <Typography variant="h6" color="blue-gray" className="mb-4">Soal & Pasangan Jawaban</Typography>
            <div className="flex flex-col gap-4">
                {opsiJawaban.map((opsi, index) => (
                    <div key={opsi.id || index} className="flex items-center gap-3">
                        <div className="flex-grow"><Input label={`Item Soal ${index + 1}`} value={opsi.teks} onChange={(e) => onOptionChange(index, e.target.value, 'teks')} /></div>
                        <div className="flex-grow"><Input label={`Pasangan Jawaban ${index + 1}`} value={opsi.pasangan_teks} onChange={(e) => onOptionChange(index, e.target.value, 'pasangan_teks')} /></div>
                        <Button size="sm" color="red" variant="outlined" className="p-2" onClick={() => onRemoveOption(index)} disabled={opsiJawaban.length <= 1}><TrashIcon className="h-4 w-4" /></Button>
                    </div>
                ))}
            </div>
            <Button size="sm" variant="text" onClick={onAddOption} className="mt-4 flex items-center gap-2"><PlusIcon className="h-4 w-4" /> Tambah Pasangan</Button>
        </div>
    );
}
