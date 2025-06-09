// resources/js/Layouts/AuthenticatedLayout.jsx

import React from "react";
import { Head, usePage } from '@inertiajs/react';

// Impor KEDUA komponen navigasi
import AppNavbar from "@/Components/AppNavbar";
import DosenSidenav from "./DosenSidenav"; // Pastikan DosenSidenav.jsx ada di folder Layouts
import Footer from "@/widgets/layout/footer";

export default function AuthenticatedLayout({ children, title }) {
  const { url, auth } = usePage().props;

  // Tentukan apakah kita berada di dalam panel dosen berdasarkan peran pengguna
  const isDosenPanel = auth.user && auth.user.is_dosen;
  
  // Alternatif jika Anda ingin berdasarkan URL:
  // const isDosenPanel = url.startsWith('/bank-soal');

  return (
    <div className="min-h-screen bg-gray-50"> {/* Background diganti agar lebih netral */}
      {title && <Head title={title} />}

      {/* === AWAL LOGIKA KONDISIONAL === */}
      {isDosenPanel ? (
        // TAMPILAN UNTUK DOSEN
        <>
          <DosenSidenav />
          <main className="ml-64 p-8"> {/* Konten utama dengan margin untuk sidebar */}
            {children}
          </main>
        </>
      ) : (
        // TAMPILAN UNTUK PENGGUNA LAIN (MAHASISWA)
        <>
          <AppNavbar />
          <div className="max-w-screen-xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
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
        </>
      )}
      {/* === AKHIR LOGIKA KONDISIONAL === */}
    </div>
  );
}