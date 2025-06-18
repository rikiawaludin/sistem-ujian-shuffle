import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from "@/Components/ui/dialog";
import UjianDetailForm from '@/Pages/Dosen/Partials/UjianDetailForm'; // Form lama yang sudah direfaktor

export default function UjianDetailFormDialog({ open, onOpenChange, ujian, mataKuliahId, onSuccess }) {
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-2xl">
                <DialogHeader>
                    <DialogTitle>{ujian ? 'Edit Detail Ujian' : 'Buat Ujian Baru'}</DialogTitle>
                    <DialogDescription>Isi semua detail yang diperlukan untuk ujian.</DialogDescription>
                </DialogHeader>
                <UjianDetailForm 
                    ujian={ujian}
                    defaultMataKuliahId={mataKuliahId}
                    onSuccess={onSuccess}
                />
            </DialogContent>
        </Dialog>
    );
}