import React, { useState, useEffect, useCallback, useMemo, useRef } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Typography, Card, Select, Option, Spinner, Alert } from "@material-tailwind/react";
import { usePage, Head, router } from '@inertiajs/react';
import axios from 'axios'; // Menggunakan axios
import 'swiper/css';

import PanelRiwayatUjian from '@/Components/DashboardPanels/PanelRiwayatUjian';
import PanelUjianAktif from '@/Components/DashboardPanels/PanelUjianAktif';
import PanelUjianMendatang from '@/Components/DashboardPanels/PanelUjianMendatang';
import RingkasanMataKuliahCard from '@/Components/DashboardPanels/RingkasanMataKuliahCard';
import HistoriUjianRingkasanCard from '@/Components/DashboardPanels/HistoriUjianRingkasanCard';
import { InformationCircleIcon } from '@heroicons/react/24/solid';

export default function Dashboard() {
  const {
    auth,
    daftarMataKuliahLokal, // Ini adalah objek/map dari MK lokal, key-nya adalah external_id
    historiUjian,
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
    router.get(route('dashboard'), {
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

  return (
    <AuthenticatedLayout user={auth.user} title="Dashboard Ujian">
      <Head title="Dashboard" />
      
      {/* Top Content: Greeting */}
      <div className="mb-8 px-4 md:px-0">
        <Typography variant="h3" color="blue-gray" className="font-bold">
            Selamat Datang, {auth.user.name.split(' ')[0]}!
        </Typography>
        <Typography color="gray" className="mt-1 font-normal">
            Berikut adalah ringkasan aktivitas ujian Anda.
        </Typography>
      </div>

      {/* --- BAGIAN PANEL RINGKASAN --- */}
      <div className="grid grid-cols-1 gap-6 lg:grid-cols-3 mb-12 px-4 md:px-0">
        <PanelRiwayatUjian />      {/* Kiri */}
        <PanelUjianAktif />        {/* Tengah */}
        <PanelUjianMendatang />    {/* Kanan */}
      </div>

      <hr className="my-10 border-blue-gray-100" />
      
      {/* --- BAGIAN MATA KULIAH --- */}
      <div id="mata-kuliah-section" className="mb-12 px-4 md:px-0">
        <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
          <Typography variant="h4" color="blue-gray" className="font-semibold">
            Mata Kuliah Anda ({selectedTahunAjaran})
          </Typography>
          <div className="w-full sm:w-auto sm:min-w-[200px] md:min-w-[250px]">
            <Select
                label="Pilih Semester"
                value={selectedSemester}
                onChange={(value) => handleSemesterChange(value)}
                animate={{ mount: { y: 0 }, unmount: { y: 25 } }}
            >
                <Option value="semua">Semua Semester</Option>
                {semesterOptions.map(smt => (
                    <Option key={`smt-opt-${smt}`} value={String(smt)}>Semester {smt}</Option>
                ))}
            </Select>
          </div>
        </div>

        {isLoadingKelasKuliah ? (
          <div className="flex justify-center py-8"><Spinner className="h-10 w-10 text-blue-gray-800" /></div>
        ) : errorKelasKuliah ? (
          <Card className="p-8 text-center shadow-md border border-red-200 bg-red-50">
            <Typography color="red" className="opacity-80">
              Gagal memuat data mata kuliah: {errorKelasKuliah}
            </Typography>
          </Card>
        ) : mataKuliahTampilList.length > 0 ? (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            {mataKuliahTampilList.map((mk) => (
              <RingkasanMataKuliahCard key={mk.external_id_kelas || mk.id} mataKuliah={mk} />
            ))}
          </div>
        ) : (
          <Card className="p-8 text-center shadow-md border border-blue-gray-50">
            <Typography color="blue-gray" className="opacity-80">
              Tidak ada mata kuliah yang terdaftar untuk Anda pada semester {selectedSemester === 'semua' ? 'ini' : selectedSemester} tahun ajaran {selectedTahunAjaran}, atau data belum tersedia.
            </Typography>
          </Card>
        )}
      </div>

      {/* --- BAGIAN HISTORI UJIAN --- */}
      <div id="histori-ujian-section" className="mb-12 px-4 md:px-0">
        <Typography variant="h4" color="blue-gray" className="font-semibold mb-6">
            Riwayat Ujian Anda
        </Typography>
        
        {historiUjianList.length > 0 ? (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            {historiUjianList.map((histori) => (
              <HistoriUjianRingkasanCard key={histori.id_pengerjaan} histori={histori} />
            ))}
          </div>
        ) : (
          // Hanya tampilkan pesan jika tidak ada histori, dan mata kuliah sudah selesai loading (untuk menghindari pesan ganda)
          !isLoadingKelasKuliah && (
            <Card className="p-8 text-center shadow-md border border-blue-gray-50">
                <Typography color="blue-gray" className="opacity-80">
                    Anda belum memiliki riwayat pengerjaan ujian.
                </Typography>
            </Card>
          )
        )}
      </div>

    </AuthenticatedLayout>
  );
}