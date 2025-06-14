import React, { useEffect } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, usePage } from '@inertiajs/react';
import { Button, Card, Input, Select, Option, Typography } from '@material-tailwind/react';
import { Editor } from '@tinymce/tinymce-react';

// Impor komponen partial yang baru dibuat
import PilihanGandaForm from '@/Pages/Dosen/Partials/PilihanGandaForm';

export default function Form({ soal, mataKuliahOptions }) {
    const isEditMode = !!soal;

    // Helper untuk mendapatkan ID kunci jawaban dari data soal (saat mode edit)
    const getInitialKunciJawabanId = () => {
        if (!isEditMode || !soal.opsi_jawaban) return null;
        // Dari relasi, soal.opsi_jawaban adalah array objek OpsiJawaban
        const kunci = soal.opsi_jawaban.find(opt => opt.is_kunci_jawaban);
        // Kita kembalikan ID dari opsi yang benar tersebut
        return kunci ? kunci.id : null;
    };

    // Helper untuk memformat opsi jawaban dari backend ke state frontend
    const formatOpsiUntukState = () => {
        if (!isEditMode || !soal.opsi_jawaban) {
            // Untuk mode 'create', mulai dengan satu opsi kosong
            return [{ id: `opt_${Date.now()}`, teks: '' }];
        }
        // Untuk mode 'edit', format data dari relasi
        return soal.opsi_jawaban.map(opt => ({ id: opt.id, teks: opt.teks_opsi }));
    };

    const { data, setData, post, put, errors, processing } = useForm({
        pertanyaan: soal?.pertanyaan || '',
        tipe_soal: soal?.tipe_soal || 'pilihan_ganda',
        mata_kuliah_id: soal?.mata_kuliah_id || (mataKuliahOptions?.[0]?.value || ''),
        opsi_jawaban: formatOpsiUntukState(),
        kunci_jawaban_id: getInitialKunciJawabanId(),
        penjelasan: soal?.penjelasan || '',
    });

    // Ketika tipe soal berubah, reset opsi jawaban
    useEffect(() => {
        if (data.tipe_soal === 'benar_salah') {
            setData('opsi_jawaban', [
                { id: 'Benar', teks: 'Benar' },
                { id: 'Salah', teks: 'Salah' },
            ]);
        } else if (data.tipe_soal === 'pilihan_ganda' && !isEditMode) {
            setData('opsi_jawaban', [{ id: `opt_${Date.now()}`, teks: '' }]);
        }
    }, [data.tipe_soal]);

    const handleOptionsChange = (index, value) => {
        const newOptions = [...data.opsi_jawaban];
        newOptions[index].teks = value;
        setData('opsi_jawaban', newOptions);
    };

    const addOption = () => {
        setData('opsi_jawaban', [...data.opsi_jawaban, { id: `opt_${Date.now()}`, teks: '' }]);
    };

    const removeOption = (index) => {
        setData('opsi_jawaban', data.opsi_jawaban.filter((_, i) => i !== index));
    };

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
                <Card className="p-6 shadow-lg border border-blue-gray-50">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <Select label="Tipe Soal" value={data.tipe_soal} onChange={(value) => setData('tipe_soal', value)}>
                                <Option value="pilihan_ganda">Pilihan Ganda</Option>
                                <Option value="benar_salah">Benar/Salah</Option>
                                <Option value="esai">Esai</Option>
                            </Select>
                        </div>
                        <div>
                            <Select
                                label="Kategori (Mata Kuliah)"
                                // 1. Ubah nilai number menjadi string saat diberikan ke komponen Select
                                value={String(data.mata_kuliah_id)}

                                // 2. Ubah kembali value (yang kini string) menjadi number saat form diubah
                                onChange={(value) => setData('mata_kuliah_id', Number(value))}

                                error={!!errors.mata_kuliah_id}
                                disabled={!mataKuliahOptions || mataKuliahOptions.length === 0}
                            >
                                {(mataKuliahOptions || []).map(option => (
                                    // 3. Ubah juga nilai number menjadi string untuk setiap Option
                                    <Option key={option.value} value={String(option.value)}>
                                        {option.label}
                                    </Option>
                                ))}
                            </Select>
                            {(!mataKuliahOptions || mataKuliahOptions.length === 0) && (
                                <Typography variant="small" color="gray" className="mt-1">
                                    Tidak ada mata kuliah yang bisa dipilih.
                                </Typography>
                            )}
                        </div>
                    </div>

                    <div className="mb-6">
                        <Typography variant="h6" color="blue-gray" className="mb-2">Pertanyaan</Typography>
                        <Editor apiKey='oatu6jzb2f3zggwf9ja9c5njnil27bsbiyvc3ow0j5ersbt4' value={data.pertanyaan} onEditorChange={(content) => setData('pertanyaan', content)} init={{ height: 300, menubar: false, plugins: 'lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount', toolbar: 'undo redo | blocks | bold italic forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help' }} />
                        {errors.pertanyaan && <Typography color="red" className="mt-1 text-sm">{errors.pertanyaan}</Typography>}
                    </div>

                    {/* === BAGIAN INI JADI JAUH LEBIH BERSIH === */}
                    {(data.tipe_soal === 'pilihan_ganda' || data.tipe_soal === 'benar_salah') && (
                        <PilihanGandaForm
                            opsiJawaban={data.opsi_jawaban}
                            kunciJawabanId={data.kunci_jawaban_id}
                            errors={errors}
                            onOptionChange={handleOptionsChange}
                            onKeyChange={(optionId) => setData('kunci_jawaban_id', optionId)} // <-- Kirim handler baru
                            onAddOption={addOption}
                            onRemoveOption={removeOption}
                        />
                    )}

                    <div className="mt-6">
                        <Typography variant="h6" color="blue-gray" className="mb-2">Penjelasan (Opsional)</Typography>
                        <Editor apiKey='oatu6jzb2f3zggwf9ja9c5njnil27bsbiyvc3ow0j5ersbt4' value={data.penjelasan} onEditorChange={(content) => setData('penjelasan', content)} init={{ height: 200, menubar: false, plugins: 'lists link code help wordcount', toolbar: 'undo redo | bold italic | bullist numlist | code' }} />
                    </div>

                    <div className="mt-8 flex justify-end">
                        <Button type="submit" disabled={processing} color="blue">
                            {isEditMode ? 'Simpan Perubahan' : 'Buat Soal'}
                        </Button>
                    </div>
                </Card>
            </form>
        </AuthenticatedLayout>
    );
}