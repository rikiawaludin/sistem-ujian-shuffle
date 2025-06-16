import React, { useState, useEffect, useCallback, useMemo } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Typography, Card, CardBody, Spinner } from "@material-tailwind/react";
import { usePage, Head } from '@inertiajs/react';
import { ArchiveBoxIcon, PlayCircleIcon, CalendarDaysIcon } from '@heroicons/react/24/solid';
import axios from 'axios';
import 'swiper/css';

import FloatingBackground from '@/Components/Homepage/FloatingBackground';
import HomeFeatureCard from '@/Components/Homepage/HomeFeatureCard';
import RingkasanMataKuliahCard from '@/Components/DashboardPanels/RingkasanMataKuliahCard';
import Modal from '@/Components/Modal';
import UjianListItem from '@/Components/UjianListItem';

export default function Dashboard() {
    const {
        auth,
        daftarMataKuliahLokal,
        historiUjian,
        daftarUjian,
        filters,
        apiBaseUrl,
        sessionToken
    } = usePage().props;

    const [modalState, setModalState] = useState({
        isOpen: false,
        title: '',
        data: [],
    });

    const [kelasKuliahMahasiswaApi, setKelasKuliahMahasiswaApi] = useState([]);
    const [isLoadingKelasKuliah, setIsLoadingKelasKuliah] = useState(true);
    const [errorKelasKuliah, setErrorKelasKuliah] = useState(null);
    const [selectedSemester, setSelectedSemester] = useState(String(filters?.semester || 'semua'));

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
                const dataKelasApi = kelas.data_kelas ?? {};
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
                    id_matakuliah_lokal: mkLokal?.id_lokal || null
                };
            })
            .filter(mk => selectedSemester === 'semua' || String(mk.semester) === String(selectedSemester));
    }, [kelasKuliahMahasiswaApi, selectedSemester, daftarMataKuliahLokal]);

    const handleOpenModal = (status) => {
        let title = '';
        let data = [];

        if (status === 'Aktif') {
            title = 'Ujian Aktif';
            data = daftarUjian.filter(u => u.status === 'Aktif');
        } else if (status === 'Selesai') {
            title = 'Riwayat Ujian';
            data = historiUjian;
        } else if (status === 'Mendatang') {
            title = 'Ujian Mendatang';
            data = daftarUjian.filter(u => u.status === 'Mendatang');
        }

        setModalState({ isOpen: true, title, data });
    };

    const handleCloseModal = () => {
        setModalState({ isOpen: false, title: '', data: [] });
    };

    const summaryData = useMemo(() => {
        return [
            {
                icon: <PlayCircleIcon />,
                title: "Ujian Aktif",
                description: `Klik untuk melihat semua ujian yang sedang berlangsung atau dapat Anda kerjakan saat ini.`,
                onClick: () => handleOpenModal('Aktif'),
                variant: 'primary',
            },
            {
                icon: <ArchiveBoxIcon />,
                title: "Riwayat Ujian",
                description: `Lihat kembali semua ujian yang telah Anda selesaikan, lengkap dengan skor dan detail lainnya.`,
                onClick: () => handleOpenModal('Selesai'),
                variant: 'secondary',
            },
            {
                icon: <CalendarDaysIcon />,
                title: "Ujian Mendatang",
                description: `Pantau jadwal semua ujian yang akan datang agar Anda bisa mempersiapkan diri dengan lebih baik.`,
                onClick: () => handleOpenModal('Mendatang'),
                variant: 'tertiary',
            },
        ];
    }, [daftarUjian, historiUjian]);


    return (
        <AuthenticatedLayout user={auth.user} title="Dashboard Ujian" useCustomPadding={true}>
            <Head title="Home" />
            <div className="relative min-h-screen w-full bg-gray-50">
                <FloatingBackground />
                <div className="relative z-10 flex flex-col min-h-[calc(100vh-150px)]">
                    <header className="text-center py-12 px-4">
                        <Typography variant="h1" className="text-4xl md:text-5xl font-bold text-gray-800 mb-4 drop-shadow-sm">
                            Selamat Datang, {auth.user.name ? auth.user.name.split(' ')[0] : 'Mahasiswa'}!
                        </Typography>
                        <Typography className="text-lg md:text-xl text-gray-600 max-w-2xl mx-auto leading-relaxed">
                            Platform ujian digital modern dengan fitur lengkap untuk pengalaman belajar yang optimal.
                        </Typography>
                    </header>
                    <div className="flex-1 flex items-center justify-center px-4 pb-20">
                        <div className="max-w-7xl mx-auto w-full">
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 lg:gap-12">
                                {/* DI SINI LETAK PERBAIKANNYA */}
                                {summaryData.map((data) => (
                                    <button key={data.title} onClick={data.onClick} className="text-left h-full">
                                        <HomeFeatureCard
                                            icon={data.icon}
                                            title={data.title}
                                            description={data.description}
                                            variant={data.variant}
                                        />
                                    </button>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
                <div id="mata-kuliah-section" className="bg-gray-50 px-4 pb-20 pt-4 relative z-10">
                    <div className="container mx-auto">
                        <div className="relative h-40 w-full overflow-hidden rounded-xl bg-gradient-to-tr from-blue-600 to-blue-400 bg-cover bg-center">
                            <div className="absolute inset-0 h-full w-full bg-black/50" />
                        </div>
                        <Card className="mx-3 -mt-32 mb-6 lg:mx-4 border border-blue-gray-100">
                            <CardBody className="p-4 sm:p-6">
                                <div className="px-4 pb-4">
                                    <Typography variant="h6" color="blue-gray" className="mb-2">
                                        Mata Kuliah Anda
                                    </Typography>
                                    <Typography variant="small" className="font-normal text-blue-gray-500">
                                        Pilih mata kuliah untuk memulai ujian atau melihat riwayat.
                                    </Typography>

                                    {isLoadingKelasKuliah ? (
                                        <div className="flex justify-center py-12"><Spinner className="h-10 w-10" /></div>
                                    ) : errorKelasKuliah ? (
                                        <Card className="mt-6 p-8 text-center"><Typography color="red">{errorKelasKuliah}</Typography></Card>
                                    ) : mataKuliahTampilList.length > 0 ? (
                                        <div className="mt-6 grid grid-cols-1 gap-12 md:grid-cols-2 xl:grid-cols-4">
                                            {mataKuliahTampilList.map((mk) => (
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
                <Modal
                    isOpen={modalState.isOpen}
                    onClose={handleCloseModal}
                    title={modalState.title}
                >
                    {modalState.data.length > 0 ? (
                        modalState.data.map(ujian => (
                            <UjianListItem key={ujian.id_ujian || ujian.id_pengerjaan} ujian={ujian} />
                        ))
                    ) : (
                        <Typography color="gray" className="text-center">Tidak ada ujian untuk ditampilkan.</Typography>
                    )}
                </Modal>
            </div>
        </AuthenticatedLayout>
    );
}