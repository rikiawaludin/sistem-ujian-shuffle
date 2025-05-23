import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Typography, Card, Button } from "@material-tailwind/react";
import { usePage } from '@inertiajs/react';
import { Swiper, SwiperSlide } from 'swiper/react';
import 'swiper/css';

// ... (Definisi PanelRiwayatUjian, PanelUjianAktif, PanelUjianMendatang tetap sama) ...
function PanelRiwayatUjian() {
  const ujianTerakhir = { mataKuliah: "Kalkulus Dasar", dosen: "Dr. Retno Wulandari, M.Si.", status: "Selesai pada 20 Mei 2025", nilai: 85 };
  return (
    <Card className="p-6 shadow-lg h-full w-full">
      <Typography variant="h5" color="blue-gray" className="mb-3 border-b border-blue-gray-200 pb-2">
        Ujian Terakhir
      </Typography>
      {ujianTerakhir ? (
        <div className="space-y-1">
          <Typography variant="h6" color="blue-gray">{ujianTerakhir.mataKuliah}</Typography>
          <Typography variant="small" color="gray">Dosen: {ujianTerakhir.dosen}</Typography>
          <Typography variant="small" color="gray">Status: {ujianTerakhir.status}</Typography>
          {ujianTerakhir.nilai && <Typography variant="small" color="blue" className="font-semibold">Nilai: {ujianTerakhir.nilai}</Typography>}
          <Button size="sm" variant="outlined" className="mt-3 w-full">Lihat Detail Riwayat</Button>
        </div>
      ) : (<Typography color="gray">Belum ada riwayat ujian.</Typography>)}
    </Card>
  );
}

function PanelUjianAktif() {
  const ujianAktif = { mataKuliah: "Fisika Mekanika", dosen: "Prof. Dr. Ir. Agus Hartono", sisaWaktu: "00:45:12" };
  return (
    <Card className="p-6 shadow-lg h-full w-full border-2 border-blue-500">
      <Typography variant="h5" color="blue-gray" className="mb-3 border-b border-blue-gray-200 pb-2">
        Ujian Berlangsung
      </Typography>
      {ujianAktif ? (
        <div className="space-y-1">
          <Typography variant="h6" color="blue-gray">{ujianAktif.mataKuliah}</Typography>
          <Typography variant="small" color="gray">Dosen: {ujianAktif.dosen}</Typography>
          <Typography variant="h4" color="red" className="my-2">{ujianAktif.sisaWaktu}</Typography>
          <Button size="sm" color="red" className="mt-3 w-full">Lanjutkan Ujian</Button>
        </div>
      ) : (<Typography color="gray">Tidak ada ujian yang sedang berlangsung.</Typography>)}
    </Card>
  );
}

function PanelUjianMendatang() {
  const ujianMendatang = { mataKuliah: "Algoritma & Pemrograman", dosen: "Dr. Indah Kurniawati, S.Kom., M.Kom.", jadwal: "27 Mei 2025, 09:00 WIB", durasi: "90 Menit" };
  return (
    <Card className="p-6 shadow-lg h-full w-full">
      <Typography variant="h5" color="blue-gray" className="mb-3 border-b border-blue-gray-200 pb-2">
        Ujian Mendatang
      </Typography>
      {ujianMendatang ? (
        <div className="space-y-1">
          <Typography variant="h6" color="blue-gray">{ujianMendatang.mataKuliah}</Typography>
          <Typography variant="small" color="gray">Dosen: {ujianMendatang.dosen}</Typography>
          <Typography variant="small" color="gray">Jadwal: {ujianMendatang.jadwal}</Typography>
          <Typography variant="small" color="gray">Durasi: {ujianMendatang.durasi}</Typography>
          <Button size="sm" variant="text" className="mt-3 w-full">Lihat Detail Jadwal</Button>
        </div>
      ) : (<Typography color="gray">Belum ada ujian yang dijadwalkan.</Typography>)}
    </Card>
  );
}


export default function Dashboard() {
  const { auth } = usePage().props;

  return (
    <AuthenticatedLayout title="Dashboard Ujian">
      {/* Salam Sambutan */}
      {/* Padding horizontal (px-4 md:px-0) di sini mungkin tidak lagi diperlukan jika AuthenticatedLayout sudah mengatur padding global */}
      <div className="mb-8"> {/* Dihapus: px-4 md:px-0 */}
        <Typography variant="h3" color="blue-gray" className="font-semibold">
          Selamat datang, {auth.user ? auth.user.name : "Pengguna"}!
        </Typography>
        <Typography color="gray" className="mt-1">
          Siap untuk ujian hari ini? Berikut informasi penting untuk Anda.
        </Typography>
      </div>

      {/* Tampilan Grid untuk Desktop dan Tablet (md ke atas) */}
      {/* Grid ini sekarang akan berada di dalam kontainer dengan max-width dari AuthenticatedLayout */}
      <div className="hidden md:grid md:grid-cols-3 md:gap-6">
        <div className="md:col-span-1"><PanelRiwayatUjian /></div>
        <div className="md:col-span-1"><PanelUjianAktif /></div>
        <div className="md:col-span-1"><PanelUjianMendatang /></div>
      </div>

      {/* Tampilan Carousel untuk Mobile (di bawah md) */}
      {/* Kontainer Swiper ini juga akan berada di dalam kontainer dengan max-width dari AuthenticatedLayout */}
      {/* Padding spesifik pada Swiper (style atau wrapper div) mungkin masih berguna untuk jarak antar slide jika diperlukan */}
      <div className="block md:hidden"> {/* Dihapus: px-4 sm:px-6 dari sini, karena sudah diatur oleh AuthenticatedLayout */}
        <Swiper
          spaceBetween={16}
          slidesPerView={1}
          initialSlide={1}
          centeredSlides={true}
          className="mySwiper"
          // Jika Anda ingin slide tidak menyentuh tepi kontainer Swiper yang sudah dipadding oleh AuthenticatedLayout:
          // Anda bisa menambahkan padding pada Swiper itu sendiri atau pada setiap SwiperSlide
          // Contoh: style={{ paddingLeft: '8px', paddingRight: '8px' }} jika padding AuthenticatedLayout dirasa terlalu lebar untuk konten slide
        >
          <SwiperSlide><PanelRiwayatUjian /></SwiperSlide>
          <SwiperSlide><PanelUjianAktif /></SwiperSlide>
          <SwiperSlide><PanelUjianMendatang /></SwiperSlide>
        </Swiper>
      </div>
    </AuthenticatedLayout>
  );
}