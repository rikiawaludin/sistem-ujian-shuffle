import React, { useEffect } from 'react';
import { useForm } from '@inertiajs/react';

// ==========================================================
// PERBAIKAN UTAMA DI SINI: Pisahkan impor untuk setiap komponen
// ==========================================================
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Checkbox } from "@/Components/ui/checkbox";
import { Textarea } from "@/Components/ui/textarea";
import { Label } from '@/Components/ui/label';
import { DateTimePicker } from '@/Components/ui/DateTimePicker';
import { RadioGroup, RadioGroupItem } from "@/Components/ui/radio-group";
import { Switch } from "@/Components/ui/switch";
import { Slider } from "@/Components/ui/slider";

// TAMBAHKAN FUNGSI HELPER INI
const toLocalISOString = (date) => {
    if (!date) return '';
    const d = new Date(date);
    const tzoffset = d.getTimezoneOffset() * 60000; //offset in milliseconds
    const localISOTime = (new Date(d - tzoffset)).toISOString().slice(0, 16);
    return localISOTime;
};

export default function UjianDetailForm({ ujian, defaultMataKuliahId, onSuccess, onError, isWizardMode = false, initialData = {} }) {
    const isEditMode = !!ujian;

    const { data, setData, post, put, errors, processing, recentlySuccessful } = useForm(
        isWizardMode ? initialData : {
            judul_ujian: ujian?.judul_ujian || '',
            deskripsi: ujian?.deskripsi || '',
            mata_kuliah_id: ujian?.mata_kuliah_id || defaultMataKuliahId,
            durasi: ujian?.durasi || 60,
            kkm: ujian?.kkm || 75,
            tanggal_mulai: ujian?.tanggal_mulai ? toLocalISOString(new Date(ujian.tanggal_mulai)) : '',
            tanggal_selesai: ujian?.tanggal_selesai ? toLocalISOString(new Date(ujian.tanggal_selesai)) : '',
            acak_soal: ujian?.acak_soal ?? true,
            acak_opsi: ujian?.acak_opsi ?? true,
            status: ujian?.status || 'draft',
            visibilitas_hasil: ujian?.visibilitas_hasil ?? true,
            sertakan_esai: ujian?.sertakan_esai ?? false,
            persentase_esai: ujian?.persentase_esai || 0,
        }
    );

    useEffect(() => {
        if (data.tanggal_mulai && data.tanggal_selesai) {
            const start = new Date(data.tanggal_mulai);
            const end = new Date(data.tanggal_selesai);
            const diffMs = end - start;

            if (diffMs > 0) {
                const diffMins = Math.round(diffMs / 60000);
                setData('durasi', diffMins);
            }
        }
    }, [data.tanggal_mulai, data.tanggal_selesai]);


    // useEffect(() => {
    //     // Hanya panggil onSuccess jika tidak dalam wizard mode
    //     if (recentlySuccessful && !isWizardMode) {
    //         onSuccess?.();
    //     }
    // }, [recentlySuccessful]);

    const submit = (e) => {
        e.preventDefault();
        if (isWizardMode) {
            onSuccess?.(data);
            return;
        }

        // Definisikan opsi dengan callback onSuccess dan onError dari props
        const options = {
            preserveScroll: true,
            onSuccess: () => onSuccess?.(),
            onError: (errors) => onError?.(errors),
        };

        if (isEditMode) {
            put(route('dosen.ujian.update', ujian.id), options);
        } else {
            post(route('dosen.ujian.store'), options);
        }
    };

    return (
        <form onSubmit={submit} className="space-y-6 mt-4">
            <div>
                <Label htmlFor="judul_ujian">Judul Ujian</Label>
                <Input id="judul_ujian" value={data.judul_ujian} onChange={e => setData('judul_ujian', e.target.value)} className="mt-1" />
                {errors.judul_ujian && <p className="text-sm text-red-600 mt-1">{errors.judul_ujian}</p>}
            </div>

            <div>
                <Label htmlFor="deskripsi">Deskripsi (Opsional)</Label>
                <Textarea id="deskripsi" value={data.deskripsi} onChange={e => setData('deskripsi', e.target.value)} className="mt-1" />
                {errors.deskripsi && <p className="text-sm text-red-600 mt-1">{errors.deskripsi}</p>}
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                {/* UBAH INPUT WAKTU MULAI */}
                <div className="flex flex-col space-y-1.5">
                    <Label htmlFor="tanggal_mulai">Waktu Mulai</Label>
                    <DateTimePicker
                        value={data.tanggal_mulai}
                        onChange={(date) => setData('tanggal_mulai', toLocalISOString(date))}
                    />
                    {errors.tanggal_mulai && <p className="text-sm text-red-600 mt-1">{errors.tanggal_mulai}</p>}
                </div>

                {/* UBAH INPUT WAKTU SELESAI */}
                <div className="flex flex-col space-y-1.5">
                    <Label htmlFor="tanggal_selesai">Waktu Selesai</Label>
                    <DateTimePicker
                        value={data.tanggal_selesai}
                        onChange={(date) => setData('tanggal_selesai', toLocalISOString(date))}
                    />
                    {errors.tanggal_selesai && <p className="text-sm text-red-600 mt-1">{errors.tanggal_selesai}</p>}
                </div>
                <div className="flex flex-col space-y-1.5">
                    <Label htmlFor="durasi">Durasi (menit)</Label>
                    <Input id="durasi" type="number" value={data.durasi} readOnly className="bg-gray-100" />
                </div>
                <div className="flex flex-col space-y-1.5">
                    <Label htmlFor="kkm">KKM</Label>
                    <Input id="kkm" type="number" value={data.kkm} onChange={e => setData('kkm', e.target.value)} />
                    {errors.kkm && <p className="text-sm text-red-600 mt-1">{errors.kkm}</p>}
                </div>
            </div>

            <div>
                <Label>Status Ujian</Label>
                <RadioGroup
                    value={data.status}
                    onValueChange={(value) => setData('status', value)}
                    className="mt-2 flex space-x-4"
                >
                    <div className="flex items-center space-x-2">
                        <RadioGroupItem value="draft" id="status-draft" />
                        <Label htmlFor="status-draft">Simpan sebagai Draft</Label>
                    </div>
                    <div className="flex items-center space-x-2">
                        <RadioGroupItem value="published" id="status-published" />
                        <Label htmlFor="status-published">Publikasikan Ujian</Label>
                    </div>
                </RadioGroup>
                {errors.status && <p className="text-sm text-red-600 mt-1">{errors.status}</p>}
            </div>


            <div className="flex space-x-6 items-center">
                <div className="flex items-center space-x-2">
                    <Checkbox id="acak_soal" checked={data.acak_soal} onCheckedChange={val => setData('acak_soal', val)} />
                    <Label htmlFor="acak_soal">Acak Soal?</Label>
                </div>
                <div className="flex items-center space-x-2">
                    <Checkbox id="acak_opsi" checked={data.acak_opsi} onCheckedChange={val => setData('acak_opsi', val)} />
                    <Label htmlFor="acak_opsi">Acak Opsi?</Label>
                </div>
                <div className="flex items-center space-x-2">
                    {/* Checkbox ini sekarang mengontrol visibilitas_hasil */}
                    <Checkbox id="visibilitas_hasil" checked={data.visibilitas_hasil} onCheckedChange={val => setData('visibilitas_hasil', val)} />
                    <Label htmlFor="visibilitas_hasil">Izinkan mahasiswa ulas hasil?</Label>
                </div>
            </div>

            <div className="mt-6 pt-6 border-t">
                <Label className="text-base font-semibold">Opsi Soal Esai</Label>
                <div className="flex items-center space-x-2 mt-4">
                    <Switch
                        id="sertakan_esai"
                        checked={data.sertakan_esai}
                        onCheckedChange={(value) => setData('sertakan_esai', value)}
                    />
                    <Label htmlFor="sertakan_esai">Sertakan Soal Esai dalam Ujian Ini?</Label>
                </div>

                {data.sertakan_esai && (
                    <div className="mt-4 pl-2">
                        <Label htmlFor="persentase_esai">Persentase Soal Esai ({data.persentase_esai}%)</Label>
                        <p className="text-sm text-muted-foreground">Tentukan berapa persen dari total soal yang harus berupa esai.</p>
                        <Slider
                            id="persentase_esai"
                            defaultValue={[data.persentase_esai]}
                            max={100}
                            min={0}
                            step={5}
                            onValueChange={(value) => setData('persentase_esai', value[0])}
                            className="mt-3"
                        />
                    </div>
                )}
            </div>

            <div className="flex justify-end pt-4">
                <Button type="submit" disabled={processing}>
                    {isWizardMode ? 'Lanjut ke Atur Soal' : (isEditMode ? 'Simpan Perubahan' : 'Buat Ujian')}
                </Button>
            </div>
        </form>
    );
}