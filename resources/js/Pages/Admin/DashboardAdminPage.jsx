import React, { useState, useEffect, useMemo, useRef } from "react";
import { router, usePage, Head } from '@inertiajs/react';
import {
    Button,
    Typography,
    Card,
    CardBody,
    Alert,
    Tabs,
    TabsHeader,
    TabsBody,
    Tab,
    TabPanel,
    Tooltip,
} from "@material-tailwind/react";
import {
    PowerIcon,
    InformationCircleIcon,
    WrenchScrewdriverIcon,
    ArrowLeftOnRectangleIcon,
} from "@heroicons/react/24/solid";
import AdvancedTable from "@/Components/Tables/AdvancedTable";
// DITAMBAHKAN: Pastikan path ke PageHeader sudah benar
import PageHeader from "@/Layouts/PageHeader";

export default function DashboardAdminPage({
    mahasiswaData,
    dosenData,
    prodiData,
    adminData,
    mataKuliahData,
    migrationHistoryUsers,
    migrationHistoryMataKuliah,
    flash
}) {
    // DITAMBAHKAN: Ambil data auth dari usePage untuk digunakan di header
    const { auth } = usePage().props;

    const [loading, setLoading] = useState({
        mahasiswa: false,
        dosen: false,
        prodi: false,
        admin: false,
        mataKuliah: false,
    });
    const [localFlash, setLocalFlash] = useState(null);
    const flashMessageTimeoutRef = useRef(null);

    const showFlashMessage = (message, type = 'info', duration = 5000) => {
        if (flashMessageTimeoutRef.current) {
            clearTimeout(flashMessageTimeoutRef.current);
        }
        setLocalFlash({ type, message });
        flashMessageTimeoutRef.current = setTimeout(() => {
            setLocalFlash(null);
            flashMessageTimeoutRef.current = null;
        }, duration);
    };

    useEffect(() => {
        if (flash && flash.message) {
            showFlashMessage(flash.message, flash.type || 'info', 7000);
            if (router.page && router.page.props && router.page.props.flash) {
                router.remember(
                    { ...router.page.props, flash: null },
                    router.page.url,
                    { replace: true, preserveState: true }
                );
            }
        }
        return () => {
            if (flashMessageTimeoutRef.current) {
                clearTimeout(flashMessageTimeoutRef.current);
            }
        };
    }, [flash]);

    const [activeMainTab, setActiveMainTab] = useState(() => localStorage.getItem('activeMainTab') || "users");
    const [activeUserTab, setActiveUserTab] = useState(() => localStorage.getItem('activeUserTab') || "mahasiswa");

    useEffect(() => {
        localStorage.setItem('activeMainTab', activeMainTab);
    }, [activeMainTab]);

    useEffect(() => {
        localStorage.setItem('activeUserTab', activeUserTab);
    }, [activeUserTab]);

    const handleSync = (type) => {
        setLoading(prev => ({ ...prev, [type]: true }));
        setLocalFlash(null);
        if (flashMessageTimeoutRef.current) {
            clearTimeout(flashMessageTimeoutRef.current);
            flashMessageTimeoutRef.current = null;
        }

        let url = '';
        let dataPropsToReload = [];

        switch (type) {
            case 'mahasiswa':
                url = route('admin.sync.mahasiswa');
                dataPropsToReload = ['mahasiswaData', 'migrationHistoryUsers'];
                break;
            case 'dosen':
                url = route('admin.sync.dosen');
                dataPropsToReload = ['dosenData', 'migrationHistoryUsers'];
                break;
            case 'prodi':
                url = route('admin.sync.prodi');
                dataPropsToReload = ['prodiData', 'migrationHistoryUsers'];
                break;
            case 'admin':
                url = route('admin.sync.admin');
                dataPropsToReload = ['adminData', 'migrationHistoryUsers'];
                break;
            case 'mataKuliah':
                url = route('admin.sync.matakuliah');
                dataPropsToReload = ['mataKuliahData', 'migrationHistoryMataKuliah'];
                break;
            default:
                setLoading(prev => ({ ...prev, [type]: false }));
                return;
        }

        router.post(url, {}, {
            onStart: () => {
                showFlashMessage(`Proses sinkronisasi data ${type} telah dimulai...`, 'info', 3000);
            },
            onFinish: () => {
                const refreshDelay = 2000;
                showFlashMessage(
                    `Sinkronisasi ${type} berjalan di latar belakang. Data akan dimuat ulang setelah ${refreshDelay / 1000} detik...`, 'info', refreshDelay - 500 < 0 ? 500 : refreshDelay - 500
                );
                setTimeout(() => {
                    router.reload({
                        only: dataPropsToReload,
                        preserveScroll: true,
                        onSuccess: (page) => {
                            setLoading(prev => ({ ...prev, [type]: false }));
                            showFlashMessage(`Data ${type} telah berhasil dimuat ulang.`, 'success', 2000);
                        },
                        onError: (errors) => {
                            setLoading(prev => ({ ...prev, [type]: false }));
                            showFlashMessage(`Gagal memuat ulang data ${type} setelah sinkronisasi.`, 'error', 7000);
                        }
                    });
                }, refreshDelay);
            },
            onError: (errors) => {
                setLoading(prev => ({ ...prev, [type]: false }));
                const errorMessage = errors.message || Object.values(errors).join(', ');
                showFlashMessage(`Gagal memulai sinkronisasi ${type}: ${errorMessage}`, 'error', 7000);
            },
            preserveState: (page) => Object.keys(page.props.errors || {}).length > 0,
            preserveScroll: true,
        });
    };

    const userColumns = useMemo(() => [
        { header: 'ID Internal', accessorKey: 'id' },
        { header: 'ID Eksternal', accessorKey: 'external_id' },
        { header: 'Ditambahkan', accessorKey: 'created_at', cell: info => info.getValue() ? new Date(info.getValue()).toLocaleString() : '-' },
        { header: 'Diperbarui', accessorKey: 'updated_at', cell: info => info.getValue() ? new Date(info.getValue()).toLocaleString() : '-' },
    ], []);

    const mataKuliahColumns = useMemo(() => [
        { header: 'ID Internal', accessorKey: 'id' },
        { header: 'ID Eksternal', accessorKey: 'external_id' },
        { header: 'Nama Mata Kuliah', accessorKey: 'nama' },
        { header: 'Kode', accessorKey: 'kode' },
        { header: 'Ditambahkan', accessorKey: 'created_at', cell: info => info.getValue() ? new Date(info.getValue()).toLocaleString() : '-' },
        { header: 'Diperbarui', accessorKey: 'updated_at', cell: info => info.getValue() ? new Date(info.getValue()).toLocaleString() : '-' },
    ], []);

    const migrationHistoryColumns = useMemo(() => [
        { header: 'ID', accessorKey: 'id' },
        {
            header: 'Tipe Sinkronisasi',
            accessorKey: 'type',
            cell: ({ row }) => {
                const original = row.original;
                if (original.is_mahasiswa) return 'Mahasiswa';
                if (original.is_dosen) return 'Dosen';
                if (original.is_prodi) return 'Prodi';
                if (original.is_admin) return 'Admin';
                if (original.is_mata_kuliah) return 'Mata Kuliah';
                return 'Tidak Diketahui';
            }
        },
        { header: 'Tanggal Sinkronisasi', accessorKey: 'created_at', cell: info => info.getValue() ? new Date(info.getValue()).toLocaleString() : '-' },
    ], []);

    const getFilteredHistory = (type) => {
        if (!migrationHistoryUsers) return [];
        switch (type) {
            case 'mahasiswa': return migrationHistoryUsers.filter(h => h.is_mahasiswa);
            case 'dosen': return migrationHistoryUsers.filter(h => h.is_dosen);
            case 'prodi': return migrationHistoryUsers.filter(h => h.is_prodi);
            case 'admin': return migrationHistoryUsers.filter(h => h.is_admin);
            default: return [];
        }
    };

    const userTabContent = (userType, data, historyData, columnsToUse) => (
        <div className="p-4 space-y-6">
            <Button
                color="blue"
                onClick={() => handleSync(userType.toLowerCase())}
                loading={loading[userType.toLowerCase()]}
                className="flex items-center gap-2"
            >
                {!loading[userType.toLowerCase()] && <PowerIcon className="h-5 w-5" />}
                {loading[userType.toLowerCase()] ? `Sinkronisasi Berjalan...` : `Sinkronisasi Data ${userType}`}
            </Button>
            <Typography variant="h6" color="blue-gray" className="mt-4">Data {userType} Tersinkronisasi</Typography>
            <AdvancedTable
                columns={columnsToUse}
                data={data || []}
                isLoading={loading[userType.toLowerCase()]}
            />
            <Typography variant="h6" color="blue-gray" className="mt-6">Histori Sinkronisasi {userType}</Typography>
            <AdvancedTable
                columns={migrationHistoryColumns}
                data={historyData || []}
            />
        </div>
    );
    
    // DITAMBAHKAN: Buat elemen tombol logout untuk dikirim sebagai prop ke PageHeader
    const logoutButton = (
        <Tooltip content="Log Out" placement="bottom">
            <a
                href={route('logout')}
                className="p-2 block rounded-full text-white/80 bg-white/10 hover:bg-white/20 transition-colors focus:outline-none"
                aria-label="Log Out"
            >
                <ArrowLeftOnRectangleIcon className="h-6 w-6" />
            </a>
        </Tooltip>
    );

    return (
        <div className="min-h-screen bg-gray-100 p-4 md:p-6">
            <Head title="Dashboard Admin" />
            <div className="max-w-screen-2xl w-full mx-auto">

                {/* DITAMBAHKAN: Komponen PageHeader diterapkan di sini */}
                <PageHeader
                    title="Dashboard Admin"
                    subtitle={`Login sebagai ${auth.user.name}`}
                    icon={<WrenchScrewdriverIcon />}
                >
                    {logoutButton}
                </PageHeader>

                <Card className="m-0 shadow-lg border border-gray-200">
                    <CardBody className="p-4 md:p-6">
                        
                        <Typography variant="h6" color="blue-gray" className="mb-4">
                            Manajemen Integrasi Data Eksternal
                        </Typography>

                        {(localFlash && localFlash.message) && (
                            <Alert
                                color={localFlash.type === 'success' ? 'green' : localFlash.type === 'warning' ? 'amber' : localFlash.type === 'error' ? 'red' : 'blue'}
                                icon={<InformationCircleIcon className="h-6 w-6" />}
                                className="mb-6"
                                open={!!(localFlash && localFlash.message)}
                            >
                                {localFlash.message}
                            </Alert>
                        )}

                        <Tabs value={activeMainTab}>
                            <TabsHeader
                                className="rounded-none border-b border-blue-gray-50 bg-transparent p-0"
                                indicatorProps={{ className: "bg-transparent border-b-2 border-blue-500 shadow-none rounded-none" }}
                            >
                                <Tab value="users" onClick={() => setActiveMainTab("users")}>Manajemen Users</Tab>
                                <Tab value="courses" onClick={() => setActiveMainTab("courses")}>Manajemen Mata Kuliah</Tab>
                            </TabsHeader>
                            <TabsBody>
                                <TabPanel value="users" className="p-0 pt-4">
                                    <Tabs value={activeUserTab} orientation="horizontal">
                                        <TabsHeader
                                            className="rounded-none border-b border-blue-gray-50 bg-blue-gray-50/50 p-0"
                                            indicatorProps={{ className: "bg-white border-b-2 border-gray-900 shadow-none rounded-none" }}
                                        >
                                            <Tab value="mahasiswa" onClick={() => setActiveUserTab("mahasiswa")}>Mahasiswa</Tab>
                                            <Tab value="dosen" onClick={() => setActiveUserTab("dosen")}>Dosen</Tab>
                                            <Tab value="prodi" onClick={() => setActiveUserTab("prodi")}>Prodi</Tab>
                                            <Tab value="admin" onClick={() => setActiveUserTab("admin")}>Admin</Tab>
                                        </TabsHeader>
                                        <TabsBody>
                                            <TabPanel value="mahasiswa" className="p-0">{userTabContent("Mahasiswa", mahasiswaData, getFilteredHistory('mahasiswa'), userColumns)}</TabPanel>
                                            <TabPanel value="dosen" className="p-0">{userTabContent("Dosen", dosenData, getFilteredHistory('dosen'), userColumns)}</TabPanel>
                                            <TabPanel value="prodi" className="p-0">{userTabContent("Prodi", prodiData, getFilteredHistory('prodi'), userColumns)}</TabPanel>
                                            <TabPanel value="admin" className="p-0">{userTabContent("Admin", adminData, getFilteredHistory('admin'), userColumns)}</TabPanel>
                                        </TabsBody>
                                    </Tabs>
                                </TabPanel>
                                <TabPanel value="courses" className="p-0 pt-4">
                                    <div className="p-4 space-y-6">
                                        <Button
                                            color="blue"
                                            onClick={() => handleSync('mataKuliah')}
                                            loading={loading.mataKuliah}
                                            className="flex items-center gap-2"
                                        >
                                            {!loading.mataKuliah && <PowerIcon className="h-5 w-5" />}
                                            {loading.mataKuliah ? "Sinkronisasi Berjalan..." : "Sinkronisasi Data Mata Kuliah"}
                                        </Button>
                                        <Typography variant="h6" color="blue-gray" className="mt-4">Data Mata Kuliah Tersinkronisasi</Typography>
                                        <AdvancedTable columns={mataKuliahColumns} data={mataKuliahData || []} isLoading={loading.mataKuliah} />
                                        <Typography variant="h6" color="blue-gray" className="mt-6">Histori Sinkronisasi Mata Kuliah</Typography>
                                        <AdvancedTable columns={migrationHistoryColumns} data={migrationHistoryMataKuliah || []} />
                                    </div>
                                </TabPanel>
                            </TabsBody>
                        </Tabs>
                    </CardBody>
                </Card>
            </div>
        </div>
    );
}