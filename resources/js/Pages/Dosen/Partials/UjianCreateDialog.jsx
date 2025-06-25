import React, { useState, useEffect } from 'react';
import { useForm, usePage, router } from '@inertiajs/react';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from "@/Components/ui/dialog";
import { Button } from '@/Components/ui/button';
import { ArrowLeft } from 'lucide-react';

import UjianDetailForm from '@/Pages/Dosen/Partials/UjianDetailForm';
import UjianAturanForm from '@/Pages/Dosen/Partials/UjianAturanForm';
import { useToast } from "@/hooks/use-toast";

export default function UjianCreateDialog({ open, onOpenChange, mataKuliahId, bankSoalSummary, onSuccess }) {
    const [step, setStep] = useState(1);

    const { toast } = useToast();

    // Form ini akan menampung semua data dari kedua form
    const { data, setData, post, processing, errors, reset, recentlySuccessful } = useForm({
        // Data dari UjianDetailForm
        judul_ujian: '',
        deskripsi: '',
        mata_kuliah_id: mataKuliahId,
        durasi: 60,
        kkm: 75,
        tanggal_mulai: '',
        tanggal_selesai: '',
        acak_soal: true,
        acak_opsi: true,
        tampilkan_hasil: true,

        // Data dari UjianAturanForm
        aturan_soal: {
            mudah: 0,
            sedang: 0,
            sulit: 0,
        },
    });

    const handleDetailsSubmit = (detailData) => {
        setData({ ...data, ...detailData });
        setStep(2);
    };

    // Fungsi submit sekarang menjadi sangat sederhana
    const handleFinalSubmit = () => {
        post(route('dosen.ujian.store'), {
            ...data,
            preserveScroll: true,
            onSuccess: () => {
                toast({
                    title: "Berhasil!",
                    description: "Ujian baru telah berhasil dibuat.",
                    variant: "success", // Atau custom variant jika ada (misal: 'success')
                });
                onOpenChange(false);
                reset();
                setStep(1);
            },
            onError: (errors) => {
                toast({
                    variant: "destructive",
                    title: "Terjadi Kesalahan!",
                    description: "Data ujian tidak dapat disimpan. Silakan periksa kembali isian Anda.",
                });
                console.error("Request GAGAL dengan error:", errors);
                if (errors.judul_ujian || errors.tanggal_mulai || errors.tanggal_selesai) {
                    setStep(1);
                }
            }
        });
    };

    const handleClose = () => {
        onOpenChange(false);
        // Beri sedikit delay agar tidak terlihat aneh saat transisi tutup
        setTimeout(() => {
            reset();
            setStep(1);
        }, 300);
    }

    return (
        <Dialog open={open} onOpenChange={handleClose}>
            <DialogContent className="sm:max-w-2xl">
                <DialogHeader>
                    <div className="flex justify-between items-center">
                        <DialogTitle className="text-xl">
                            {step === 1 ? 'Langkah 1: Detail Ujian' : 'Langkah 2: Atur Komposisi Soal'}
                        </DialogTitle>
                        {step === 2 && (
                            <Button variant="ghost" size="sm" onClick={() => setStep(1)} className="text-sm">
                                <ArrowLeft className="h-4 w-4 mr-2" />
                                Kembali
                            </Button>
                        )}
                    </div>
                    <DialogDescription>
                        {step === 1
                            ? 'Isi semua detail yang diperlukan untuk ujian.'
                            : `Tentukan jumlah soal yang akan diambil dari Bank Soal.`
                        }
                    </DialogDescription>
                </DialogHeader>

                <div className="mt-4">
                    {step === 1 && (
                        <UjianDetailForm
                            // Gunakan 'key' untuk me-remount komponen jika kita kembali, memastikan state initial-nya benar
                            key={`step1-${mataKuliahId}`}
                            // Kita tidak mengirim 'ujian' karena ini mode 'create'
                            defaultMataKuliahId={mataKuliahId}
                            // Ganti fungsi submit bawaan dengan fungsi untuk lanjut ke step 2
                            onSuccess={handleDetailsSubmit}
                            // Kita tandai ini sebagai bagian dari wizard
                            isWizardMode={true}
                            initialData={data} // Berikan state awal
                        />
                    )}

                    {step === 2 && (
                        <UjianAturanForm
                            key="step2"
                            bankSoalSummary={bankSoalSummary}
                            onSuccess={handleFinalSubmit}
                            isWizardMode={true}
                            // Berikan state dan fungsi dari induk sebagai props
                            processing={processing}
                            data={data}
                            setData={setData}
                        />
                    )}
                </div>
            </DialogContent>
        </Dialog>
    );
}