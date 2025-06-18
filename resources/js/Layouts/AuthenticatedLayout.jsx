// resources/js/Layouts/AuthenticatedLayout.jsx

import React, { useState } from "react";
import { Head, usePage } from '@inertiajs/react';

import { Bars3Icon } from "@heroicons/react/24/outline";
import AppNavbar from "@/Components/AppNavbar";
import DosenSidenav from "./DosenSidenav";
import Footer from "@/widgets/layout/footer";

// DIUBAH: Tambahkan prop 'useCustomPadding' dengan nilai default 'false'
export default function AuthenticatedLayout({ children, title, useCustomPadding = false }) {
  const { url, auth } = usePage().props;
  const isDosenPanel = auth.user && auth.user.is_dosen;

  // State untuk mengontrol sidebar
  const [isSidebarCollapsed, setIsSidebarCollapsed] = useState(false);
  const [isMobileSidebarOpen, setIsMobileSidebarOpen] = useState(false);

  // Fungsi untuk mengubah state
  const toggleSidebarCollapse = () => setIsSidebarCollapsed(!isSidebarCollapsed);
  const toggleMobileSidebar = () => setIsMobileSidebarOpen(!isMobileSidebarOpen);

  return (
    <div className={`min-h-screen ${!useCustomPadding ? 'bg-gray-50' : ''}`}>
      {title && <Head title={title} />}

      {isDosenPanel ? (
        // Tampilan Dosen dengan sidebar yang dinamis
        <div className="relative min-h-screen md:flex">
          {/* Backdrop untuk mobile view, muncul saat sidebar terbuka */}
          {isMobileSidebarOpen && (
            <div
              className="fixed inset-0 z-20 bg-black opacity-50 md:hidden"
              onClick={toggleMobileSidebar}
            ></div>
          )}

          {/* Kirim state dan fungsi toggle ke Sidenav */}
          <DosenSidenav
            isCollapsed={isSidebarCollapsed}
            isMobileOpen={isMobileSidebarOpen}
            toggleCollapse={toggleSidebarCollapse}
            onCloseMobile={toggleMobileSidebar} // Fungsi untuk menutup dari dalam Sidenav
          />

          {/* Konten Utama */}
          <main className={`flex-1 p-4 md:p-8 transition-all duration-300 ease-in-out ${isSidebarCollapsed ? 'md:ml-20' : 'md:ml-64'}`}>
            {/* Header dengan Tombol Hamburger untuk Mobile */}
            <header className="mb-4 md:hidden">
              <button
                onClick={toggleMobileSidebar}
                className="p-2 text-gray-500 rounded-md hover:bg-gray-200 focus:outline-none focus:ring"
              >
                <Bars3Icon className="w-6 h-6" />
              </button>
            </header>

            {/* Children */}
            {children}
          </main>
        </div>
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
                  brandName="STMIK Bandung"
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