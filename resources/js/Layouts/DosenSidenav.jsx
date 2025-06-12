import React from 'react';
import { Link, usePage } from '@inertiajs/react';
import { Typography, Button } from "@material-tailwind/react";
import { PresentationChartBarIcon, BookOpenIcon, DocumentTextIcon } from "@heroicons/react/24/solid";

// Definisikan rute khusus untuk navigasi dosen
const dosenRoutes = [
    {
        name: "Dashboard",
        pathName: 'dosen.dashboard', // <-- Rute baru
        pathPattern: 'dosen.dashboard',
        icon: <PresentationChartBarIcon className="w-5 h-5 mr-3" />
    },
    {
        name: "Bank Soal",
        pathName: 'dosen.bank-soal.index', // Pastikan ini sesuai dengan nama baru
        pathPattern: 'dosen.bank-soal.*',
        icon: <BookOpenIcon className="w-5 h-5 mr-3" />
    },
    {
        name: "Manajemen Ujian",
        pathName: 'dosen.ujian.index', // Rute baru untuk daftar ujian
        pathPattern: 'dosen.ujian.*',
        icon: <DocumentTextIcon className="w-5 h-5 mr-3" /> // Contoh ikon baru
    }
];

export default function DosenSidenav() {
    return (
        <aside className="bg-white shadow-lg w-64 h-screen fixed top-0 left-0 z-40 p-4">
            <div className="mb-8 text-center">
                <Link href={route('home')}>
                    <Typography variant="h5" color="blue-gray">
                        Panel Dosen
                    </Typography>
                </Link>
            </div>
            <div className="flex flex-col gap-2">
                {dosenRoutes.map(({ name, pathName, pathPattern, icon }) => {
                    // Gunakan pathPattern untuk mengecek link aktif
                    const isActive = route().current(pathPattern);

                    return (
                        // Gunakan route(pathName) untuk membuat URL
                        <Link key={name} href={route(pathName)}>
                            <Button
                                variant={isActive ? "gradient" : "text"}
                                color={isActive ? "blue" : "blue-gray"}
                                className="flex items-center gap-4 px-4 capitalize"
                                fullWidth
                            >
                                {icon}
                                <Typography color="inherit" className="font-medium capitalize text-sm">
                                    {name}
                                </Typography>
                            </Button>
                        </Link>
                    );
                })}
            </div>
        </aside>
    );
}