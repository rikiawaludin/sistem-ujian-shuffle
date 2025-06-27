import React from 'react';
import { usePage } from '@inertiajs/react';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/Components/ui/card";
import { Input } from "@/Components/ui/input";
import { CheckCircle2, AlertTriangle, BarChart3 } from 'lucide-react';

// Komponen AturanItem tidak perlu diubah sama sekali
function AturanItem({ level, label, tersedia, value, onChange, error }) {
    const levelConfig = {
        mudah: { icon: CheckCircle2, color: 'text-green-600', bgColor: 'bg-green-50', borderColor: 'border-green-200' },
        sedang: { icon: BarChart3, color: 'text-blue-600', bgColor: 'bg-blue-50', borderColor: 'border-blue-200' },
        sulit: { icon: AlertTriangle, color: 'text-red-600', bgColor: 'bg-red-50', borderColor: 'border-red-200' },
    };
    const config = levelConfig[level] || levelConfig.sedang;
    const IconComponent = config.icon;

    return (
        <Card className={`transition-all hover:shadow-md ${error ? 'border-red-500' : config.borderColor}`}>
            <CardContent className="p-4">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <div className="flex items-center gap-4">
                        <div className={`flex h-12 w-12 items-center justify-center rounded-full ${config.bgColor}`}>
                            <IconComponent className={`h-6 w-6 ${config.color}`} />
                        </div>
                        <div>
                            <CardTitle className="capitalize text-lg">{label}</CardTitle>
                            <CardDescription>Tersedia: {tersedia} soal</CardDescription>
                        </div>
                    </div>
                    <div className="w-full sm:w-28">
                        <Input
                            type="number"
                            className="h-12 text-center text-xl font-bold"
                            value={value}
                            onChange={e => onChange(level, e.target.value)}
                            max={tersedia}
                            min="0"
                        />
                        {error && <p className="text-xs text-red-500 mt-1">{error}</p>}
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}

// Komponen Utama dengan Logika yang Diperbarui
export default function UjianAturanForm({
    bankSoalSummary,
    onSuccess,
    isWizardMode = false,
    processing,
    data,
    setData,
}) {
    const { errors } = usePage().props;

    // **PERUBAHAN 1: Handler diperbarui untuk menerima 'tipe'**
    const handleAturanChange = (tipe, level, jumlah) => {
        // Ambil jumlah soal yang tersedia dari struktur baru
        const tersedia = bankSoalSummary[tipe]?.[level] || 0;
        let nilai = parseInt(jumlah, 10) || 0;
        if (nilai > tersedia) nilai = tersedia;
        if (nilai < 0) nilai = 0;

        // Set state dengan struktur bersarang
        setData('aturan_soal', {
            ...data.aturan_soal,
            [tipe]: {
                ...data.aturan_soal[tipe],
                [level]: nilai
            }
        });
    };

    const submitAturan = (e) => {
        e.preventDefault();
        onSuccess?.();
    };

    const totalNonEsaiDipilih = Object.values(data.aturan_soal.non_esai || {}).reduce((sum, count) => sum + Number(count), 0);
    const totalEsaiDipilih = Object.values(data.aturan_soal.esai || {}).reduce((sum, count) => sum + Number(count), 0);
    const totalSoalDipilih = totalNonEsaiDipilih + totalEsaiDipilih;

    return (
        <form onSubmit={submitAturan} className="mt-4">
            {/* Area Konten Utama */}
            <div className="space-y-6">
                {/* Grup Soal Pilihan Ganda */}
                <div>
                    <h4 className="text-lg font-medium mb-4">Komposisi Soal Pilihan Ganda & Sejenisnya</h4>
                    <div className="space-y-4">
                        <AturanItem
                            level="mudah"
                            label="Mudah"
                            tersedia={bankSoalSummary.non_esai?.mudah || 0}
                            value={data.aturan_soal.non_esai.mudah}
                            onChange={(level, val) => handleAturanChange('non_esai', level, val)}
                            error={errors['aturan_soal.non_esai.mudah']}
                        />
                        <AturanItem
                            level="sedang"
                            label="Sedang"
                            tersedia={bankSoalSummary.non_esai?.sedang || 0}
                            value={data.aturan_soal.non_esai.sedang}
                            onChange={(level, val) => handleAturanChange('non_esai', level, val)}
                            error={errors['aturan_soal.non_esai.sedang']}
                        />
                        <AturanItem
                            level="sulit"
                            label="Sulit"
                            tersedia={bankSoalSummary.non_esai?.sulit || 0}
                            value={data.aturan_soal.non_esai.sulit}
                            onChange={(level, val) => handleAturanChange('non_esai', level, val)}
                            error={errors['aturan_soal.non_esai.sulit']}
                        />
                    </div>
                </div>

                {/* Grup Soal Esai (Kondisional) */}
                {data.sertakan_esai && (
                    <div>
                        <h4 className="text-lg font-medium mb-4">Komposisi Soal Esai</h4>
                        <div className="space-y-4">
                            <AturanItem
                                level="mudah"
                                label="Esai Mudah"
                                tersedia={bankSoalSummary.esai?.mudah || 0}
                                value={data.aturan_soal.esai.mudah}
                                onChange={(level, val) => handleAturanChange('esai', level, val)}
                                error={errors['aturan_soal.esai.mudah']}
                            />
                            <AturanItem
                                level="sedang"
                                label="Esai Sedang"
                                tersedia={bankSoalSummary.esai?.sedang || 0}
                                value={data.aturan_soal.esai.sedang}
                                onChange={(level, val) => handleAturanChange('esai', level, val)}
                                error={errors['aturan_soal.esai.sedang']}
                            />
                            <AturanItem
                                level="sulit"
                                label="Esai Sulit"
                                tersedia={bankSoalSummary.esai?.sulit || 0}
                                value={data.aturan_soal.esai.sulit}
                                onChange={(level, val) => handleAturanChange('esai', level, val)}
                                error={errors['aturan_soal.esai.sulit']}
                            />
                        </div>
                    </div>
                )}
            </div>

            {/* Area Footer (Total & Tombol) */}
            <div className="flex justify-between items-center mt-8 pt-6 border-t">
                <div className="text-right">
                    <p className="font-semibold text-lg text-gray-800">{totalSoalDipilih}</p>
                    <p className="text-sm text-gray-500">Total Soal Dipilih</p>
                </div>
                <Button type="submit" disabled={processing || totalSoalDipilih === 0}>
                    {isWizardMode ? 'Simpan Ujian & Aturan' : 'Simpan Aturan'}
                </Button>
            </div>
        </form>

    );
}