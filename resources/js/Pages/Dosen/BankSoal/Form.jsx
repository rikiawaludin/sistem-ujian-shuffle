import React, { useEffect } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, usePage } from '@inertiajs/react';
import { Button, Card, Select, Option, Typography, Input } from '@material-tailwind/react';
import { Editor } from '@tinymce/tinymce-react';
import PilihanGandaForm from '@/Pages/Dosen/Partials/PilihanGandaForm';

export default function Form({ soal, mataKuliahOptions }) {
    const isEditMode = !!soal;

    const generateEmptyOptions = (count) => {
        return Array.from({ length: count }, (_, i) => ({
            id: `opt_${Date.now()}_${i}`,
            teks: ''
        }));
    };

    // Helper untuk mendapatkan ID kunci jawaban dari data soal (saat mode edit)
    const getInitialKunciJawabanId = () => {
        if (!isEditMode || !soal.opsi_jawaban) return null;
        const kunci = soal.opsi_jawaban.find(opt => opt.is_kunci_jawaban);
        return kunci ? kunci.id : null;
    };

    // Helper untuk memformat opsi jawaban dari backend ke state frontend
    const formatOpsiUntukState = () => {
        // Jika mode edit, format data dari backend
        if (isEditMode && soal.opsi_jawaban) {
            return soal.opsi_jawaban.map(opt => ({
                id: opt.id,
                teks: opt.teks_opsi
            }));
        }
        // Jika mode buat baru, defaultnya adalah 4 opsi kosong untuk 'pilihan_ganda'
        return generateEmptyOptions(4);
    };

    const { data, setData, post, put, errors, processing, reset } = useForm({
        pertanyaan: soal?.pertanyaan || '',
        tipe_soal: soal?.tipe_soal || 'pilihan_ganda',
        level_kesulitan: soal?.level_kesulitan || 'sedang',
        bobot: soal?.bobot || 10,
        mata_kuliah_id: soal?.mata_kuliah_id || (mataKuliahOptions?.[0]?.value || ''),
        opsi_jawaban: formatOpsiUntukState(),
        kunci_jawaban_id: getInitialKunciJawabanId(),
        penjelasan: soal?.penjelasan || '',
    });

    // useEffect ini HANYA untuk sinkronisasi data saat masuk mode EDIT.
    useEffect(() => {
        if (isEditMode && soal) {
            reset({
                pertanyaan: soal.pertanyaan,
                tipe_soal: soal.tipe_soal,
                level_kesulitan: soal.level_kesulitan,
                bobot: soal.bobot,
                mata_kuliah_id: soal.mata_kuliah_id,
                opsi_jawaban: formatOpsiUntukState(),
                kunci_jawaban_id: getInitialKunciJawabanId(),
                penjelasan: soal.penjelasan,
            });
        }
    }, [soal]);

    // Handler baru saat tipe soal diubah oleh pengguna
    const handleTipeSoalChange = (value) => {
        let newOptions = [];
        if (value === 'pilihan_ganda') {
            newOptions = generateEmptyOptions(4);
        } else if (value === 'benar_salah') {
            newOptions = [
                { id: 'Benar', teks: 'Benar' },
                { id: 'Salah', teks: 'Salah' },
            ];
        }
        // Untuk 'esai', newOptions akan menjadi array kosong

        // Update tipe_soal dan opsi_jawaban secara bersamaan
        setData(currentData => ({
            ...currentData,
            tipe_soal: value,
            opsi_jawaban: newOptions,
        }));
    };

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
                            {/* Gunakan handler baru untuk onChange */}
                            <Select label="Tipe Soal" value={data.tipe_soal} onChange={handleTipeSoalChange}>
                                <Option value="pilihan_ganda">Pilihan Ganda</Option>
                                <Option value="benar_salah">Benar/Salah</Option>
                                <Option value="esai">Esai</Option>
                            </Select>
                        </div>
                        <div>
                            <Select
                                label="Mata Kuliah"
                                value={String(data.mata_kuliah_id)}
                                onChange={(value) => setData('mata_kuliah_id', Number(value))}
                                error={!!errors.mata_kuliah_id}
                                disabled={!mataKuliahOptions || mataKuliahOptions.length === 0}
                            >
                                {(mataKuliahOptions || []).map(option => (
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
                        <div>
                            <Select
                                label="Level Kesulitan"
                                value={data.level_kesulitan}
                                onChange={(value) => setData('level_kesulitan', value)}
                                error={!!errors.level_kesulitan}
                            >
                                <Option value="mudah">Mudah</Option>
                                <Option value="sedang">Sedang</Option>
                                <Option value="sulit">Sulit</Option>
                            </Select>
                        </div>
                        <div>
                            <Input
                                type="number"
                                label="Bobot Soal"
                                value={data.bobot}
                                onChange={e => setData('bobot', e.target.value)}
                                error={!!errors.bobot}
                                min="0"
                            />
                        </div>
                    </div>

                    <div className="mb-6">
                        <Typography variant="h6" color="blue-gray" className="mb-2">Pertanyaan</Typography>
                        <Editor apiKey='oatu6jzb2f3zggwf9ja9c5njnil27bsbiyvc3ow0j5ersbt4' value={data.pertanyaan} onEditorChange={(content) => setData('pertanyaan', content)} init={{ height: 300, menubar: false, plugins: 'lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount', toolbar: 'undo redo | blocks | bold italic forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help' }} />
                        {errors.pertanyaan && <Typography color="red" className="mt-1 text-sm">{errors.pertanyaan}</Typography>}
                    </div>

                    {(data.tipe_soal === 'pilihan_ganda' || data.tipe_soal === 'benar_salah') && (
                        <PilihanGandaForm
                            opsiJawaban={data.opsi_jawaban}
                            kunciJawabanId={data.kunci_jawaban_id}
                            errors={errors}
                            onOptionChange={handleOptionsChange}
                            onKeyChange={(optionId) => setData('kunci_jawaban_id', optionId)}
                            onAddOption={addOption}
                            onRemoveOption={removeOption}
                            canManageOptions={data.tipe_soal === 'pilihan_ganda'}
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