import React, { useEffect, useState } from 'react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Textarea } from '@/components/ui/textarea';
import { Input } from '@/components/ui/input';
import { ArrowLeft, User, Save, CheckCircle } from 'lucide-react';
import { useToast } from "@/hooks/use-toast";
import { ToastAction } from "@/components/ui/toast";
import { Badge } from '@/components/ui/badge';

import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from "@/components/ui/alert-dialog";

export default function Koreksi({ auth, pengerjaan }) {
    const { props } = usePage();
    const { toast } = useToast();

    // DITAMBAHKAN: State untuk mengontrol dialog konfirmasi
    const [isFinalizeDialogOpen, setIsFinalizeDialogOpen] = useState(false);

    const forms = pengerjaan.detail_jawaban.map(jawaban => {
        const { post, setData, data, errors, processing } = useForm({
            skor_per_soal: jawaban.skor_per_soal || 0,
        });

        function handleSubmit(e) {
            e.preventDefault();
            // DIUBAH: Tambahkan callback onSuccess di sini
            post(route('dosen.ujian.koreksi.simpan', jawaban.id), {
                preserveScroll: true,
                onSuccess: () => {
                    toast({
                        title: "Skor Disimpan!",
                        description: `Skor untuk soal ini telah berhasil diperbarui.`,
                        variant: "success",
                        duration: 3000, // Tampilkan selama 3 detik
                    });
                },
                onError: () => {
                    toast({
                        title: "Gagal Menyimpan",
                        description: "Terjadi kesalahan. Periksa kembali nilai yang Anda masukkan.",
                        variant: "destructive",
                    });
                }
            });
        }
        return { post, setData, data, errors, processing, handleSubmit, id: jawaban.id };
    });

    const { post: postFinalisasi, processing: processingFinalisasi } = useForm({});
    function handleFinalisasi() {
        postFinalisasi(route('dosen.ujian.koreksi.finalisasi', pengerjaan.id), {
            onSuccess: () => {
                toast({
                    title: "Finalisasi Berhasil!",
                    description: "Skor akhir mahasiswa telah berhasil dihitung dan disimpan.",
                    variant: "success",
                });
            },
        });
    }

    useEffect(() => {
        if (props.flash?.all_essays_graded) {
            toast({
                title: "Semua Esai Telah Dinilai!",
                description: "Anda sekarang dapat menghitung skor akhir untuk mahasiswa ini.",
                duration: 10000,
                action: (
                    <ToastAction altText="Finalisasi Skor" onClick={() => setIsFinalizeDialogOpen(true)}>
                        Finalisasi Skor
                    </ToastAction>
                ),
            });
        }
    }, [props.flash]);

    return (
        <>
            <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
                <Head title={`Koreksi: ${pengerjaan.user.email}`} />

                {/* ========================================================== */}
                {/* PERUBAHAN UTAMA: Header Gradasi seperti Dashboard */}
                {/* ========================================================== */}
                <div className="bg-gradient-to-r from-blue-600 to-indigo-700 text-white p-6 shadow-lg">
                    <div className="max-w-7xl mx-auto">
                        <div className="flex items-center justify-between">
                            <div className="flex items-center space-x-4">
                                <Link href={route('dosen.ujian.hasil.index', pengerjaan.ujian_id)}>
                                    <Button variant="ghost" size="sm" className="text-white hover:bg-white/20">
                                        <ArrowLeft className="h-4 w-4 mr-2" />
                                        Kembali ke Daftar
                                    </Button>
                                </Link>
                                <div>
                                    <h1 className="text-2xl font-bold">Koreksi Esai</h1>
                                    <p className="text-blue-100">{pengerjaan.ujian.judul_ujian}</p>
                                </div>
                            </div>
                            <div className="text-right">
                                <p className="text-blue-100">Mahasiswa</p>
                                <p className="font-semibold">{pengerjaan.user.name || pengerjaan.user.email}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Konten Utama */}
                <div className="max-w-4xl mx-auto p-6 space-y-6">
                    {pengerjaan.detail_jawaban.map((jawaban, index) => {
                        const form = forms.find(f => f.id === jawaban.id);
                        const isGraded = jawaban.skor_per_soal !== null;
                        return (
                            <Card key={jawaban.id} className="overflow-hidden shadow-md">
                                <CardHeader className="bg-gray-50 border-b">
                                    <CardTitle className="flex justify-between items-center text-lg">
                                        <span>Soal Esai #{index + 1}</span>
                                        {isGraded && <Badge variant="success"><CheckCircle className="h-4 w-4 mr-1" />Sudah Dinilai</Badge>}
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="p-6 space-y-4">
                                    <div className="p-4 bg-gray-100 rounded-md">
                                        <div className="font-semibold prose max-w-none" dangerouslySetInnerHTML={{ __html: jawaban.soal.pertanyaan }} />
                                    </div>
                                    <div className="p-4 bg-blue-50 border border-blue-200 rounded-md">
                                        <h3 className="font-semibold mb-2">Jawaban Mahasiswa:</h3>
                                        <p className="whitespace-pre-wrap font-mono text-sm">{jawaban.jawaban_user || <span className="italic text-gray-500">Tidak dijawab.</span>}</p>
                                    </div>
                                    <form onSubmit={form.handleSubmit} className="flex items-center gap-4 pt-4 border-t">
                                        <label className="font-medium">Beri Nilai:</label>
                                        <Input
                                            type="number"
                                            value={form.data.skor_per_soal}
                                            onChange={(e) => form.setData('skor_per_soal', e.target.value)}
                                            className="w-24"
                                            placeholder="0"
                                        />
                                        <Button type="submit" disabled={form.processing}>
                                            <Save className="h-4 w-4 mr-2" />
                                            {form.processing ? 'Menyimpan...' : 'Simpan Skor'}
                                        </Button>
                                        {form.errors.skor_per_soal && <p className="text-red-500 text-sm mt-1">{form.errors.skor_per_soal}</p>}
                                    </form>
                                </CardContent>
                            </Card>
                        );
                    })}

                    <div className="mt-8">
                        <Button onClick={() => setIsFinalizeDialogOpen(true)} disabled={processingFinalisasi} size="lg" className="w-full bg-green-600 hover:bg-green-700">
                            <CheckCircle className="h-5 w-5 mr-2" />
                            {processingFinalisasi ? 'Memproses...' : 'Selesai Koreksi & Hitung Skor Akhir'}
                        </Button>
                        <p className="text-center text-sm text-gray-500 mt-2">Tombol ini akan menghitung nilai akhir mahasiswa setelah semua skor esai disimpan.</p>
                    </div>
                </div>
            </div>

            {/* DITAMBAHKAN: Komponen AlertDialog untuk Konfirmasi */}
            <AlertDialog open={isFinalizeDialogOpen} onOpenChange={setIsFinalizeDialogOpen}>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Apakah Anda Yakin?</AlertDialogTitle>
                        <AlertDialogDescription>
                            Anda akan memfinalisasi skor untuk mahasiswa ini. Setelah skor akhir dihitung, status pengerjaan akan diubah menjadi 'selesai' dan tidak dapat dinilai ulang. Pastikan semua jawaban esai sudah diberi nilai dengan benar.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Batal</AlertDialogCancel>
                        <AlertDialogAction onClick={handleFinalisasi}>Lanjutkan Finalisasi</AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </>
    );
}