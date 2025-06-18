import React from 'react';
import { Link, usePage } from '@inertiajs/react';
import { Typography, Button } from "@material-tailwind/react";
import { PresentationChartBarIcon, BookOpenIcon, DocumentTextIcon, ArrowRightOnRectangleIcon, XMarkIcon, ChevronDoubleLeftIcon } from "@heroicons/react/24/solid";

// Definisikan rute khusus untuk navigasi dosen
const dosenRoutes = [
    {
        name: "Dashboard",
        pathName: 'dosen.dashboard', // <-- Rute baru
        pathPattern: 'dosen.dashboard',
        icon: <PresentationChartBarIcon className="w-5 h-5" />
    },
    {
        name: "Bank Soal",
        pathName: 'dosen.bank-soal.index', // Pastikan ini sesuai dengan nama baru
        pathPattern: 'dosen.bank-soal.*',
        icon: <BookOpenIcon className="w-5 h-5" />
    },
    {
        name: "Manajemen Ujian",
        pathName: 'dosen.ujian.index', // Rute baru untuk daftar ujian
        pathPattern: 'dosen.ujian.*',
        icon: <DocumentTextIcon className="w-5 h-5" /> // Contoh ikon baru
    }
];

export default function DosenSidenav({ isCollapsed, isMobileOpen, toggleCollapse, onCloseMobile }) {

    const sidebarWidth = isCollapsed ? 'w-20' : 'w-64';

    // Logika untuk menampilkan sidebar di mobile
    // Sembunyikan di mobile secara default (-translate-x-full), tampilkan jika isMobileOpen
    // Selalu tampil di desktop (md:translate-x-0)
    const mobileTransform = isMobileOpen ? 'translate-x-0' : '-translate-x-full';

    return (
        <aside
            className={`bg-white shadow-xl h-screen fixed top-0 left-0 z-40 p-4 transition-all duration-300 ease-in-out ${sidebarWidth} ${mobileTransform} md:translate-x-0`}
        >
            {/* Header Sidenav */}
            <div className={`flex items-center mb-8 ${isCollapsed ? 'justify-center' : 'justify-between'}`}>
                {/* Logo dan Judul Sidenav, sekarang digabung dan dinamis */}
                <Link href={route('dosen.dashboard')} className="flex items-center gap-3 overflow-hidden">
                    <img
                        src="/images/stmik.png" // Mengambil path dari referensi AppNavbar Anda
                        alt="Logo STMIK"
                        className="h-8 w-auto flex-shrink-0" // flex-shrink-0 penting agar logo tidak ikut menyusut
                    />
                    <Typography
                        variant="h5"
                        color="blue-gray"
                        className={`whitespace-nowrap transition-all duration-200 ${isCollapsed ? 'opacity-0 w-0' : 'opacity-100 w-auto'}`}
                    >
                        STMIK Bandung
                    </Typography>
                </Link>

                {/* Tombol Close untuk Mobile, disembunyikan jika sidebar diciutkan */}
                {!isCollapsed && (
                    <button onClick={onCloseMobile} className="text-blue-gray-700 md:hidden">
                        <XMarkIcon className="w-6 h-6" />
                    </button>
                )}
            </div>

            {/* Daftar Navigasi */}
            <div className="flex flex-col gap-2">
                {dosenRoutes.map(({ name, pathName, pathPattern, icon }) => {
                    const isActive = route().current(pathPattern);
                    return (
                        <Link key={name} href={route(pathName)} title={name}>
                            <Button
                                variant={isActive ? "gradient" : "text"}
                                color={isActive ? "blue" : "blue-gray"}
                                // Pusatkan item jika diciutkan
                                className={`flex items-center gap-4 px-4 capitalize transition-all duration-200 ${isCollapsed ? 'justify-center' : ''}`}
                                fullWidth
                            >
                                {/* Clone ikon untuk menambahkan margin kanan jika tidak diciutkan */}
                                {React.cloneElement(icon, {
                                    className: `${icon.props.className} ${isCollapsed ? '' : 'mr-3'}`
                                })}

                                {/* Teks navigasi, sembunyikan jika diciutkan */}
                                <Typography color="inherit" className={`font-medium capitalize text-sm whitespace-nowrap transition-opacity  ${isCollapsed ? 'hidden' : 'block'}`}>
                                    {name}
                                </Typography>
                            </Button>
                        </Link>
                    );
                })}
            </div>

            {/* Tombol untuk menciutkan sidebar (hanya di desktop) */}
            <div className="hidden md:block absolute bottom-4 left-0 w-full px-4">
                <Button
                    variant="text"
                    color="blue-gray"
                    onClick={toggleCollapse}
                    className="flex items-center gap-4 px-4 capitalize w-full"
                >
                    <ChevronDoubleLeftIcon className={`w-5 h-5 transition-transform duration-300 ${isCollapsed ? 'rotate-180' : ''}`} />
                    <Typography color="inherit" className={`font-medium capitalize text-sm whitespace-nowrap transition-opacity ${isCollapsed ? 'hidden' : 'block'}`}>
                        Ciutkan
                    </Typography>
                </Button>
            </div>
        </aside>
    );
}