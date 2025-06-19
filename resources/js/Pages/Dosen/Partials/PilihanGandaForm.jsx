import React from 'react';
import { Button, Input, Radio, Checkbox, Typography } from '@material-tailwind/react'; // Impor Checkbox
import { TrashIcon, PlusIcon } from '@heroicons/react/24/solid';

export default function PilihanGandaForm({
    inputType = 'radio', // 'radio' atau 'checkbox'
    opsiJawaban,
    kunciJawabanId, // Bisa string (untuk radio) atau array (untuk checkbox)
    errors,
    onOptionChange,
    onKeyChange,
    onAddOption,
    onRemoveOption,
    canManageOptions = true,
}) {
    const handleKeyChange = (optionId) => {
        if (inputType === 'checkbox') {
            const newKeys = [...kunciJawabanId];
            const index = newKeys.indexOf(optionId);
            if (index > -1) newKeys.splice(index, 1);
            else newKeys.push(optionId);
            onKeyChange(newKeys);
        } else {
            onKeyChange(optionId);
        }
    };
    
    return (
        <div className="p-4 border border-blue-gray-100 rounded-lg bg-blue-gray-50/50 mt-6">
            <Typography variant="h6" color="blue-gray" className="mb-4">Opsi & Kunci Jawaban</Typography>
            <div className="flex flex-col gap-4">
                {opsiJawaban.map((opsi, index) => (
                    <div key={opsi.id || index} className="flex items-center gap-3">
                        {inputType === 'radio' ? (
                            <Radio name="kunci_jawaban" checked={String(kunciJawabanId) === String(opsi.id)} onChange={() => handleKeyChange(opsi.id)} />
                        ) : (
                            <Checkbox checked={kunciJawabanId?.includes(opsi.id)} onChange={() => handleKeyChange(opsi.id)} />
                        )}
                        <div className="flex-grow">
                            <Input label={`Teks Opsi ${index + 1}`} value={opsi.teks} onChange={(e) => onOptionChange(index, e.target.value)} />
                        </div>
                        {canManageOptions && (
                            <Button size="sm" color="red" variant="outlined" className="p-2" onClick={() => onRemoveOption(index)} disabled={opsiJawaban.length <= 1}>
                                <TrashIcon className="h-4 w-4" />
                            </Button>
                        )}
                    </div>
                ))}
            </div>
            {canManageOptions && (
                <Button size="sm" variant="text" onClick={onAddOption} className="mt-4 flex items-center gap-2">
                    <PlusIcon className="h-4 w-4" /> Tambah Opsi
                </Button>
            )}
        </div>
    );
}