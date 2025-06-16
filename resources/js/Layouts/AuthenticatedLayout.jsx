// resources/js/Layouts/AuthenticatedLayout.jsx

import React from "react";
import { Head, usePage } from '@inertiajs/react';

import AppNavbar from "@/Components/AppNavbar";
import DosenSidenav from "./DosenSidenav";
import Footer from "@/widgets/layout/footer";

// DIUBAH: Tambahkan prop 'useCustomPadding' dengan nilai default 'false'
export default function AuthenticatedLayout({ children, title, useCustomPadding = false }) {
    const { url, auth } = usePage().props;
    const isDosenPanel = auth.user && auth.user.is_dosen;

    return (
        <div className={`min-h-screen ${!useCustomPadding ? 'bg-gray-50' : ''}`}>
            {title && <Head title={title} />}

            {isDosenPanel ? (
                // Tampilan Dosen (tidak diubah)
                <>
                    <DosenSidenav />
                    <main className="ml-64 p-8">
                        {children}
                    </main>
                </>
            ) : (
                // Tampilan Mahasiswa
                <>
                    <AppNavbar />
                    {/* DIUBAH: Logika kondisional ditambahkan di sini.
                      - Jika useCustomPadding={true}, maka {children} akan dirender langsung tanpa wrapper.
                      - Jika tidak (default), maka wrapper dengan padding standar akan digunakan.
                    */}
                    {useCustomPadding ? (
                        children
                    ) : (
                        <div className="max-w-screen-2xl mx-auto py-6 px-2">
                            <main className="mt-2">
                                {children}
                            </main>
                            <div className="text-blue-gray-600 mt-8">
                                <Footer
                                    brandName="Nama Tim Anda"
                                    brandLink="#"
                                    routes={[]}
                                />
                            </div>
                        </div>
                    )}
                </>
            )}
        </div>
    );
}