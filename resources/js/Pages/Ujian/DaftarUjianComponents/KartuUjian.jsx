import React from 'react';
import { Typography, Button, CardBody, Card, CardHeader, CardContent, Chip } from "@material-tailwind/react"; // Gunakan CardContent jika ada di versi Anda, jika tidak bisa pakai CardBody
import { router } from '@inertiajs/react';
import { ClockIcon, ClipboardDocumentListIcon, CalendarDaysIcon, PlayCircleIcon, EyeIcon, ExclamationTriangleIcon, BookOpenIcon, ListBulletIcon } from "@heroicons/react/24/solid";

// Fungsi helper untuk mendapatkan informasi berdasarkan status ujian
function getStatusInfo(ujian) {
    switch (ujian.status) {
        case "Belum Dikerjakan":
            return {
                chip: { label: "Akan Datang", color: "light-blue" },
                button: { text: "Mulai Ujian", color: "blue", icon: <PlayCircleIcon className="h-5 w-5" />, disabled: false, variant: "gradient" }
            };
        case "Sedang Dikerjakan":
            return {
                chip: { label: "Berlangsung", color: "amber" },
                button: { text: "Lanjutkan Ujian", color: "amber", icon: <PlayCircleIcon className="h-5 w-5" />, disabled: false, variant: "gradient" }
            };
        case "Selesai":
            // PERIKSA 'visibilitas_hasil' DI SINI
            if (ujian.visibilitas_hasil) {
                return {
                    chip: { label: "Selesai", color: "green" },
                    button: { text: "Lihat Hasil", color: "green", icon: <EyeIcon className="h-5 w-5" />, disabled: false, variant: "outlined" }
                };
            } else {
                return {
                    chip: { label: "Selesai", color: "green" },
                    button: { text: "Hasil Ditutup", color: "blue-gray", icon: <EyeIcon className="h-5 w-5" />, disabled: true, variant: "outlined" }
                };
            }
        // TAMBAHKAN CASE BARU
        case "Selesai (Hasil Ditutup)":
            return {
                chip: { label: "Selesai", color: "blue-gray" },
                button: { text: "Hasil Ditutup", color: "blue-gray", icon: <EyeIcon className="h-5 w-5" />, disabled: true, variant: "outlined" }
            };
        default: // Waktu Habis, Tidak Tersedia
            return {
                chip: { label: ujian.status, color: "blue-gray" },
                button: { text: "Tidak Tersedia", color: "blue-gray", icon: <ExclamationTriangleIcon className="h-5 w-5" />, disabled: true, variant: "outlined" }
            };
    }
}

// Fungsi helper untuk ikon jenis ujian
const getExamTypeIcon = (type) => {
    // Di controller, jenis_ujian Anda 'kuis', 'uts', 'uas'. Kita sesuaikan di sini.
    switch (type) {
        case 'kuis': return <FileText className="w-5 h-5" />;
        case 'uts': return <BookOpen className="w-5 h-5" />;
        case 'uas': return <ListBulletIcon className="w-5 h-5" />;
        default: return <FileText className="w-5 h-5" />;
    }
};

