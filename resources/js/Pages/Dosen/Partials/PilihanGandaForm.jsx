// File: PilihanGandaForm.jsx

import React from 'react';
import { Button, Input, Radio, Typography } from '@material-tailwind/react';
import { TrashIcon, PlusIcon } from '@heroicons/react/24/solid';

export default function PilihanGandaForm({
    opsiJawaban,
    kunciJawabanId,
    errors,
    onOptionChange,
    onKeyChange,
    onAddOption,
    onRemoveOption,
    canManageOptions = true,
}) {
    return (
        <div className="p-4 border border-blue-gray-100 rounded-lg bg-blue-gray-50/50 mt-6">
            <Typography variant="h6" color="blue-gray" className="mb-4">
                Opsi & Kunci Jawaban
            </Typography>

            <div className="flex flex-col gap-4">
                {opsiJawaban.map((opsi, index) => (
                    <div key={opsi.id || index} className="flex items-center gap-3">
                        <Radio
                            name="kunci_jawaban"
                            id={`kunci-${opsi.id}`}
                            checked={String(kunciJawabanId) === String(opsi.id)}
                            onChange={() => onKeyChange(opsi.id)}
                            className="border-blue-gray-400"
                        />
                        <div className="flex-grow">
                            <Input
                                label={`Teks Opsi ${index + 1}`}
                                value={opsi.teks}
                                onChange={(e) => onOptionChange(index, e.target.value)}
                                error={!!(errors.opsi_jawaban && errors.opsi_jawaban[index]?.teks)}
                                // Atribut 'disabled' dihapus agar input selalu bisa diedit
                            />
                        </div>
                        {/* Tombol hapus tetap dikontrol oleh canManageOptions */}
                        {canManageOptions && (
                            <Button size="sm" color="red" variant="outlined" className="p-2" onClick={() => onRemoveOption(index)} disabled={opsiJawaban.length <= 1}>
                                <TrashIcon className="h-4 w-4" />
                            </Button>
                        )}
                    </div>
                ))}
            </div>

            {/* Tombol tambah opsi tetap dikontrol oleh canManageOptions */}
            {canManageOptions && (
                <Button size="sm" variant="text" onClick={onAddOption} className="mt-4 flex items-center gap-2">
                    <PlusIcon className="h-4 w-4" />
                    Tambah Opsi
                </Button>
            )}
            
            {errors.kunci_jawaban_id && <Typography color="red" className="mt-2 text-sm">{errors.kunci_jawaban_id}</Typography>}
        </div>
    );
}