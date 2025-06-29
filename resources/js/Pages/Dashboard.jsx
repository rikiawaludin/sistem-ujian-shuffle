// resources/js/Layouts/Dashboard.jsx

import React, { useState, useEffect, useCallback, useMemo } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Typography, Card, Spinner, Input } from "@material-tailwind/react";
import { usePage, Head } from '@inertiajs/react';
import axios from 'axios';
import { MagnifyingGlassIcon } from '@heroicons/react/24/solid';

// Hapus import yang tidak lagi digunakan
// import HomeFeatureCard from '@/Components/Homepage/HomeFeatureCard';
// import RingkasanMataKuliahCard from '@/Components/DashboardPanels/RingkasanMataKuliahCard';

// Import komponen baru
import DashboardHeader from '@/Components/DashboardPanels/DashboardHeader';
import MataKuliahCard from '@/Components/DashboardPanels/MataKuliahCard';

// Modal & UjianListItem bisa tetap ada jika ada alur lain yang menggunakannya,
// namun dalam konteks desain baru ini, mereka tidak dipanggil dari halaman utama.
// import Modal from '@/Components/Modal';
// import UjianListItem from '@/Components/UjianListItem';

export default function Dashboard() {
    const {
        auth,
        daftarMataKuliahLokal,
        // historiUjian, // Tidak digunakan di layout baru
        // daftarUjian, // Tidak digunakan di layout baru
        filters,
        apiBaseUrl,
        sessionToken
    } = usePage().props;

    const [kelasKuliahMahasiswaApi, setKelasKuliahMahasiswaApi] = useState([]);
    const [isLoadingKelasKuliah, setIsLoadingKelasKuliah] = useState(true);
    const [errorKelasKuliah, setErrorKelasKuliah] = useState(null);

    // State baru untuk pencarian
    const [searchTerm, setSearchTerm] = useState('');

    const fetchKelasKuliahMahasiswa = useCallback(async () => {
        if (!apiBaseUrl || typeof sessionToken !== 'string' || sessionToken.trim() === '') {
            setErrorKelasKuliah("Token sesi tidak valid atau tidak ditemukan. Silakan coba login kembali.");
            setIsLoadingKelasKuliah(false); return;
        }
        setIsLoadingKelasKuliah(true); setErrorKelasKuliah(null);
        let cleanApiBaseUrl = apiBaseUrl.endsWith('/') ? apiBaseUrl.slice(0, -1) : apiBaseUrl;
        const apiUrl = `${cleanApiBaseUrl}/ujian/mata-kuliah/mahasiswa`;

        try {
            const response = await axios.get(apiUrl, {
                headers: { 'Authorization': `Bearer ${sessionToken}`, 'Accept': 'application/json' }
            });
            setKelasKuliahMahasiswaApi(response.data.data?.kelas_kuliah || []);
        } catch (error) {
            console.error("Error fetching kelas kuliah mahasiswa:", error);
            let errorMessage = "Gagal mengambil data kelas kuliah.";
            if (error.response?.data?.message) errorMessage = error.response.data.message;
            else if (error.message) errorMessage = error.message;
            setErrorKelasKuliah(errorMessage);
            setKelasKuliahMahasiswaApi([]);
        } finally { setIsLoadingKelasKuliah(false); }
    }, [apiBaseUrl, sessionToken]);

    useEffect(() => { fetchKelasKuliahMahasiswa(); }, [fetchKelasKuliahMahasiswa]);

    const mataKuliahTampilList = useMemo(() => {
        if (!kelasKuliahMahasiswaApi || kelasKuliahMahasiswaApi.length === 0) return [];
        return kelasKuliahMahasiswaApi
            .filter(kelas => kelas?.matakuliah?.mk_id)
            .map(kelas => {
                const matkulApi = kelas.matakuliah;
                const dosenApi = kelas.dosen ?? {};
                const mkLokal = daftarMataKuliahLokal?.[matkulApi.mk_id] || null;
                let dosenNamaDisplay = "Dosen akan segera ditentukan";
                if (dosenApi.nm_dosen && String(dosenApi.nm_dosen).trim() !== "") {
                    dosenNamaDisplay = `${String(dosenApi.nm_dosen).trim()}${dosenApi.gelar ? ', ' + String(dosenApi.gelar).trim() : ''}`;
                } else if (mkLokal?.dosen_lokal?.nama) {
                    dosenNamaDisplay = mkLokal.dosen_lokal.nama;
                }
                return {
                    id: matkulApi.mk_id,
                    nama: matkulApi.nm_mk,
                    dosen: { nama: dosenNamaDisplay },
                    semester: matkulApi.semester,
                    id_matakuliah_lokal: mkLokal?.id_lokal || null,
                    sks: matkulApi.sks || 0,
                    kode_mk: matkulApi.kd_mk || 'N/A',
                };
            })
    }, [kelasKuliahMahasiswaApi, daftarMataKuliahLokal]);

    const totalSks = useMemo(() => {
        return mataKuliahTampilList.reduce((sum, mk) => sum + mk.sks, 0);
    }, [mataKuliahTampilList]);

    // Logika filter untuk pencarian
    const filteredMataKuliah = useMemo(() => {
        return mataKuliahTampilList.filter(mk =>
            mk.nama.toLowerCase().includes(searchTerm.toLowerCase()) ||
            mk.dosen.nama.toLowerCase().includes(searchTerm.toLowerCase())
        );
    }, [mataKuliahTampilList, searchTerm]);

    return (
        <AuthenticatedLayout user={auth.user} title="Dashboard" useCustomPadding={true}>
            <Head title="Dashboard" />
            <div className="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-indigo-100">
                <div className="container mx-auto px-4 sm:px-6 py-8">
                    <DashboardHeader
                        auth={auth} 
                        userName={auth.user.name ? auth.user.name : 'Mahasiswa'}
                        totalMataKuliah={mataKuliahTampilList.length}
                        totalSks={totalSks}
                        namaJurusan={auth.user.nama_jurusan}
                    />

                    <div className="mb-8">
                        <div className="flex flex-col md:flex-row gap-4 items-center justify-between">
                            <Typography variant="h4" color="blue-gray" className="font-bold shrink-0">
                                Mata Kuliah Anda
                            </Typography>
                            <div className="w-full md:max-w-sm">
                                <Input
                                    label="Cari mata kuliah atau dosen..."
                                    icon={<MagnifyingGlassIcon className="w-5 h-5" />}
                                    value={searchTerm}
                                    onChange={(e) => setSearchTerm(e.target.value)}
                                    className="bg-white/70 backdrop-blur-sm"
                                />
                            </div>
                        </div>
                    </div>

                    {isLoadingKelasKuliah ? (
                        <div className="flex justify-center py-20"><Spinner className="h-12 w-12" /></div>
                    ) : errorKelasKuliah ? (
                        <Card className="p-8 text-center bg-red-50/50 border-red-200"><Typography color="red" className="font-semibold">{errorKelasKuliah}</Typography></Card>
                    ) : filteredMataKuliah.length > 0 ? (
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            {filteredMataKuliah.map((mk) => (
                                <MataKuliahCard key={mk.id_matakuliah_lokal || mk.id} mataKuliah={mk} />
                            ))}
                        </div>
                    ) : (
                        <div className="text-center py-20">
                            <Typography variant="h6" color="blue-gray" className="font-medium">
                                Tidak Ada Mata Kuliah Ditemukan
                            </Typography>
                            <Typography color="gray" className="mt-1">
                                Coba ubah kata kunci pencarian Anda atau periksa kembali semester yang aktif.
                            </Typography>
                        </div>
                    )}

                    <div className="mt-12 text-center">
                        <div className="inline-flex items-center gap-2 bg-white/60 backdrop-blur-sm rounded-full px-5 py-2.5 text-sm text-gray-600 border border-gray-200/80">
                            <span className={`w-2 h-2 rounded-full animate-pulse ${isLoadingKelasKuliah ? 'bg-yellow-500' : 'bg-green-500'}`}></span>
                            Menampilkan {filteredMataKuliah.length} dari {mataKuliahTampilList.length} mata kuliah
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}