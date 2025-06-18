import React, { useEffect } from 'react';
import { useForm, usePage } from '@inertiajs/react';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/Components/ui/card";
import { Input } from "@/Components/ui/input";
import { CheckCircle2, AlertTriangle, BarChart3 } from 'lucide-react';

// ==========================================================
// 1. Komponen AturanItem yang bersih (hanya untuk tampilan)
// ==========================================================
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

// ==========================================================
// 2. Komponen Utama UjianAturanForm dengan semua logika
// ==========================================================
export default function UjianAturanForm({ ujian, bankSoalSummary, onSuccess }) {
    const { errors } = usePage().props;

    const formatAturanToState = (aturanArray) => {
        const state = { mudah: 0, sedang: 0, sulit: 0 };
        if (aturanArray) {
            aturanArray.forEach(aturan => {
                state[aturan.level_kesulitan] = aturan.jumlah_soal;
            });
        }
        return state;
    };

    const { data, setData, put, processing, recentlySuccessful } = useForm({
        aturan_soal: formatAturanToState(ujian.aturan),
    });

    useEffect(() => {
        if (recentlySuccessful) {
            onSuccess?.();
        }
    }, [recentlySuccessful]);

    const handleAturanChange = (level, jumlah) => {
        const tersedia = bankSoalSummary[level] || 0;
        let nilai = parseInt(jumlah, 10) || 0;
        if (nilai > tersedia) nilai = tersedia;
        if (nilai < 0) nilai = 0;
        
        setData('aturan_soal', { ...data.aturan_soal, [level]: nilai });
    };

    const submitAturan = (e) => {
        e.preventDefault();
        if(ujian && ujian.id) {
            put(route('dosen.ujian.update', ujian.id), { preserveScroll: true });
        }
    };

    const totalSoalTersedia = Object.values(bankSoalSummary || {}).reduce((sum, count) => sum + count, 0);
    const totalSoalDipilih = Object.values(data.aturan_soal).reduce((sum, count) => sum + Number(count), 0);

    return (
        <form onSubmit={submitAturan} className="mt-4">
            {totalSoalTersedia > 0 ? (
                <div className="space-y-4">
                    <AturanItem
                        level="mudah"
                        label="Mudah"
                        tersedia={bankSoalSummary.mudah || 0}
                        value={data.aturan_soal.mudah}
                        onChange={handleAturanChange}
                        error={errors['aturan_soal.mudah']}
                    />
                    <AturanItem
                        level="sedang"
                        label="Sedang"
                        tersedia={bankSoalSummary.sedang || 0}
                        value={data.aturan_soal.sedang}
                        onChange={handleAturanChange}
                        error={errors['aturan_soal.sedang']}
                    />
                    <AturanItem
                        level="sulit"
                        label="Sulit"
                        tersedia={bankSoalSummary.sulit || 0}
                        value={data.aturan_soal.sulit}
                        onChange={handleAturanChange}
                        error={errors['aturan_soal.sulit']}
                    />
                </div>
            ) : (
                <div className="text-center p-8 text-gray-500 border-2 border-dashed rounded-lg">
                    <h3 className="text-lg font-semibold">Bank Soal Kosong</h3>
                    <p>Tidak ada soal di Bank Soal untuk mata kuliah ini.</p>
                </div>
            )}
             <div className="flex justify-between items-center mt-6 pt-4 border-t">
                <div className="text-right">
                    <p className="font-semibold text-gray-800">{totalSoalDipilih}</p>
                    <p className="text-xs text-gray-500">Total Soal Dipilih</p>
                </div>
                <Button type="submit" disabled={processing || totalSoalDipilih === 0}>
                    Simpan Aturan
                </Button>
            </div>
        </form>
    );
}