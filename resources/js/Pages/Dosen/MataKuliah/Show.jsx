import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import React, { useState } from 'react';
import { Head, Link, usePage, router } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/Components/ui/tabs";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from "@/Components/ui/dialog"
import { ArrowLeft, BookOpen, FileText, BarChart3, Users, Plus, Edit, Trash2 } from 'lucide-react';
import { Badge } from "@/Components/ui/badge";
import { Cog6ToothIcon } from '@heroicons/react/24/solid';
import UjianDetailFormDialog from '@/Pages/Dosen/Partials/UjianDetailFormDialog';
import UjianAturanDialog from '@/Pages/Dosen/Partials/UjianAturanDialog';
import UjianCreateDialog from '@/Pages/Dosen/Partials/UjianCreateDialog'; 

// Komponen Form Soal yang akan kita buat
import BankSoalForm from '@/Pages/Dosen/Partials/BankSoalForm';

const ListItem = ({ children }) => (
    <div className="p-4 border rounded-lg hover:bg-gray-50 transition-colors">
        {children}
    </div>
);

export default function Show() {
    const { course, soalSummary = {}, ujianSummary = {}, mataKuliahOptions, bankSoalSummaryByDifficulty = {} } = usePage().props;

    // State untuk mengontrol modal form soal
    const [isFormOpen, setIsFormOpen] = useState(false);
    const [editingSoal, setEditingSoal] = useState(null); // untuk menyimpan data soal yang akan diedit

    const handleAddSoal = () => {
        setEditingSoal(null); // Pastikan mode edit mati
        setIsFormOpen(true);
    };

    const handleEditSoal = (soal) => {
        setEditingSoal(soal); // Set data soal untuk diedit
        setIsFormOpen(true);
    };

    const handleDeleteSoal = (soal) => {
        if (confirm(`Apakah Anda yakin ingin menghapus soal: "${soal.pertanyaan.substring(0, 50)}..."?`)) {
            router.delete(route('dosen.bank-soal.destroy', soal.id), {
                preserveScroll: true, // Agar halaman tidak scroll ke atas setelah aksi
            });
        }
    };

    const difficultyStyles = {
        mudah: 'bg-green-100 text-green-800 border-green-200',
        sedang: 'bg-yellow-100 text-yellow-800 border-yellow-200',
        sulit: 'bg-red-100 text-red-800 border-red-200',
    };

    // TAMBAHKAN STATE BARU UNTUK MODAL UJIAN
    const [isCreateUjianOpen, setIsCreateUjianOpen] = useState(false); // State untuk wizard baru
    const [isEditUjianOpen, setIsEditUjianOpen] = useState(false); // State untuk modal edit detail
    const [isAturanFormOpen, setIsAturanFormOpen] = useState(false);
    const [selectedUjian, setSelectedUjian] = useState(null);

    // HANDLER BARU UNTUK UJIAN
    // Handler untuk membuka wizard create
    const handleAddUjian = () => {
        setSelectedUjian(null);
        setIsCreateUjianOpen(true);
    };

    const handleEditUjian = (ujian) => {
        setSelectedUjian(ujian);
        setIsEditUjianOpen(true);
    };

    const handleAturSoal = (ujian) => {
        setSelectedUjian(ujian);
        setIsAturanFormOpen(true);
    };

    // Periksa apakah user prop ada sebelum mencoba mengaksesnya
    const { auth } = usePage().props;
    const user = auth ? auth.user : null;

    return (
        <div>
            <Head title={`Kelola: ${course.nama}`} />

            <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 p-6">
                {/* Header Halaman */}
                <div className="bg-gradient-to-r from-blue-600 to-indigo-700 text-white p-6 shadow-lg rounded-xl mb-8">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center space-x-4">
                            <Link href={route('dosen.dashboard')}>
                                <Button variant="ghost" size="sm" className="text-white hover:bg-white/20">
                                    <ArrowLeft className="h-4 w-4 mr-2" />
                                    Kembali
                                </Button>
                            </Link>
                            <div>
                                <h1 className="text-2xl font-bold">{course.nama}</h1>
                                <p className="text-blue-100">{course.kode}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Konten Utama dengan Tabs */}
                <Tabs defaultValue="overview" className="w-full">
                    {/* ========================================================== */}
                    {/* PERUBAHAN STYLE UTAMA DI SINI */}
                    {/* ========================================================== */}
                    <TabsList className="grid w-full grid-cols-2 md:grid-cols-4 h-auto bg-white/80 backdrop-blur-sm shadow-md mb-6 p-1 rounded-lg">
                        <TabsTrigger value="overview" className="flex items-center gap-2 py-2 data-[state=active]:bg-white data-[state=active]:text-blue-700 data-[state=active]:shadow-md rounded-md">
                            <BarChart3 className="h-4 w-4" />
                            <span>Overview</span>
                        </TabsTrigger>
                        <TabsTrigger value="questions" className="flex items-center gap-2 py-2 data-[state=active]:bg-white data-[state=active]:text-blue-700 data-[state=active]:shadow-md rounded-md">
                            <BookOpen className="h-4 w-4" />
                            <span>Bank Soal</span>
                        </TabsTrigger>
                        <TabsTrigger value="exams" className="flex items-center gap-2 py-2 data-[state=active]:bg-white data-[state=active]:text-blue-700 data-[state=active]:shadow-md rounded-md">
                            <FileText className="h-4 w-4" />
                            <span>Ujian</span>
                        </TabsTrigger>
                        <TabsTrigger value="results" className="flex items-center gap-2 py-2 data-[state=active]:bg-white data-[state=active]:text-blue-700 data-[state=active]:shadow-md rounded-md">
                            <Users className="h-4 w-4" />
                            <span>Hasil & Penilaian</span>
                        </TabsTrigger>
                    </TabsList>

                    {/* (Konten untuk TabsContent tidak ada perubahan) */}
                    <TabsContent value="overview" className="space-y-6">
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                            {/* KARTU BANK SOAL */}
                            <Card className="bg-white shadow-md">
                                <CardHeader className="pb-3">
                                    <CardTitle className="text-lg flex items-center">
                                        <BookOpen className="h-5 w-5 mr-2 text-blue-600" />
                                        Bank Soal
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="text-3xl font-bold text-blue-600 mb-2">
                                        {course.soal.length}
                                    </div>
                                    <p className="text-sm text-gray-600">Total Soal Tersedia</p>
                                    <div className="mt-4 space-y-2">
                                        <div className="flex justify-between text-sm">
                                            <span>Pilihan Ganda</span>
                                            <span className="font-medium">
                                                {soalSummary['pilihan_ganda'] || 0}
                                            </span>
                                        </div>
                                        <div className="flex justify-between text-sm">
                                            <span>Benar Salah</span>
                                            <span className="font-medium">
                                                {soalSummary['benar_salah'] || 0}
                                            </span>
                                        </div>
                                        <div className="flex justify-between text-sm">
                                            <span>Esai</span>
                                            <span className="font-medium">
                                                {soalSummary['esai'] || 0}
                                            </span>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* KARTU UJIAN */}
                            <Card className="bg-white shadow-md">
                                <CardHeader className="pb-3">
                                    <CardTitle className="text-lg flex items-center">
                                        <FileText className="h-5 w-5 mr-2 text-green-600" />
                                        Ujian
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="text-3xl font-bold text-green-600 mb-2">
                                        {course.ujian.length}
                                    </div>
                                    <p className="text-sm text-gray-600">Total Ujian</p>
                                    <div className="mt-4 space-y-2">
                                        <div className="flex justify-between text-sm">
                                            <span>Aktif</span>
                                            <span className="font-medium text-green-600">
                                                {ujianSummary['published'] || 0}
                                            </span>
                                        </div>
                                        <div className="flex justify-between text-sm">
                                            <span>Terjadwal</span>
                                            <span className="font-medium text-blue-600">
                                                {ujianSummary['scheduled'] || 0}
                                            </span>
                                        </div>
                                        <div className="flex justify-between text-sm">
                                            <span>Selesai/Arsip</span>
                                            <span className="font-medium text-gray-600">
                                                {ujianSummary['archived'] || 0}
                                            </span>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* KARTU MAHASISWA */}
                            <Card className="bg-white shadow-md">
                                <CardHeader className="pb-3">
                                    <CardTitle className="text-lg flex items-center">
                                        <Users className="h-5 w-5 mr-2 text-purple-600" />
                                        Mahasiswa
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="text-3xl font-bold text-purple-600 mb-2">
                                        {course.students_count}
                                    </div>
                                    <p className="text-sm text-gray-600">Terdaftar</p>
                                    <div className="mt-4">
                                        <div className="flex justify-between text-sm mb-2">
                                            <span>Kehadiran Rata-rata</span>
                                            <span className="font-medium">N/A</span>
                                        </div>
                                        {/* Placeholder untuk progress bar */}
                                        <div className="w-full bg-gray-200 rounded-full h-2">
                                            <div className="bg-purple-600 h-2 rounded-full" style={{ width: '0%' }}></div>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        {/* Kartu Deskripsi Mata Kuliah */}
                        <Card className="bg-white shadow-md">
                            <CardHeader>
                                <CardTitle>Deskripsi Mata Kuliah</CardTitle>
                            </CardHeader>
                            <CardContent>
                                {/* Ganti dengan deskripsi dari database jika ada */}
                                <p className="text-gray-700">Mata kuliah ini membahas konsep-konsep utama dalam bidang terkait...</p>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <TabsContent value="questions" className="space-y-6">
                        <div className="flex justify-between items-center">
                            <div>
                                <h3 className="text-xl font-bold text-gray-800">Bank Soal Mata Kuliah</h3>
                                <p className="text-sm text-muted-foreground">Kelola semua soal untuk mata kuliah ini.</p>
                            </div>
                            <Button onClick={handleAddSoal} className="bg-blue-600 hover:bg-blue-700">
                                <Plus className="h-4 w-4 mr-2" />
                                Tambah Soal
                            </Button>
                        </div>

                        <div className="space-y-4">
                            {course.soal.map((soal) => (
                                <Card key={soal.id} className="bg-white shadow-md hover:shadow-lg transition-shadow">
                                    <CardContent className="p-4">
                                        <div className="flex items-start justify-between">
                                            <div className="flex-1 mr-4">
                                                <div className="flex items-center space-x-3 mb-2">
                                                    <Badge variant="outline" className="text-xs capitalize border-gray-300">
                                                        {soal.tipe_soal.replace('_', ' ')}
                                                    </Badge>
                                                    <Badge className={`text-xs capitalize ${difficultyStyles[soal.level_kesulitan] || 'bg-gray-100 text-gray-800'}`}>
                                                        {soal.level_kesulitan}
                                                    </Badge>
                                                </div>
                                                <div
                                                    className="font-medium text-gray-800 line-clamp-2"
                                                    dangerouslySetInnerHTML={{ __html: soal.pertanyaan }}
                                                />
                                            </div>
                                            <div className="flex space-x-2">
                                                <Button variant="outline" size="sm" onClick={() => handleEditSoal(soal)}>
                                                    <Edit className="h-4 w-4" />
                                                </Button>
                                                <Button variant="outline" size="sm" className="text-red-600 hover:text-red-700" onClick={() => handleDeleteSoal(soal)}>
                                                    <Trash2 className="h-4 w-4" />
                                                </Button>
                                            </div>
                                        </div>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                    </TabsContent>

                    <TabsContent value="exams" className="space-y-6">
                        <div className="flex justify-between items-center">
                            <div>
                                <h3 className="text-xl font-bold text-gray-800">Manajemen Ujian</h3>
                                <p className="text-sm text-muted-foreground">Kelola semua ujian untuk mata kuliah ini.</p>
                            </div>
                            <Button onClick={handleAddUjian} className="bg-green-600 hover:bg-green-700">
                                <Plus className="h-4 w-4 mr-2" />
                                Buat Ujian Baru
                            </Button>
                        </div>

                        <div className="space-y-4">
                            {course.ujian.map((ujian) => (
                                <Card key={ujian.id} className="bg-white shadow-md">
                                    <CardContent className="p-4">
                                        <div className="flex items-center justify-between">
                                            <div className="flex-1 mr-4">
                                                <div className="flex items-center gap-3 mb-1">
                                                    <h4 className="font-semibold text-gray-800">{ujian.judul_ujian}</h4>
                                                    <Badge variant="secondary" className="capitalize">{ujian.status_publikasi}</Badge>
                                                </div>
                                                <p className="text-sm text-gray-500">
                                                    Durasi: {ujian.durasi} menit | KKM: {ujian.kkm || 'N/A'}
                                                </p>
                                            </div>
                                            <div className="flex items-center space-x-2">
                                                <Button variant="outline" size="sm" className="flex items-center gap-2" onClick={() => handleAturSoal(ujian)}>
                                                    <Cog6ToothIcon className="h-4 w-4" /> Atur Soal
                                                </Button>
                                                <Button variant="outline" size="sm" onClick={() => handleEditUjian(ujian)}>
                                                    <Edit className="h-4 w-4" />
                                                </Button>
                                                {/* Tombol Hapus bisa ditambahkan di sini jika perlu */}
                                            </div>
                                        </div>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                    </TabsContent>

                    <TabsContent value="results">
                        <Card>
                            <CardHeader>
                                <CardTitle>Hasil & Penilaian</CardTitle>
                                <CardDescription>Hasil pengerjaan ujian oleh mahasiswa.</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <p className="text-center text-gray-500 py-8">Fitur untuk menampilkan hasil akan diimplementasikan pada tahap selanjutnya.</p>
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>

                {/* MODAL UNTUK FORM TAMBAH/EDIT SOAL */}
                <Dialog open={isFormOpen} onOpenChange={setIsFormOpen}>
                    <DialogContent className="sm:max-w-[80vw] max-h-[90vh] overflow-y-auto">
                        <DialogHeader>
                            <DialogTitle>{editingSoal ? 'Edit Soal' : 'Buat Soal Baru'}</DialogTitle>
                            <DialogDescription>
                                {editingSoal ? 'Perbarui detail soal di bawah ini.' : `Buat soal baru untuk mata kuliah ${course.nama}.`}
                            </DialogDescription>
                        </DialogHeader>
                        {/* Render komponen form di dalam modal */}
                        <BankSoalForm
                            // Kirim props yang dibutuhkan oleh form
                            soal={editingSoal}
                            mataKuliahOptions={mataKuliahOptions}
                            // Tambahkan prop untuk menutup modal setelah berhasil
                            onSuccess={() => setIsFormOpen(false)}
                            // Set mata kuliah default sesuai halaman saat ini
                            defaultMataKuliahId={course.id}
                        />
                    </DialogContent>
                </Dialog>

                <UjianCreateDialog
                    open={isCreateUjianOpen}
                    onOpenChange={setIsCreateUjianOpen}
                    mataKuliahId={course.id}
                    bankSoalSummary={bankSoalSummaryByDifficulty} // Kirim summary soal
                    onSuccess={() => setIsCreateUjianOpen(false)}
                />

                {/* TAMBAHKAN MODAL-MODAL BARU DI SINI */}
                {/* Modal untuk Form Detail Ujian */}
                <UjianDetailFormDialog
                    open={isEditUjianOpen}
                    onOpenChange={setIsEditUjianOpen}
                    ujian={selectedUjian}
                    mataKuliahId={course.id}
                    onSuccess={() => setIsUjianFormOpen(false)}
                />

                {/* Modal untuk Form Aturan Soal */}
                {selectedUjian && (
                    <UjianAturanDialog
                        open={isAturanFormOpen}
                        onOpenChange={setIsAturanFormOpen}
                        ujian={selectedUjian}
                        onSuccess={() => setIsAturanFormOpen(false)}
                    />
                )}

            </div>
        </div>
    );
}