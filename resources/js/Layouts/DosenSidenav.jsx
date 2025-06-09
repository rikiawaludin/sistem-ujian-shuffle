import React from 'react';
import { Link, usePage } from '@inertiajs/react';
import { Typography, Button } from "@material-tailwind/react";
import { BookOpenIcon, PresentationChartBarIcon } from "@heroicons/react/24/solid";

// Definisikan rute khusus untuk navigasi dosen
const dosenRoutes = [
    // {
    //     name: "Dashboard Dosen",
    //     path: route('dosen.dashboard'), // Ganti dengan nama rute dashboard dosen Anda jika ada
    //     icon: <PresentationChartBarIcon className="w-5 h-5 mr-3" />
    // },
    {
        name: "Bank Soal",
        path: route('dosen.bank-soal.index'),
        icon: <BookOpenIcon className="w-5 h-5 mr-3" />
    }
];

export default function DosenSidenav() {
    return (
        <aside className="bg-white shadow-lg w-64 h-screen fixed top-0 left-0 z-40 p-4">
            <div className="mb-8 text-center">
                <Link href={route('dashboard')}>
                    <Typography variant="h5" color="blue-gray">
                        Panel Dosen
                    </Typography>
                </Link>
            </div>
            <div className="flex flex-col gap-2">
                {dosenRoutes.map(({ name, path, icon }) => {
                    const isActive = route().current(path.split('/').pop() + '.*');
                    return (
                        <Link key={name} href={path}>
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