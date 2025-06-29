// resources/js/Components/DashboardPanels/MataKuliahCard.jsx

import React from 'react';
import { Card, Typography, Button, Tooltip, Avatar } from "@material-tailwind/react";
import { Link } from '@inertiajs/react';
// DIUBAH: Avatar tidak lagi digunakan, namun kita tetap mengimpor UserIcon
import { UserIcon, CalendarIcon, DocumentTextIcon } from '@heroicons/react/24/solid';
import defaultMatkulImage from '/public/images/placeholder-matakuliah.jpg';
// defaultAvatar tidak lagi digunakan karena kita menggantinya dengan ikon
// import defaultAvatar from '/public/images/default-avatar.png';

export default function MataKuliahCard({ mataKuliah }) {
    const hasLocalData = !!mataKuliah.id_matakuliah_lokal;

    return (
        <Card className="group overflow-hidden hover:shadow-xl transition-all duration-300 hover:-translate-y-1.5 cursor-pointer border-0 shadow-lg h-full flex flex-col">
            {/* Bagian Header Card (Tidak ada perubahan) */}
            <div className="relative h-48 overflow-hidden">
                <img
                    src={defaultMatkulImage}
                    alt={mataKuliah.nama}
                    className="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"
                />
                <div className="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
                <div className="absolute top-3 right-3 bg-white/90 text-gray-800 text-xs font-semibold px-2 py-1 rounded-full backdrop-blur-sm">
                    {mataKuliah.sks} SKS
                </div>
                <div className="absolute bottom-4 left-4 right-4">
                    <Typography
                        variant="h5"
                        color="white"
                        className="font-bold mb-0.5 transition-colors group-hover:text-blue-200"
                    >
                        {mataKuliah.nama}
                    </Typography>
                    <Typography color="white" className="text-sm opacity-80 font-mono">
                        {mataKuliah.kode_mk}
                    </Typography>
                </div>
            </div>

            {/* ======== DI SINI LETAK PERUBAHANNYA ======== */}
            <div className="p-5 flex-grow">
                {/* DIUBAH: Container utama untuk info semester dan dosen digabung menjadi satu */}
                <div className="flex items-center justify-between mb-4 text-sm text-gray-600">
                    {/* Info Semester */}
                    <div className="flex items-center gap-1.5">
                        <CalendarIcon className="w-4 h-4" />
                        <span>{mataKuliah.semester ? `Semester ${mataKuliah.semester}` : 'Umum'}</span>
                    </div>

                    {/* DIUBAH: Info Dosen sekarang menggunakan UserIcon, bukan Avatar */}
                    <div className="flex items-center gap-1.5">
                        <UserIcon className="w-4 h-4" />
                        <span>{mataKuliah.dosen?.nama || 'Dosen Belum Ditentukan'}</span>
                    </div>
                </div>
            </div>
            {/* ======== AKHIR DARI PERUBAHAN ======== */}


            <div className="p-5 pt-0">
                <Link href={hasLocalData ? route('ujian.daftarPerMataKuliah', { id_mata_kuliah: mataKuliah.id_matakuliah_lokal }) : '#'}>
                    {/* DIUBAH: Properti `variant` dan `color` dihapus, diganti dengan `className` */}
                    <Button
                        fullWidth
                        disabled={!hasLocalData}
                        className="flex items-center justify-center bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium py-2.5 disabled:opacity-60 disabled:cursor-not-allowed"
                    >
                        {/* DITAMBAHKAN: Ikon untuk tombol */}
                        <DocumentTextIcon className="h-5 w-5 mr-2" />
                        <span>{hasLocalData ? 'Lihat Ujian' : 'Tidak Tersedia'}</span>
                    </Button>
                </Link>
            </div>
        </Card>
    );
}