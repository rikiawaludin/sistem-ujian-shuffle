import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { Button, Card, Input, Select, Option, Textarea } from '@material-tailwind/react';
// Untuk WYSIWYG, Anda bisa install react-quill atau editor lain
// import ReactQuill from 'react-quill'; 
// import 'react-quill/dist/quill.snow.css';

export default function Form({ soal }) { // 'soal' akan ada jika mode edit
    const isEditMode = !!soal;
    const { data, setData, post, put, errors, processing } = useForm({
        pertanyaan: soal?.pertanyaan || '',
        tipe_soal: soal?.tipe_soal || 'pilihan_ganda',
        opsi_jawaban: soal?.opsi_jawaban || [{ id: 'opt_1', teks: '' }], // Mulai dengan 1 opsi
        kunci_jawaban: soal?.kunci_jawaban || [],
        penjelasan: soal?.penjelasan || '',
        kategori_soal: soal?.kategori_soal || '',
    });

    const handleOptionsChange = (index, value) => {
        const newOptions = [...data.opsi_jawaban];
        newOptions[index].teks = value;
        setData('opsi_jawaban', newOptions);
    };

    const addOption = () => {
        setData('opsi_jawaban', [
            ...data.opsi_jawaban,
            { id: `opt_${Date.now()}`, teks: '' } // Gunakan timestamp untuk ID unik sementara
        ]);
    };

    const removeOption = (index) => {
        const newOptions = data.opsi_jawaban.filter((_, i) => i !== index);
        setData('opsi_jawaban', newOptions);
    };
    
    const handleKeyChange = (optionId) => {
        setData('kunci_jawaban', [optionId]); // Simpan sebagai array
    }

    const submit = (e) => {
        e.preventDefault();
        if (isEditMode) {
            put(route('dosen.bank-soal.update', soal.id));
        } else {
            post(route('dosen.bank-soal.store'));
        }
    };

    return (
        <AuthenticatedLayout user={usePage().props.auth.user} title={isEditMode ? "Edit Soal" : "Buat Soal Baru"}>
            <Head title={isEditMode ? "Edit Soal" : "Buat Soal Baru"} />
            
            <form onSubmit={submit}>
                <Card className="p-6">
                    {/* ... (Kolom input untuk Pertanyaan, Tipe Soal, Kategori) ... */}
                    
                    {/* Pertanyaan dengan Textarea (Ganti dengan ReactQuill jika sudah install) */}
                    <div className="mb-4">
                        <Textarea label="Pertanyaan" value={data.pertanyaan} onChange={e => setData('pertanyaan', e.target.value)} error={!!errors.pertanyaan} />
                    </div>

                    {/* ... Bagian Opsi Jawaban Dinamis ... */}
                    {(data.tipe_soal === 'pilihan_ganda' || data.tipe_soal === 'benar_salah') && (
                        <div className="p-4 border rounded-lg">
                            <Typography variant="h6" className="mb-3">Opsi Jawaban</Typography>
                            {data.opsi_jawaban.map((opsi, index) => (
                                <div key={index} className="flex items-center gap-2 mb-2">
                                    <input type="radio" name="kunci_jawaban" value={opsi.id} checked={data.kunci_jawaban[0] === opsi.id} onChange={() => handleKeyChange(opsi.id)} />
                                    <Input label={`Teks Opsi ${index + 1}`} value={opsi.teks} onChange={e => handleOptionsChange(index, e.target.value)} />
                                    <Button color="red" size="sm" onClick={() => removeOption(index)} disabled={data.opsi_jawaban.length <= 1}>X</Button>
                                </div>
                            ))}
                            <Button size="sm" onClick={addOption}>Tambah Opsi</Button>
                        </div>
                    )}

                    {/* ... (Kolom Penjelasan, Tombol Submit) ... */}
                    <div className="mt-6">
                        <Button type="submit" disabled={processing}>
                            {isEditMode ? 'Simpan Perubahan' : 'Buat Soal'}
                        </Button>
                    </div>
                </Card>
            </form>
        </AuthenticatedLayout>
    );
}