import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, Link } from '@inertiajs/react';
import { Button, Card, Input, Select, Option, Textarea, Checkbox, Typography } from '@material-tailwind/react';
import { Editor } from '@tinymce/tinymce-react';

export default function Form({ ujian, mataKuliahOptions }) {
    const isEditMode = !!ujian;
    const { data, setData, post, put, errors, processing } = useForm({
        judul_ujian: ujian?.judul_ujian || '',
        deskripsi: ujian?.deskripsi || '',
        mata_kuliah_id: ujian?.mata_kuliah_id || '',
        durasi: ujian?.durasi || 60,
        kkm: ujian?.kkm || 75,
        tanggal_mulai: ujian?.tanggal_mulai?.substring(0, 16) || '',
        tanggal_selesai: ujian?.tanggal_selesai?.substring(0, 16) || '',
        acak_soal: ujian?.acak_soal ?? true,
        tampilkan_hasil: ujian?.tampilkan_hasil ?? true,
    });

    const submit = (e) => {
        e.preventDefault();
        isEditMode ? put(route('dosen.ujian.update', ujian.id)) : post(route('dosen.ujian.store'));
    };

    return (
        <AuthenticatedLayout title={isEditMode ? 'Edit Ujian' : 'Buat Ujian Baru'}>
            <Head title={isEditMode ? 'Edit Ujian' : 'Buat Ujian Baru'} />
            <form onSubmit={submit}>
                <Card className="p-6">
                    <Typography variant="h5" className="mb-6">{isEditMode ? 'Edit Detail Ujian' : 'Buat Ujian Baru'}</Typography>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Input label="Judul Ujian" value={data.judul_ujian} onChange={e => setData('judul_ujian', e.target.value)} error={!!errors.judul_ujian} />
                        <div>
                            <Select
                                label="Mata Kuliah"
                                value={String(data.mata_kuliah_id)}
                                onChange={val => setData('mata_kuliah_id', val)}
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
                    </div>
                    {/* Deskripsi Editor - Full Width */}
                    <div className="mt-6 mb-6">
                        <Typography variant="h6" color="blue-gray" className="mb-2">
                            Deskripsi (Opsional)
                        </Typography>
                        <Editor
                            apiKey="oatu6jzb2f3zggwf9ja9c5njnil27bsbiyvc3ow0j5ersbt4"
                            value={data.deskripsi}
                            onEditorChange={(content) => setData('deskripsi', content)}
                            init={{
                                height: 200,
                                menubar: false,
                                plugins:
                                    'lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount',
                                toolbar:
                                    'undo redo | blocks | bold italic forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help'
                            }}
                        />
                        {errors.deskripsi && (
                            <Typography color="red" className="mt-1 text-sm">
                                {errors.deskripsi}
                            </Typography>
                        )}
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Input
                            type="datetime-local"
                            label="Waktu Mulai"
                            value={data.tanggal_mulai}
                            onChange={e => setData('tanggal_mulai', e.target.value)}
                            error={!!errors.tanggal_mulai}
                        />
                        <Input
                            type="datetime-local"
                            label="Waktu Selesai"
                            value={data.tanggal_selesai}
                            onChange={e => setData('tanggal_selesai', e.target.value)}
                            error={!!errors.tanggal_selesai}
                        />
                        <Input
                            type="number"
                            label="Durasi (menit)"
                            value={data.durasi}
                            onChange={e => setData('durasi', e.target.value)}
                            error={!!errors.durasi}
                        />
                        <Input
                            type="number"
                            label="KKM"
                            value={data.kkm}
                            onChange={e => setData('kkm', e.target.value)}
                            error={!!errors.kkm}
                        />
                        <Checkbox
                            label="Acak Soal?"
                            checked={data.acak_soal}
                            onChange={e => setData('acak_soal', e.target.checked)}
                        />
                        <Checkbox
                            label="Tampilkan Hasil ke Siswa?"
                            checked={data.tampilkan_hasil}
                            onChange={e => setData('tampilkan_hasil', e.target.checked)}
                        />
                    </div>

                    <div className="flex justify-end mt-6 gap-3">
                        <Link href={route('dosen.ujian.index')}><Button variant="text">Batal</Button></Link>
                        <Button type="submit" disabled={processing}>{isEditMode ? 'Simpan Perubahan' : 'Lanjutkan & Tambah Soal'}</Button>
                    </div>
                </Card>
            </form>
        </AuthenticatedLayout>
    );
}