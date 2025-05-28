// resources/js/Pages/Dashboard.jsx
import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Typography, Card } from "@material-tailwind/react";
import { usePage, Head } from '@inertiajs/react';
import { Swiper, SwiperSlide } from 'swiper/react';
import 'swiper/css';

import PanelRiwayatUjian from '@/Components/DashboardPanels/PanelRiwayatUjian'; // Ini bisa menampilkan ringkasan dari prop historiUjian atau data statis internalnya
import PanelUjianAktif from '@/Components/DashboardPanels/PanelUjianAktif';
import PanelUjianMendatang from '@/Components/DashboardPanels/PanelUjianMendatang';
import RingkasanMataKuliahCard from '@/Components/DashboardPanels/RingkasanMataKuliahCard';
import HistoriUjianRingkasanCard from '@/Components/DashboardPanels/HistoriUjianRingkasanCard';

export default function Dashboard() {
  const { auth, daftarMataKuliah, historiUjian } = usePage().props;

  // Data dari props sekarang seharusnya sudah memiliki jumlah_ujian_tersedia dan detail yang cukup
  const mataKuliahList = Array.isArray(daftarMataKuliah) ? daftarMataKuliah : [];
  const historiUjianList = Array.isArray(historiUjian) ? historiUjian : [];

  return (
    <AuthenticatedLayout title="Dashboard Ujian">
      <Head title="Dashboard" />
      
      <div id="dashboard-top-content" className="mb-8">
        <Typography variant="h3" color="blue-gray" className="font-semibold">
          Selamat datang, {auth.user ? auth.user.name : "Pengguna"}!
        </Typography>
        <Typography color="gray" className="mt-1">
          Siap untuk ujian hari ini? Berikut informasi penting untuk Anda.
        </Typography>
      </div>

      {/* Panel Ujian Atas */}
      <div className="hidden md:grid md:grid-cols-3 md:gap-6 mb-12">
        <div className="md:col-span-1"><PanelRiwayatUjian historiTerakhir={historiUjianList.length > 0 ? historiUjianList[0] : null} /></div> 
        <div className="md:col-span-1"><PanelUjianAktif /></div> {/* Perlu data ujian aktif dari backend */}
        <div className="md:col-span-1"><PanelUjianMendatang /></div> {/* Perlu data ujian mendatang dari backend */}
      </div>
      <div className="block md:hidden mb-12">
        <Swiper spaceBetween={16} slidesPerView={1} initialSlide={1} centeredSlides={true} className="mySwiper" style={{ paddingLeft: '16px', paddingRight: '16px' }}>
          <SwiperSlide><PanelRiwayatUjian historiTerakhir={historiUjianList.length > 0 ? historiUjianList[0] : null} /></SwiperSlide>
          <SwiperSlide><PanelUjianAktif /></SwiperSlide>
          <SwiperSlide><PanelUjianMendatang /></SwiperSlide>
        </Swiper>
      </div>

      <hr className="my-8 border-blue-gray-100" />
      
      <div id="mata-kuliah-section" className="mb-12">
        <Typography variant="h4" color="blue-gray" className="mb-6 font-semibold">
          Mata Kuliah Tersedia
        </Typography>
        {mataKuliahList.length > 0 ? (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {mataKuliahList.map((mk) => (
              <RingkasanMataKuliahCard key={mk.id} mataKuliah={mk} />
            ))}
          </div>
        ) : (
          <Card className="p-8 text-center shadow-md border border-blue-gray-50">
            <Typography color="blue-gray" className="opacity-80">
              Belum ada mata kuliah yang terdaftar untuk Anda.
            </Typography>
          </Card>
        )}
      </div>

      <hr className="my-8 border-blue-gray-100" />

      <div id="histori-ujian-section" className="mb-12">
        <Typography variant="h4" color="blue-gray" className="mb-6 font-semibold">
          Riwayat Ujian Anda
        </Typography>
        {historiUjianList.length > 0 ? (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {historiUjianList.map((histori) => (
              <HistoriUjianRingkasanCard key={histori.id_pengerjaan} histori={histori} />
            ))}
          </div>
        ) : (
          <Card className="p-8 text-center shadow-md border border-blue-gray-50">
            <Typography color="blue-gray" className="opacity-80">
              Anda belum memiliki riwayat pengerjaan ujian.
            </Typography>
          </Card>
        )}
      </div>
    </AuthenticatedLayout>
  );
}