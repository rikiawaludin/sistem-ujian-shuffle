import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from "@/Components/ui/dialog";
import UjianAturanForm from '@/Pages/Dosen/Partials/UjianAturanForm'; // Form lama yang sudah direfaktor
import { usePage } from '@inertiajs/react';

export default function UjianAturanDialog({ open, onOpenChange, ujian, onSuccess }) {

    if (!ujian) {
        return null;
    }
    // Di sini kita mungkin perlu mengambil bankSoalSummary dari props utama
    const { bankSoalSummaryByDifficulty } = usePage().props;

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-2xl">
                <DialogHeader>
                    <DialogTitle>Atur Komposisi Soal</DialogTitle>
                    <DialogDescription>Tentukan jumlah soal yang akan diambil untuk ujian: {ujian.judul_ujian}</DialogDescription>
                </DialogHeader>
                <UjianAturanForm
                    ujian={ujian}
                    bankSoalSummary={bankSoalSummaryByDifficulty}
                    onSuccess={onSuccess}
                />
            </DialogContent>
        </Dialog>
    );
}