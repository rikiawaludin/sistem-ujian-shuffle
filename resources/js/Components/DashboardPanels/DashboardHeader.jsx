import React from 'react';
import { Card, Typography, Tooltip } from "@material-tailwind/react";
import { router } from '@inertiajs/react';
import { LogOut } from 'lucide-react';
import { AcademicCapIcon, BookOpenIcon, BuildingLibraryIcon, ArrowLeftOnRectangleIcon } from '@heroicons/react/24/solid'; // Ganti CalendarDaysIcon dengan BuildingLibraryIcon untuk Jurusan

const StatItem = ({ icon, value, label, colorClass }) => (
    <Card className="bg-white/10 border-white/20 backdrop-blur-sm">
        <div className="p-4 text-center">
            {React.cloneElement(icon, { className: `w-6 h-6 mx-auto mb-2 ${colorClass}` })}
            <div className="text-2xl font-bold text-white">{value}</div>
            <div className="text-sm text-white/80 capitalize">{label}</div> {/* Tambahkan capitalize */}
        </div>
    </Card>
);

export default function DashboardHeader({ auth, userName, totalMataKuliah, totalSks, namaJurusan }) {

    // const handleLogout = () => router.get(route('logout')); 

    // DITAMBAHKAN: Logika untuk memformat nama jurusan
    let displayJurusan = 'N/A';
    if (namaJurusan && namaJurusan.includes(' - ')) {
        // Ambil bagian setelah " - ", ubah ke title case (misal: "Teknik Informatika")
        displayJurusan = namaJurusan.split(' - ')[1]
            .toLowerCase()
            .split(' ')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ');
    } else if (namaJurusan) {
        displayJurusan = namaJurusan;
    }

    return (
        <div className="mb-8">
            {/* DIUBAH: Pilihan gradien baru (Netral & Canggih) diterapkan */}
            <div className="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl p-6 sm:p-8 text-white relative overflow-hidden shadow-lg">
                <div className="relative z-10">

                    <div className="flex items-center justify-between gap-4 mb-6">

                        {/* Bagian Kiri: Logo dan Sapaan */}
                        <div className="flex items-center gap-4">
                            {/* DIUBAH: Ikon diganti dengan Logo */}
                            <img src="/images/stmik.png" alt="Logo STMIK Bandung" className="h-10 w-auto" />
                            <div>
                                <Typography variant="h4" className="font-bold text-white">
                                    Selamat Datang, {userName}!
                                </Typography>
                                <Typography color="white" className="opacity-80">
                                    Portal Ujian dan Mata Kuliah Anda.
                                </Typography>
                            </div>
                        </div>

                        {/* Bagian Kanan: Menu Pengguna (Logout/Profil) */}
                        <div className="flex-shrink-0">
                            {auth.user ? (
                                <Tooltip content="Log Out" placement="bottom">
                                    {/* DIUBAH: <button> diganti dengan tag <a> biasa */}
                                    <a
                                        href={route('logout')} // Menggunakan href untuk navigasi langsung
                                        className="p-2 block rounded-full text-white/80 bg-white/10 hover:bg-white/20 transition-colors focus:outline-none"
                                    >
                                        <LogOut className="h-6 w-6" />
                                    </a>
                                </Tooltip>
                            ) : (
                                // Tombol login ini juga bisa diubah menjadi tag <a> jika rutenya adalah halaman login standar
                                <a href={route('login')}>
                                    <Button color="white" variant="outlined">Log In</Button>
                                </a>
                            )}
                        </div>
                    </div>

                    <div className="grid grid-cols-2 md:grid-cols-3 gap-4 mt-6">
                        <StatItem
                            icon={<BookOpenIcon />}
                            value={totalMataKuliah}
                            label="Mata Kuliah"
                            colorClass="text-purple-200"
                        />
                        <StatItem
                            icon={<AcademicCapIcon />}
                            value={totalSks}
                            label="Total SKS"
                            colorClass="text-blue-200"
                        />
                        <StatItem
                            // Ikon diganti agar lebih relevan dengan "Jurusan"
                            icon={<BuildingLibraryIcon />}
                            // DIUBAH: Gunakan variabel yang sudah diformat
                            value={displayJurusan}
                            label="Jurusan"
                            colorClass="text-green-200"
                        />
                    </div>
                </div>
            </div>
        </div>
    );
}