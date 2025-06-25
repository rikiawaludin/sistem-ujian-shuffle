import React, { useEffect } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, usePage } from '@inertiajs/react';
import { Button, Card, Select, Option, Typography, Input } from '@material-tailwind/react';
import { Editor } from '@tinymce/tinymce-react';
import PilihanGandaForm from '@/Pages/Dosen/Partials/PilihanGandaForm';
import MenjodohkanForm from '@/Pages/Dosen/Partials/MenjodohkanForm';
import IsianSingkatForm from '@/Pages/Dosen/Partials/IsianSingkatForm';
import { useToast } from "@/hooks/use-toast";

export default function Form({ soal, mataKuliahOptions, onSuccess, defaultMataKuliahId }) {
    const isEditMode = !!soal;

    const { toast } = useToast();

    const generateEmptyOptions = (count) => {
        return Array.from({ length: count }, (_, i) => ({
            id: `opt_${Date.now()}_${i}`,
            teks: ''
        }));
    };

    const generateEmptyMatching = (count) => Array.from({ length: count }, (_, i) => ({ id: `match_${Date.now()}_${i}`, teks: '', pasangan_teks: '' }));

    // Helper untuk mendapatkan ID kunci jawaban dari data soal (saat mode edit)
    const getInitialKunciJawabanId = () => {
        if (!isEditMode || !soal.opsi_jawaban) return null;

        // Jika tipe soalnya jawaban ganda, kembalikan array ID
        if (soal.tipe_soal === 'pilihan_jawaban_ganda') {
            return soal.opsi_jawaban
                .filter(opt => opt.is_kunci_jawaban)
                .map(opt => opt.id);
        }

        // Jika tidak, kembalikan satu ID seperti biasa
        const kunci = soal.opsi_jawaban.find(opt => opt.is_kunci_jawaban);
        return kunci ? kunci.id : null;
    };


    // Helper untuk memformat opsi jawaban dari backend ke state frontend
    const formatOpsiUntukState = () => {
        if (!isEditMode) return generateEmptyOptions(4); // Default untuk pilihan ganda
        switch (soal.tipe_soal) {
            case 'pilihan_ganda':
            case 'pilihan_jawaban_ganda':
            case 'benar_salah':
            case 'isian_singkat':
                return soal.opsi_jawaban.map(opt => ({ id: opt.id, teks: opt.teks_opsi }));
            case 'menjodohkan':
                return soal.opsi_jawaban.map(opt => ({ id: opt.id, teks: opt.teks_opsi, pasangan_teks: opt.pasangan_teks }));
            default:
                return [];
        }
    };

    const { data, setData, post, put, errors, processing, reset, recentlySuccessful } = useForm({
        pertanyaan: soal?.pertanyaan || '',
        tipe_soal: soal?.tipe_soal || 'pilihan_ganda',
        level_kesulitan: soal?.level_kesulitan || 'sedang',
        bobot: soal?.bobot || 10,
        mata_kuliah_id: soal?.mata_kuliah_id || defaultMataKuliahId || '',
        opsi_jawaban: formatOpsiUntukState(),
        kunci_jawaban_id: getInitialKunciJawabanId(),
        penjelasan: soal?.penjelasan || '',
    });

    // useEffect(() => {
    //     // Cek apakah form baru saja berhasil
    //     if (recentlySuccessful) {
    //         onSuccess?.(); // Panggil fungsi onSuccess untuk menutup modal
    //     }
    // }, [recentlySuccessful]);

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
        let newKunciJawaban = null;

        if (value === 'pilihan_ganda') newOptions = generateEmptyOptions(4);
        if (value === 'pilihan_jawaban_ganda') {
            newOptions = generateEmptyOptions(4);
            newKunciJawaban = []; // Kunci jawaban ganda adalah array
        }
        if (value === 'benar_salah') newOptions = [{ id: 'Benar', teks: 'Benar' }, { id: 'Salah', teks: 'Salah' }];
        if (value === 'menjodohkan') newOptions = generateEmptyMatching(4);
        if (value === 'isian_singkat') newOptions = generateEmptyOptions(1); // Mulai dengan 1 jawaban singkat

        setData(currentData => ({
            ...currentData,
            tipe_soal: value,
            opsi_jawaban: newOptions,
            kunci_jawaban_id: newKunciJawaban
        }));
    };

    const handleOptionChange = (index, value, fieldName = 'teks') => {
        const newOptions = [...data.opsi_jawaban];
        newOptions[index][fieldName] = value;
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

        const options = {
            preserveScroll: true,
            onSuccess: () => {
                // Tampilkan notifikasi sukses
                toast({
                    title: "Berhasil!",
                    description: `Soal telah berhasil ${isEditMode ? 'diperbarui' : 'dibuat'}.`,
                    variant: "success",
                });
                // Panggil props onSuccess untuk menutup modal
                onSuccess?.();
            },
            onError: (errors) => {
                // Tampilkan notifikasi error
                toast({
                    variant: "destructive",
                    title: "Terjadi Kesalahan!",
                    description: "Data soal tidak dapat disimpan. Silakan periksa kembali semua isian Anda.",
                });
                console.error("Submit GAGAL dengan error:", errors);
            },
        };

        if (isEditMode) {
            put(route('dosen.bank-soal.update', soal.id), options);
        } else {
            post(route('dosen.bank-soal.store'), options);
        }
    };

    // DITAMBAHKAN: Logika untuk mencari nama mata kuliah berdasarkan ID
    const selectedCourse = mataKuliahOptions?.find(
        option => option.value === data.mata_kuliah_id
    );
    const courseName = selectedCourse ? selectedCourse.label : 'Mata Kuliah tidak ditemukan';

    return (
        <div>
            <form onSubmit={submit}>
                <Card className="p-6 shadow-lg border border-blue-gray-50">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            {/* Gunakan handler baru untuk onChange */}
                            <Select label="Tipe Soal" value={data.tipe_soal} onChange={handleTipeSoalChange}>
                                <Option value="pilihan_ganda">Pilihan Ganda</Option>
                                <Option value="pilihan_jawaban_ganda">Pilihan Jawaban Ganda</Option>
                                <Option value="benar_salah">Benar/Salah</Option>
                                <Option value="isian_singkat">Isian Singkat</Option>
                                <Option value="menjodohkan">Menjodohkan</Option>
                                <Option value="esai">Esai</Option>
                            </Select>
                        </div>
                        <div>
                            <Input
                                type="text"
                                label="Mata Kuliah"
                                value={courseName}
                                disabled // atau bisa juga pakai readOnly
                                error={!!errors.mata_kuliah_id}
                            />
                            {/* Jika ada error, kita tetap bisa menampilkannya di bawah input */}
                            {errors.mata_kuliah_id && (
                                <Typography color="red" className="mt-1 text-sm">
                                    {errors.mata_kuliah_id}
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

                    {/* === BLOK KONDISIONAL DENGAN PROPS LENGKAP === */}
                    {(data.tipe_soal === 'pilihan_ganda' || data.tipe_soal === 'benar_salah') && (
                        <PilihanGandaForm
                            inputType="radio"
                            opsiJawaban={data.opsi_jawaban}
                            kunciJawabanId={data.kunci_jawaban_id}
                            errors={errors}
                            onOptionChange={handleOptionChange}
                            onKeyChange={(id) => setData('kunci_jawaban_id', id)}
                            onAddOption={addOption}
                            onRemoveOption={removeOption}
                            canManageOptions={data.tipe_soal === 'pilihan_ganda'}
                        />
                    )}
                    {data.tipe_soal === 'pilihan_jawaban_ganda' && (
                        <PilihanGandaForm
                            inputType="checkbox"
                            opsiJawaban={data.opsi_jawaban}
                            kunciJawabanId={data.kunci_jawaban_id || []}
                            errors={errors}
                            onOptionChange={handleOptionChange}
                            onKeyChange={(keys) => setData('kunci_jawaban_id', keys)}
                            onAddOption={addOption}
                            onRemoveOption={removeOption}
                        />
                    )}
                    {data.tipe_soal === 'menjodohkan' && (
                        <MenjodohkanForm
                            opsiJawaban={data.opsi_jawaban}
                            errors={errors}
                            onOptionChange={handleOptionChange} // Menggunakan handler generik
                            onAddOption={addOption}
                            onRemoveOption={removeOption}
                        />
                    )}
                    {data.tipe_soal === 'isian_singkat' && (
                        <IsianSingkatForm
                            opsiJawaban={data.opsi_jawaban}
                            errors={errors}
                            onOptionChange={handleOptionChange} // Menggunakan handler generik
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
        </div>
    );
}