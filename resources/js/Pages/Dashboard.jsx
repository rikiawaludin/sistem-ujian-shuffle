import React, { useState, useEffect, useCallback, useMemo, useRef } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Typography, Card, CardBody, Select, Option, Spinner, Alert } from "@material-tailwind/react";
import { usePage, Head, router } from '@inertiajs/react';
import { ArchiveBoxIcon, PlayCircleIcon, CalendarDaysIcon } from '@heroicons/react/24/solid';

import axios from 'axios'; // Menggunakan axios
import 'swiper/css';

import PanelRiwayatUjian from '@/Components/DashboardPanels/PanelRiwayatUjian';
import PanelUjianAktif from '@/Components/DashboardPanels/PanelUjianAktif';
import PanelUjianMendatang from '@/Components/DashboardPanels/PanelUjianMendatang';
import RingkasanMataKuliahCard from '@/Components/DashboardPanels/RingkasanMataKuliahCard';
import InfoPanelCard from '@/Components/DashboardPanels/InfoPanelCard';
import HistoriUjianRingkasanCard from '@/Components/DashboardPanels/HistoriUjianRingkasanCard';
import { InformationCircleIcon } from '@heroicons/react/24/solid';

export default function Dashboard() {
  const {
    auth,
    daftarMataKuliahLokal, // Ini adalah objek/map dari MK lokal, key-nya adalah external_id
    historiUjian,
    daftarUjian,
    availableSemesters,
    filters,
    apiBaseUrl,
    sessionToken,
    flash
  } = usePage().props;

  const [kelasKuliahMahasiswaApi, setKelasKuliahMahasiswaApi] = useState([]);
  const [isLoadingKelasKuliah, setIsLoadingKelasKuliah] = useState(true);
  const [errorKelasKuliah, setErrorKelasKuliah] = useState(null);
  const [localFlash, setLocalFlash] = useState(null);
  const flashMessageTimeoutRef = useRef(null);

  // ... (fungsi showFlashMessage dan useEffect untuk flash server tetap sama) ...

  const [selectedSemester, setSelectedSemester] = useState(String(filters?.semester || 'semua'));
  const [selectedTahunAjaran, setSelectedTahunAjaran] = useState(filters?.tahun_ajaran || '2024/2025');

  useEffect(() => {
    const semesterFromProp = String(filters?.semester || 'semua');
    const tahunAjaranFromProp = filters?.tahun_ajaran || '2024/2025';
    if (selectedSemester !== semesterFromProp) setSelectedSemester(semesterFromProp);
    if (selectedTahunAjaran !== tahunAjaranFromProp) setSelectedTahunAjaran(tahunAjaranFromProp);
  }, [filters]);

  const fetchKelasKuliahMahasiswa = useCallback(async () => {
    if (!apiBaseUrl || typeof sessionToken !== 'string' || sessionToken.trim() === '') {
      console.warn('Dashboard.jsx: Menghentikan fetchKelasKuliahMahasiswa karena sessionToken hilang atau tidak valid.', { apiBaseUrl, sessionToken });
      setErrorKelasKuliah("Token sesi tidak valid atau tidak ditemukan. Silakan coba login kembali.");
      setIsLoadingKelasKuliah(false); return;
    }
    setIsLoadingKelasKuliah(true); setErrorKelasKuliah(null);
    let cleanApiBaseUrl = apiBaseUrl;
    if (apiBaseUrl.endsWith('/')) cleanApiBaseUrl = apiBaseUrl.slice(0, -1);
    const apiUrl = `${cleanApiBaseUrl}/ujian/mata-kuliah/mahasiswa`;
    console.log(`Dashboard.jsx: Fetching (axios) from API URL: ${apiUrl}`);

    try {
      const response = await axios.get(apiUrl, {
        headers: { 'Authorization': `Bearer ${sessionToken}`, 'Accept': 'application/json' }
      });
      setKelasKuliahMahasiswaApi(response.data.data?.kelas_kuliah || []);
    } catch (error) {
      console.error("Error fetching kelas kuliah mahasiswa:", error);
      let errorMessage = "Gagal mengambil data kelas kuliah.";
      if (error.response && error.response.data && error.response.data.message) {
        errorMessage = error.response.data.message;
      } else if (error.message) {
        errorMessage = error.message;
      }
      setErrorKelasKuliah(errorMessage);
      setKelasKuliahMahasiswaApi([]);
    } finally { setIsLoadingKelasKuliah(false); }
  }, [apiBaseUrl, sessionToken]);

  useEffect(() => {
    fetchKelasKuliahMahasiswa();
  }, [fetchKelasKuliahMahasiswa]);

  const handleSemesterChange = (value) => {
    router.get(route('home'), {
      semester: value,
      tahun_ajaran: selectedTahunAjaran
    }, { preserveState: true, preserveScroll: true, replace: true });
  };

  const mataKuliahTampilList = useMemo(() => {
    if (!kelasKuliahMahasiswaApi || kelasKuliahMahasiswaApi.length === 0) return [];

    return kelasKuliahMahasiswaApi
      .filter(kelas => kelas && kelas.matakuliah && kelas.matakuliah.mk_id)
      .map(kelas => {
        const matkulApi = kelas.matakuliah;
        const dosenApi = kelas.dosen ?? {};
        const dataKelasApi = kelas.data_kelas ?? {};
        const mkLokal = daftarMataKuliahLokal ? daftarMataKuliahLokal[matkulApi.mk_id] : null;

        if (!mkLokal) {
          console.warn(`Mata kuliah dari API (ID: ${matkulApi.mk_id}, Nama: ${matkulApi.nm_mk}) tidak ditemukan di data lokal. Ditampilkan dengan data API saja.`);
        }

        let dosenNamaDisplay = "Dosen akan segera ditentukan";
        if (dosenApi.nm_dosen && String(dosenApi.nm_dosen).trim() !== "") {
          dosenNamaDisplay = `${String(dosenApi.nm_dosen).trim()}${dosenApi.gelar ? ', ' + String(dosenApi.gelar).trim() : ''}`;
        } else if (mkLokal && mkLokal.dosen_lokal && mkLokal.dosen_lokal.nama) {
          dosenNamaDisplay = mkLokal.dosen_lokal.nama;
        }

        return {
          id: matkulApi.mk_id,
          external_id_kelas: dataKelasApi.kelas_kuliah_id,
          nama: matkulApi.nm_mk,
          kode_mk: matkulApi.kd_mk,
          dosen: {
            nama: dosenNamaDisplay,
            external_id: dosenApi.dosen_id
          },
          deskripsi_singkat: mkLokal?.deskripsi_lokal || matkulApi.nm_mk,
          img: mkLokal?.img_lokal || '/public/images/placeholder-matakuliah.jpg',
          jumlah_ujian_tersedia: mkLokal?.jumlah_ujian_tersedia_lokal || 0,
          semester: matkulApi.semester,
          tahun_ajaran_kelas: dataKelasApi.tahun_id,
          id_matakuliah_lokal: mkLokal?.id_lokal || null
        };
      })
      .filter(mk => selectedSemester === 'semua' || String(mk.semester) === String(selectedSemester));
  }, [kelasKuliahMahasiswaApi, selectedSemester, daftarMataKuliahLokal]);


  const historiUjianList = Array.isArray(historiUjian) ? historiUjian : [];
  const semesterOptions = Array.isArray(availableSemesters) ? availableSemesters : [];

  // Data untuk panel ringkasan, kita hitung di sini
  const summaryData = useMemo(() => {
    const riwayatCount = Array.isArray(historiUjian) ? historiUjian.length : 0;
    const aktifCount = Array.isArray(daftarUjian) ? daftarUjian.filter(u => u.status === 'Sedang Dikerjakan').length : 0;
    const mendatangCount = Array.isArray(daftarUjian) ? daftarUjian.filter(u => u.status === 'Belum Dikerjakan' || u.status === 'Akan Datang').length : 0;

    return [
      { icon: <ArchiveBoxIcon className="w-6 h-6" />, title: "Ujian Selesai", count: riwayatCount },
      { icon: <PlayCircleIcon className="w-6 h-6" />, title: "Ujian Aktif", count: aktifCount },
      { icon: <CalendarDaysIcon className="w-6 h-6" />, title: "Ujian Mendatang", count: mendatangCount },
    ];
  }, [historiUjian, daftarUjian]);

  return (
    <AuthenticatedLayout user={auth.user} title="Dashboard Ujian">
      <Head title="Home" />

      {/* Header dengan Background */}
      <div id="home-section" className="relative flex h-[320px] content-center items-center justify-center pt-16 pb-32">
        <div className="absolute top-0 h-full w-full bg-[url('/images/background-dashboard.jpg')] bg-cover bg-center" />
        <div className="absolute top-0 h-full w-full bg-black/60 bg-cover bg-center" />
        <div className="max-w-8xl container relative mx-auto">
          <div className="flex flex-wrap items-center">
            <div className="ml-auto mr-auto w-full px-4 text-center lg:w-8/12">
              <Typography variant="h1" color="white" className="mb-6 font-black">
                Selamat Datang, {auth.user.name ? auth.user.name.split(' ')[0] : 'Mahasiswa'}!
              </Typography>
              <Typography variant="lead" color="white" className="opacity-80">
                Ini adalah semua aktivitas ujian Anda. Lihat kemajuan Anda di sini.
              </Typography>
            </div>
          </div>
        </div>
      </div>

      {/* Bagian Konten dengan Card yang menimpa background */}
      <section className="-mt-32 bg-gray-50 px-4 pb-20 pt-4">
        <div className="container mx-auto">
          {/* Tiga Panel Ringkasan menggunakan komponen baru */}
          <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            {summaryData.map(({ icon, title, count }) => (
              <InfoPanelCard key={title} icon={icon} title={title} count={count} />
            ))}
          </div>

          {/* Bagian Mata Kuliah */}
          <div id="mata-kuliah-section" className="mt-12">
            {/* Latar belakang biru */}
            <div className="relative h-40 w-full overflow-hidden rounded-xl bg-gradient-to-tr from-blue-600 to-blue-400 bg-cover bg-center">
              <div className="absolute inset-0 h-full w-full bg-black/50" />
            </div>

            {/* Card yang menimpa latar belakang */}
            <Card className="mx-3 -mt-32 mb-6 lg:mx-4 border border-blue-gray-100">
              <CardBody className="p-4 sm:p-6">
                <div id="mata-kuliah-section" className="px-4 pb-4">
                  <Typography variant="h6" color="blue-gray" className="mb-2">
                    Mata Kuliah Anda
                  </Typography>
                  <Typography variant="small" className="font-normal text-blue-gray-500">
                    Pilih mata kuliah untuk memulai ujian atau melihat riwayat.
                  </Typography>

                  {/* Loading/Error/Content State untuk Mata Kuliah */}
                  {isLoadingKelasKuliah ? (
                    <div className="flex justify-center py-12"><Spinner className="h-10 w-10" /></div>
                  ) : errorKelasKuliah ? (
                    <Card className="mt-6 p-8 text-center"><Typography color="red">{errorKelasKuliah}</Typography></Card>
                  ) : mataKuliahTampilList.length > 0 ? (
                    // Menggunakan struktur grid dari referensi
                    <div className="mt-6 grid grid-cols-1 gap-12 md:grid-cols-2 xl:grid-cols-4">
                      {mataKuliahTampilList.map((mk) => (
                        // Kita panggil komponen RingkasanMataKuliahCard yang sudah kita buat
                        // karena gayanya sudah sangat mirip dengan referensi
                        <RingkasanMataKuliahCard key={mk.id_matakuliah_lokal || mk.id} mataKuliah={mk} />
                      ))}
                    </div>
                  ) : (
                    <Card className="mt-6 p-8 text-center">
                      <Typography>Tidak ada mata kuliah yang terdaftar untuk Anda.</Typography>
                    </Card>
                  )}
                </div>
              </CardBody>
            </Card>
          </div>

        </div>
      </section>

      {/* ========================================================== */}
      {/* BAGIAN 2: MATA KULIAH (DENGAN STYLE BARU)               */}
      {/* ========================================================== */}


    </AuthenticatedLayout>
  );
}