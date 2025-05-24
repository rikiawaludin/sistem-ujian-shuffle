import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Typography, Card } from "@material-tailwind/react";
import { usePage } from '@inertiajs/react';
import { Swiper, SwiperSlide } from 'swiper/react';
import 'swiper/css';

import PanelRiwayatUjian from '@/Components/DashboardPanels/PanelRiwayatUjian';
import PanelUjianAktif from '@/Components/DashboardPanels/PanelUjianAktif';
import PanelUjianMendatang from '@/Components/DashboardPanels/PanelUjianMendatang';
import RingkasanMataKuliahCard from '@/Components/RingkasanMataKuliahCard';

// Data statis ujian yang akan digunakan oleh RingkasanMataKuliahCard
// untuk menghitung jumlah ujian per mata kuliah (jika tidak disediakan dari backend)
// Nantinya, jumlah ujian sebaiknya dihitung di backend dan dioper sebagai bagian dari objek mata kuliah.
const semuaUjianStatis = [
    { id: 101, mata_kuliah_id: 1, nama: "UTS Web Lanjut", status: "Belum Dikerjakan" },
    { id: 102, mata_kuliah_id: 1, nama: "Kuis Framework Web", status: "Sedang Dikerjakan" },
    { id: 103, mata_kuliah_id: 1, nama: "UAS Web Lanjut", status: "Selesai" },
    { id: 201, mata_kuliah_id: 2, nama: "Kuis Kalkulus Bab 1", status: "Belum Dikerjakan" },
    { id: 202, mata_kuliah_id: 2, nama: "UTS Kalkulus", status: "Selesai" },
    { id: 301, mata_kuliah_id: 123, nama: "UTS Fisika Mekanika", status: "Belum Dikerjakan" },
    { id: 302, mata_kuliah_id: 123, nama: "Kuis Dinamika", status: "Belum Dikerjakan" },
];

export default function Dashboard() {
  const { auth, daftarMataKuliah: propsDaftarMataKuliah } = usePage().props;

  const mataKuliahUntukDitampilkan = propsDaftarMataKuliah || [
    { id: 1, nama: 'Pemrograman Web Lanjut', dosen: { nama: 'Dr. Indah K., M.Kom.' }, deskripsi_singkat: 'Mempelajari konsep lanjutan...', img: '/images/web-lanjut.jfif' },
    { id: 2, nama: 'Kalkulus Dasar', dosen: { nama: 'Dr. Retno W., M.Si.' }, deskripsi_singkat: 'Pengenalan konsep limit...', img: '/images/kalkulus-dasar.jfif' },
    { id: 123, nama: 'Fisika Mekanika', dosen: { nama: 'Prof. Dr. Agus H.' }, deskripsi_singkat: 'Studi tentang gerak benda.', img: '/images/fisika.jpg' },
  ];

  // Tambahkan jumlah ujian ke setiap mata kuliah
  const mataKuliahList = mataKuliahUntukDitampilkan.map(mk => {
    const jumlahUjian = semuaUjianStatis.filter(ujian => ujian.mata_kuliah_id === mk.id).length;
    return { ...mk, jumlah_ujian_tersedia: jumlahUjian };
  });

  return (
    <AuthenticatedLayout title="Dashboard Ujian">
      <div id="dashboard-top-content" className="mb-8">
        <Typography variant="h3" color="blue-gray" className="font-semibold">
          Selamat datang, {auth.user ? auth.user.name : "Pengguna"}!
        </Typography>
        <Typography color="gray" className="mt-1">
          Siap untuk ujian hari ini? Berikut informasi penting untuk Anda.
        </Typography>
      </div>

      <div className="hidden md:grid md:grid-cols-3 md:gap-6 mb-12">
        <div className="md:col-span-1"><PanelRiwayatUjian /></div>
        <div className="md:col-span-1"><PanelUjianAktif /></div>
        <div className="md:col-span-1"><PanelUjianMendatang /></div>
      </div>

      <div className="block md:hidden mb-12">
        <Swiper spaceBetween={16} slidesPerView={1} initialSlide={1} centeredSlides={true} className="mySwiper" style={{ paddingLeft: '16px', paddingRight: '16px' }}>
          <SwiperSlide><PanelRiwayatUjian /></SwiperSlide>
          <SwiperSlide><PanelUjianAktif /></SwiperSlide>
          <SwiperSlide><PanelUjianMendatang /></SwiperSlide>
        </Swiper>
      </div>
      <hr className="my-6 border-blue-gray-100" /> {/* Garis pemisah lebih baik dari <br/> */}
      
      <div id="mata-kuliah-section" className="mb-12"> {/* Nama ID sudah benar */}
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
          <Card className="p-8 text-center shadow-lg border border-blue-gray-50">
            <Typography color="blue-gray" className="opacity-80">
              Belum ada mata kuliah yang terdaftar untuk Anda.
            </Typography>
          </Card>
        )}
      </div>
    </AuthenticatedLayout>
  );
}