import React from "react";
import { Head } from '@inertiajs/react';
import AppNavbar from "@/Components/AppNavbar"; // Pastikan nama dan path komponen Navbar benar
import Footer from "@/widgets/layout/footer";   // Pastikan path benar

export default function AuthenticatedLayout({ children, title }) {
  return (
    <div className="min-h-screen bg-frost"> {/* Warna latar utama */}
      {title && <Head title={title} />}

      <AppNavbar />

      {/* Kontainer Utama untuk Konten Halaman */}
      {/* Diubah dari "p-4" menjadi lebih spesifik dengan max-width dan padding responsif */}
      <div className="max-w-screen-xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        {/* 'py-6' untuk padding atas-bawah */}
        {/* 'px-4 sm:px-6 lg:px-8' untuk padding kiri-kanan yang responsif */}
        {/* 'max-w-screen-xl' membatasi lebar maksimum konten, 'mx-auto' membuatnya terpusat */}
        
        <main className="mt-2"> {/* Sesuaikan mt-2 atau hilangkan jika py-6 sudah cukup */}
          {children}
        </main>
        
        <div className="text-blue-gray-600 mt-8">
          <Footer
            brandName="Nama Tim Anda" // Sesuaikan
            brandLink="#"            // Sesuaikan
            routes={[
              // Link footer opsional
            ]}
          />
        </div>
      </div>
    </div>
  );
}