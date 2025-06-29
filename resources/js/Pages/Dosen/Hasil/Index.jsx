import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/Components/ui/card";
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Edit, CheckCircle, Clock } from 'lucide-react';

export default function Index({ auth, ujian, pengerjaanMenunggu, pengerjaanSelesai }) {
    return (
        <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
            <Head title={`Hasil Ujian: ${ujian.judul_ujian}`} />

            {/* ========================================================== */}
            {/* PERUBAHAN UTAMA: Header Gradasi seperti Dashboard */}
            {/* ========================================================== */}
            <div className="bg-gradient-to-r from-blue-600 to-indigo-700 text-white p-6 shadow-lg">
                <div className="max-w-7xl mx-auto">
                    <div className="flex items-center space-x-4">
                        <Link href={route('dosen.matakuliah.show', ujian.mata_kuliah_id)}>
                            <Button variant="ghost" size="sm" className="text-white hover:bg-white/20">
                                <ArrowLeft className="h-4 w-4 mr-2" />
                                Kembali ke Mata Kuliah
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-2xl font-bold">Hasil & Penilaian</h1>
                            <p className="text-blue-100">{ujian.judul_ujian}</p>
                        </div>
                    </div>
                </div>
            </div>

            {/* Konten Utama */}
            <div className="max-w-7xl mx-auto p-6 space-y-6">
                {/* Kartu untuk yang perlu dinilai */}
                <Card className="bg-white shadow-md">
                    <CardHeader>
                        <CardTitle>Menunggu Penilaian ({pengerjaanMenunggu.length})</CardTitle>
                        <CardDescription>Daftar pengerjaan yang mengandung esai dan perlu dinilai secara manual.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        {pengerjaanMenunggu.map((pengerjaan) => (
                            <div key={pengerjaan.id} className="p-3 border rounded-md mb-2 flex justify-between items-center hover:bg-gray-50 transition-colors">
                                <div className="font-medium">{pengerjaan.user.name || pengerjaan.user.email}</div>
                                <Link href={route('dosen.ujian.koreksi.show', pengerjaan.id)}>
                                    <Button size="sm" className="bg-orange-500 hover:bg-orange-600">
                                        <Edit className="h-4 w-4 mr-2" />
                                        Koreksi Jawaban
                                    </Button>
                                </Link>
                            </div>
                        ))}
                        {pengerjaanMenunggu.length === 0 && <p className="text-gray-500 text-center py-4">Tidak ada pengerjaan yang menunggu penilaian.</p>}
                    </CardContent>
                </Card>

                {/* Kartu untuk yang sudah selesai */}
                <Card className="bg-white shadow-md">
                     <CardHeader>
                        <CardTitle>Sudah Selesai Dinilai ({pengerjaanSelesai.length})</CardTitle>
                         <CardDescription>Daftar pengerjaan yang nilainya sudah final.</CardDescription>
                    </CardHeader>
                    <CardContent>
                         {pengerjaanSelesai.map((pengerjaan) => (
                            <div key={pengerjaan.id} className="p-3 border rounded-md mb-2 flex justify-between items-center">
                                <span className="font-medium">{pengerjaan.user.name || pengerjaan.user.email}</span>
                                <Badge variant="success" className="text-base">
                                    {`Skor: ${pengerjaan.skor_total}`}
                                </Badge>
                            </div>
                        ))}
                         {pengerjaanSelesai.length === 0 && <p className="text-gray-500 text-center py-4">Belum ada pengerjaan yang selesai dinilai.</p>}
                    </CardContent>
                </Card>
            </div>
        </div>
    );
}