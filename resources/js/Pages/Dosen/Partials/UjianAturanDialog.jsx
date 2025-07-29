import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from "@/Components/ui/dialog";
import UjianAturanForm from '@/Pages/Dosen/Partials/UjianAturanForm';
import { usePage, useForm } from '@inertiajs/react';
import { useEffect } from "react";
import { useToast } from "@/hooks/use-toast";

export default function UjianAturanDialog({ open, onOpenChange, bankSoalSummary, ujian, onSuccess }) {

    const { toast } = useToast();

    // Helper function untuk mengubah format data aturan dari array ke object
    const formatAturanToState = (aturanArray) => {
        const state = {
            non_esai: { mudah: 0, sedang: 0, sulit: 0 },
            esai: { mudah: 0, sedang: 0, sulit: 0 }
        };

        if (aturanArray) {
            aturanArray.forEach(aturan => {
                // Pastikan tipe soal dan level kesulitan ada di state
                if (state[aturan.tipe_soal] && typeof state[aturan.tipe_soal][aturan.level_kesulitan] !== 'undefined') {
                    state[aturan.tipe_soal][aturan.level_kesulitan] = aturan.jumlah_soal;
                }
            });
        }
        return state;
    };

    // const { bankSoalSummary } = usePage().props;

    // 1. Tambahkan state `useForm` di sini
    const { data, setData, put, processing, recentlySuccessful, reset } = useForm({
        aturan_soal: formatAturanToState(ujian?.aturan),
        sertakan_esai: ujian?.sertakan_esai || false,
    });

    // 2. Reset form setiap kali modal dibuka dengan ujian yang berbeda
    useEffect(() => {
        if (open && ujian) {
            // setData untuk memastikan kedua properti (aturan_soal dan sertakan_esai) diperbarui
            setData({
                aturan_soal: formatAturanToState(ujian.aturan),
                sertakan_esai: ujian.sertakan_esai, // <-- TAMBAHKAN INI
            });
        }
    }, [open, ujian]);

     const submit = () => {
        if (ujian && ujian.id) {
            put(route('dosen.ujian.update', ujian.id), {
                preserveScroll: true,
                onSuccess: () => {
                    // Tampilkan notifikasi sukses
                    toast({
                        title: "Berhasil!",
                        description: "Komposisi soal untuk ujian telah berhasil diperbarui.",
                        variant: "success",
                    });
                    // Jalankan callback asli (misalnya untuk menutup dialog)
                    onSuccess?.();
                },
                onError: (errors) => {
                    // Tampilkan notifikasi error
                    toast({
                        variant: "destructive",
                        title: "Terjadi Kesalahan!",
                        description: "Komposisi soal tidak dapat disimpan. Silakan periksa kembali isian Anda.",
                    });
                    console.error("Request GAGAL dengan error:", errors);
                }
            });
        }
    };

    if (!ujian) {
        return null;
    }

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-4xl max-h-[80vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>Atur Komposisi Soal</DialogTitle>
                    <DialogDescription>Tentukan jumlah soal yang akan diambil untuk ujian: {ujian.judul_ujian}</DialogDescription>
                </DialogHeader>
                {/* 5. Kirim props yang dibutuhkan oleh UjianAturanForm */}
                <UjianAturanForm
                    bankSoalSummary={bankSoalSummary}
                    onSuccess={submit} // Panggil fungsi submit lokal
                    processing={processing}
                    data={data}
                    setData={setData}
                // isWizardMode tidak perlu karena ini bukan wizard
                />
            </DialogContent>
        </Dialog>
    );
}