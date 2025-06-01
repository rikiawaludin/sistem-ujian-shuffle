// resources/js/Pages/Dashboard.jsx
import React, { useState, useEffect } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Typography, Card, Select, Option } from "@material-tailwind/react";
import { usePage, Head, router } from '@inertiajs/react';
import { Swiper, SwiperSlide } from 'swiper/react';
import 'swiper/css';

import PanelRiwayatUjian from '@/Components/DashboardPanels/PanelRiwayatUjian';
import PanelUjianAktif from '@/Components/DashboardPanels/PanelUjianAktif';
import PanelUjianMendatang from '@/Components/DashboardPanels/PanelUjianMendatang';
import RingkasanMataKuliahCard from '@/Components/DashboardPanels/RingkasanMataKuliahCard';
import HistoriUjianRingkasanCard from '@/Components/DashboardPanels/HistoriUjianRingkasanCard';

export default function Dashboard() {
  const { auth, daftarMataKuliah, historiUjian, availableSemesters, filters } = usePage().props;

  const mataKuliahList = Array.isArray(daftarMataKuliah) ? daftarMataKuliah : [];
  const historiUjianList = Array.isArray(historiUjian) ? historiUjian : [];
  const semesterOptions = Array.isArray(availableSemesters) ? availableSemesters : [];

  // State lokal untuk filter. Ini akan selalu mencerminkan apa yang seharusnya ditampilkan di Select.
  // Diinisialisasi dari props.filters saat komponen pertama kali dimuat.
  const [selectedSemester, setSelectedSemester] = useState(String(filters?.semester || 'semua'));
  const [selectedTahunAjaran, setSelectedTahunAjaran] = useState(filters?.tahun_ajaran || '2024/2025');

  // useEffect ini bertugas untuk MEMPERBARUI STATE LOKAL
  // ketika props.filters dari Inertia berubah (misalnya setelah router.get selesai
  // atau ketika pengguna menggunakan tombol back/forward browser).
  useEffect(() => {
    // console.log("Props 'filters' berubah, menyinkronkan state lokal:", filters);
    const semesterFromProp = String(filters?.semester || 'semua');
    const tahunAjaranFromProp = filters?.tahun_ajaran || '2024/2025';

    // Hanya update state jika berbeda dengan nilai dari props, untuk menghindari loop render yang tidak perlu.
    if (selectedSemester !== semesterFromProp) {
      setSelectedSemester(semesterFromProp);
    }
    if (selectedTahunAjaran !== tahunAjaranFromProp) {
      setSelectedTahunAjaran(tahunAjaranFromProp);
    }
  }, [filters]); // Dependensi HANYA pada props.filters


  // Fungsi untuk menangani perubahan pada Select Semester.
  // Fungsi ini HANYA bertanggung jawab untuk MEMICU navigasi Inertia.
  const handleSemesterChange = (value) => {
    // console.log("User memilih semester:", value, "Tahun ajaran saat ini:", selectedTahunAjaran);

    // State setSelectedSemester(value) TIDAK PERLU dipanggil di sini.
    // Perubahan pada <Select> akan memicu router.get.
    // Setelah router.get berhasil, Inertia akan mengirim props.filters baru.
    // useEffect di atas yang bergantung pada [filters] akan menangkap perubahan props.filters tersebut
    // dan kemudian mengupdate state selectedSemester, yang akan me-render ulang <Select> dengan nilai baru.

    router.get(route('dashboard'), {
      semester: value, // Gunakan 'value' yang baru dipilih pengguna dari argumen fungsi
      tahun_ajaran: selectedTahunAjaran // Gunakan state tahun ajaran saat ini
    }, {
      preserveState: true,
      preserveScroll: true,
      replace: true
    });
  };

  // Handler untuk tahun ajaran (jika Anda membuat Select untuk ini)
  // const handleTahunAjaranChange = (value) => {
  //   router.get(route('dashboard'), {
  //       semester: selectedSemester,
  //       tahun_ajaran: value 
  //   }, { preserveState: true, preserveScroll: true, replace: true });
  // };

  return (
    <AuthenticatedLayout
      user={auth.user}
      title="Dashboard Ujian"
    >
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
        <div className="md:col-span-1"><PanelUjianAktif /></div>
        <div className="md:col-span-1"><PanelUjianMendatang /></div>
      </div>
      <div className="block md:hidden mb-12">
        <Swiper
          spaceBetween={16}
          slidesPerView={1.2}
          centeredSlides={false}
          initialSlide={0}
          className="mySwiperMobileDashboard"
          style={{ paddingLeft: '16px', paddingRight: '16px' }}
        >
          <SwiperSlide><PanelRiwayatUjian historiTerakhir={historiUjianList.length > 0 ? historiUjianList[0] : null} /></SwiperSlide>
          <SwiperSlide><PanelUjianAktif /></SwiperSlide>
          <SwiperSlide><PanelUjianMendatang /></SwiperSlide>
        </Swiper>
      </div>

      <hr className="my-8 border-blue-gray-100" />

      <div id="mata-kuliah-section" className="mb-12">
        <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
          <Typography variant="h4" color="blue-gray" className="font-semibold">
            Mata Kuliah Tersedia ({selectedTahunAjaran})
          </Typography>
          <div className="w-full sm:w-auto sm:min-w-[200px]"> {/* Pastikan Select punya lebar yang cukup */}
            <select
              value={selectedSemester}
              onChange={(e) => handleSemesterChange(e.target.value)}
              className="p-2 border rounded" // styling sederhana
            >
              <option value="semua">Semua Semester</option>
              {semesterOptions.map(smt => (
                <option key={`smt-html-${smt}`} value={String(smt)}>Semester {smt}</option>
              ))}
            </select>
          </div>
        </div>

        {mataKuliahList.length > 0 ? (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {mataKuliahList.map((mk) => (
              <RingkasanMataKuliahCard key={mk.id} mataKuliah={mk} />
            ))}
          </div>
        ) : (
          <Card className="p-8 text-center shadow-md border border-blue-gray-50">
            <Typography color="blue-gray" className="opacity-80">
              Tidak ada mata kuliah yang tersedia untuk filter yang dipilih pada tahun ajaran {selectedTahunAjaran}.
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