import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from "@/Components/ui/dialog";
import UjianDetailForm from '@/Pages/Dosen/Partials/UjianDetailForm'; // Form lama yang sudah direfaktor
import { useToast } from "@/hooks/use-toast";

export default function UjianDetailFormDialog({ open, onOpenChange, ujian, mataKuliahId, onSuccess }) {

    const { toast } = useToast();

    // 3. Buat handler untuk event sukses dari form
    const handleSuccess = () => {
        toast({
            title: "Berhasil!",
            description: `Detail ujian telah berhasil ${ujian ? 'diperbarui' : 'dibuat'}.`,
            variant: "success",
        });
        // Panggil callback onSuccess asli dari props (misal: menutup dialog)
        onSuccess?.();
    };

    // 4. Buat handler untuk event error dari form
    const handleError = (errors) => {
        toast({
            variant: "destructive",
            title: "Terjadi Kesalahan!",
            description: "Data detail ujian tidak dapat disimpan. Silakan periksa kembali isian Anda.",
        });
        console.error("Request GAGAL dengan error:", errors);
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-2xl max-h-[80vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>{ujian ? 'Edit Detail Ujian' : 'Buat Ujian Baru'}</DialogTitle>
                    <DialogDescription>Isi semua detail yang diperlukan untuk ujian.</DialogDescription>
                </DialogHeader>
                <UjianDetailForm
                    ujian={ujian}
                    defaultMataKuliahId={mataKuliahId}
                    onSuccess={handleSuccess}
                    onError={handleError}
                />
            </DialogContent>
        </Dialog>
    );
}