export default function KartuUjian({ ujian }) {
    const statusInfo = getStatusInfo(ujian);

    const handleAksiUjian = () => {
        if (ujian.status === "Belum Dikerjakan" || ujian.status === "Sedang Dikerjakan") {
            router.get(route('ujian.kerjakan', { id_ujian: ujian.id }));
        } else if (ujian.status === "Selesai" && ujian.id_pengerjaan_terakhir) {
            router.get(route('ujian.hasil.detail', { id_attempt: ujian.id_pengerjaan_terakhir }));
        }
    };

    return (
        <Card className="w-full hover:shadow-md transition-shadow duration-200 border-l-4 border-blue-500">
            {/* CardHeader dan CardBody sekarang ada di Material Tailwind, kita bisa gunakan */}
            <CardBody className="p-5">
                <div className="flex items-start justify-between mb-4">
                    <div className="flex items-center space-x-3">
                        <div className='p-2 rounded-lg bg-blue-50 text-blue-600'>
                            {getExamTypeIcon(ujian.jenis_ujian)}
                        </div>
                        <div>
                            <Typography variant="h6" color="blue-gray" className="font-semibold text-lg">{ujian.nama}</Typography>
                            {/* Di controller, Anda tidak mengirim nama mata kuliah per ujian. Jika perlu, tambahkan di ListUjianController */}
                            {/* <Typography className="text-sm text-gray-500">{ujian.namaMataKuliah}</Typography> */}
                        </div>
                    </div>
                    <Chip value={statusInfo.chip.label} color={statusInfo.chip.color} size="sm" className="capitalize" />
                </div>

                <div
                    className="mb-6 prose max-w-none text-blue-gray-800"
                    dangerouslySetInnerHTML={{ __html: ujian.deskripsi }}
                />

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm text-gray-700 mb-5 border-t pt-4">
                    <div className="flex items-center space-x-2">
                        <CalendarDaysIcon className="w-4 h-4 text-gray-500" />
                        <span>Mulai Ujian: {ujian.waktuMulai}</span>
                    </div>
                    <div className="flex items-center space-x-2">
                        <CalendarDaysIcon className="w-4 h-4 text-gray-500" />
                        <span>Batas Waktu: {ujian.batasWaktuPengerjaan}</span>
                    </div>
                    <div className="flex items-center space-x-2">
                        <ClockIcon className="w-4 h-4 text-gray-500" />
                        <span>Durasi: {ujian.durasi}</span>
                    </div>
                    <div className="flex items-center space-x-2">
                        <ClipboardDocumentListIcon className="w-4 h-4 text-gray-500" />
                        <span>{ujian.jumlahSoal} Soal</span>
                    </div>
                </div>

                <div className="flex flex-col items-center gap-4 sm:flex-row sm:justify-between">
                    {/* ^-- Diubah: `items-center` untuk menengahkan di mode mobile (flex-col) */}
                    {/* `sm:justify-between` untuk merentangkan di layar besar (flex-row) */}

                    {(ujian.status === "Selesai" || ujian.status === "Selesai (Hasil Ditutup)") && ujian.skor !== null ? (
                        <div className="text-center sm:text-left">
                            <Typography className="text-xs text-gray-500">SKOR / KKM</Typography>
                            <div className="flex items-baseline justify-center sm:justify-start space-x-1">
                                <Typography variant="h5" color={ujian.skor >= ujian.kkm ? "green" : "red"} className="font-bold">
                                    {ujian.skor}
                                </Typography>
                                <Typography color="blue-gray" className="font-bold text-lg">
                                    / {ujian.kkm}
                                </Typography>
                            </div>
                        </div>
                    ) : (
                        // Tetap berikan div kosong agar `justify-between` berfungsi di layar besar
                        <div className="hidden sm:block"></div>
                    )}

                    <Button
                        onClick={handleAksiUjian}
                        disabled={statusInfo.button.disabled}
                        variant={statusInfo.button.variant}
                        color={statusInfo.button.color}
                        // Lebar tombol diatur agar tidak terlalu kecil di mobile dan pas di desktop
                        className="flex items-center justify-center gap-2 w-full max-w-xs sm:w-auto sm:max-w-none"
                    >
                        {statusInfo.button.icon}
                        {statusInfo.button.text}
                    </Button>
                </div>
            </CardBody>
        </Card>
    );
}

// Tambahkan definisi untuk FileText jika belum ada, atau impor dari lucide-react jika sudah terinstall
const FileText = (props) => (
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" {...props}>
        <path fillRule="evenodd" d="M5.625 1.5c-1.036 0-1.875.84-1.875 1.875v17.25c0 1.035.84 1.875 1.875 1.875h12.75c1.035 0 1.875-.84 1.875-1.875V12.75A3.75 3.75 0 0016.5 9h-1.875a.375.375 0 01-.375-.375V6.75A3.75 3.75 0 009 3H5.625zM12.75 3.188A2.25 2.25 0 0010.5 5.438v1.875a1.875 1.875 0 001.875 1.875h1.875V3.188z" clipRule="evenodd" />
        <path d="M14.25 11.25a.75.75 0 01.75-.75h1.5a.75.75 0 010 1.5h-1.5a.75.75 0 01-.75-.75zM14.25 15a.75.75 0 01.75-.75h1.5a.75.75 0 010 1.5h-1.5a.75.75 0 01-.75-.75zM14.25 18.75a.75.75 0 01.75-.75h1.5a.75.75 0 010 1.5h-1.5a.75.75 0 01-.75-.75z" />
    </svg>
);