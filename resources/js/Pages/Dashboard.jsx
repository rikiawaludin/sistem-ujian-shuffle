import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Typography, Card } from "@material-tailwind/react"; // Button mungkin tidak lagi dibutuhkan di sini jika sudah di dalam panel
import { usePage } from '@inertiajs/react';
import { Swiper, SwiperSlide } from 'swiper/react';
import 'swiper/css';

// Impor komponen panel yang sudah dipisah
import PanelRiwayatUjian from '@/Components/DashboardPanels/PanelRiwayatUjian';
import PanelUjianAktif from '@/Components/DashboardPanels/PanelUjianAktif';
import PanelUjianMendatang from '@/Components/DashboardPanels/PanelUjianMendatang';
import RingkasanMataKuliahCard from '@/Components/RingkasanMataKuliahCard';

export default function Dashboard() {
  const { auth, daftarMataKuliah } = usePage().props;

  const mataKuliahUntukDitampilkan = daftarMataKuliah || [
    {
        id: 1,
        nama: 'Pemrograman Web Lanjut',
        dosen: { nama: 'Dr. Indah K., M.Kom.' },
        deskripsi_singkat: 'Mempelajari konsep lanjutan pengembangan web dengan framework modern dan best practices terkini.',
        jumlah_ujian_tersedia: 5,
        img: '/images/web-lanjut.jfif', // Ganti dengan path gambar Anda
    },
    {
        id: 2,
        nama: 'Kalkulus Dasar',
        dosen: { nama: 'Dr. Retno W., M.Si.' },
        deskripsi_singkat: 'Pengenalan konsep fundamental limit, turunan, dan integral untuk aplikasi rekayasa.',
        jumlah_ujian_tersedia: 3,
        img: '/images/kalkulus-dasar.jfif', // Ganti dengan path gambar Anda
    },
    {
        id: 3,
        nama: 'Fisika Mekanika Klasik',
        dosen: { nama: 'Prof. Dr. Agus H.' },
        deskripsi_singkat: 'Studi tentang gerak benda dan gaya yang mempengaruhinya berdasarkan hukum Newton.',
        jumlah_ujian_tersedia: 4,
        img: '/images/fisika.jpg', // Ganti dengan path gambar Anda
    },
  ];

  const mataKuliahList = Array.isArray(mataKuliahUntukDitampilkan) ? mataKuliahUntukDitampilkan : [];

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
        <Swiper
          spaceBetween={16}
          slidesPerView={1}
          initialSlide={1}
          centeredSlides={true}
          className="mySwiper"
          style={{ paddingLeft: '16px', paddingRight: '16px' }}
        >
          <SwiperSlide><PanelRiwayatUjian /></SwiperSlide>
          <SwiperSlide><PanelUjianAktif /></SwiperSlide>
          <SwiperSlide><PanelUjianMendatang /></SwiperSlide>
        </Swiper>
      </div>
      <hr />
      <br />
      <div id="mata-kuliah-section" className="mb-12">
        <Typography variant="h4" color="blue-gray" className="mb-6 font-semibold">
          Daftar Ujian Mata Kuliah
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