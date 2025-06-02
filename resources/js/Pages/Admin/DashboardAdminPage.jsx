import { useState, useEffect } from "react"; // useEffect ditambahkan untuk flash message handling
import { router } from '@inertiajs/react';
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
    Spinner
} from "@material-tailwind/react";
import {
    ArrowPathIcon, // Pengganti Sync, jika masih ingin digunakan di tempat lain
    PowerIcon,
    ClockIcon,
    InformationCircleIcon
} from "@heroicons/react/24/solid";

// Komponen Tabel Dasar (Helper Component)
function BasicTable({ columns, rows, isLoading }) {
    if (isLoading) {
        return (
            <div className="flex justify-center items-center h-32">
                <Spinner className="h-12 w-12" />
            </div>
        );
    }

    if (!rows || rows.length === 0) {
        return (
            <Typography variant="small" className="text-center p-4">
                Tidak ada data untuk ditampilkan.
            </Typography>
        );
    }

    return (
        <div className="overflow-x-auto">
            <table className="w-full min-w-max table-auto text-left">
                <thead>
                    <tr>
                        {columns.map((col) => (
                            <th key={col.field} className="border-b border-blue-gray-100 bg-blue-gray-50 p-4">
                                <Typography variant="small" color="blue-gray" className="font-normal leading-none opacity-70">
                                    {col.headerName || ''} {/* Fallback jika headerName undefined */}
                                </Typography>
                            </th>
                        ))}
                    </tr>
                </thead>
                <tbody>
                    {rows.map((row, index) => {
                        const isLast = index === rows.length - 1;
                        const classes = isLast ? "p-4" : "p-4 border-b border-blue-gray-50";
                        return (
                            <tr key={row.id || index} className="hover:bg-blue-gray-50/50">
                                {columns.map((col) => {
                                    let cellValue = col.valueGetter
                                        ? col.valueGetter({ row })
                                        : row[col.field];

                                    // Pastikan cellValue bukan undefined atau null untuk children Typography
                                    if (cellValue === null || cellValue === undefined) {
                                        cellValue = ''; // Atau bisa juga '-'
                                    }

                                    return (
                                        <td key={col.field + (row.id || index)} className={classes}>
                                            <Typography variant="small" color="blue-gray" className="font-normal">
                                                {String(cellValue)} {/* Pastikan dikonversi ke string jika ada angka 0 */}
                                            </Typography>
                                        </td>
                                    );
                                })}
                            </tr>
                        );
                    })}
                </tbody>
            </table>
            {/* Perbaikan untuk variant "caption" menjadi "small" */}
            <Typography variant="small" color="gray" className="mt-2 text-xs">
                Catatan: Fitur pagination, sorting, dan filtering lanjutan tidak tersedia pada tabel dasar ini.
            </Typography>
        </div>
    );
}


export default function DashboardAdminPage({
    mahasiswaData,
    dosenData,
    prodiData,
    adminData,
    mataKuliahData,
    migrationHistoryUsers,
    migrationHistoryMataKuliah,
    apiBaseUrl, // Prop dari AdminController untuk client-side API call
    flash // Flash message dari server (via Inertia)
}) {
    const [loading, setLoading] = useState({
        mahasiswa: false,
        dosen: false,
        prodi: false,
        admin: false,
        mataKuliah: false,
        // Anda bisa menambahkan state loading terpisah untuk tabel histori jika diperlukan
        // historyMahasiswa: false, 
        // historyDosen: false,
        // ...dst
    });
    const [localFlash, setLocalFlash] = useState(null); // Untuk flash message dari operasi client-side

    // Efek untuk menangani flash message dari server dan membersihkannya
    useEffect(() => {
        if (flash && flash.message) {
            setLocalFlash({ type: flash.type || 'info', message: flash.message });
            // Membersihkan flash prop dari Inertia agar tidak muncul lagi pada navigasi berikutnya
            // Ini adalah praktik umum, tetapi pastikan router.remember bekerja sesuai harapan Anda
            // atau gunakan cara lain untuk "consume" flash message.
            if (router.page && router.page.props && router.page.props.flash) {
                 router.remember(
                    { ...router.page.props, flash: null }, 
                    router.page.url, 
                    { replace: true, preserveState: true } // preserveState mungkin berguna di sini
                );
            }
        }
    }, [flash]);


    const [activeMainTab, setActiveMainTab] = useState("users");
    const [activeUserTab, setActiveUserTab] = useState("mahasiswa");

    // handleSync: Pilih salah satu implementasi di bawah ini
    // OPSI 1: Jika Laravel Controller yang melakukan fetch ke API Eksternal (Direkomendasikan)
    // (Kode SyncController.php Anda yang versi sebelumnya, yang ada Http::get())
    const handleSyncServerSide = (type) => {
        setLoading(prev => ({ ...prev, [type]: true }));
        setLocalFlash(null);
        let url = '';
        switch (type) {
            case 'mahasiswa': url = route('admin.sync.mahasiswa'); break;
            case 'dosen': url = route('admin.sync.dosen'); break;
            case 'prodi': url = route('admin.sync.prodi'); break;
            case 'admin': url = route('admin.sync.admin'); break;
            case 'mataKuliah': url = route('admin.sync.matakuliah'); break;
            default:
                setLoading(prev => ({ ...prev, [type]: false }));
                return;
        }
        router.post(url, {}, {
            onFinish: () => {
                setLoading(prev => ({ ...prev, [type]: false }));
                // Flash akan dihandle oleh useEffect
                // Jika perlu refresh data tabel:
                // router.reload({ only: ['mahasiswaData', 'migrationHistoryUsers'] }); // sesuaikan only array
            },
            preserveState: (page) => Object.keys(page.props.errors || {}).length > 0,
            preserveScroll: true,
        });
    };

    // OPSI 2: Jika React yang melakukan fetch ke API Eksternal (Kurang Direkomendasikan karena Keamanan)
    // (Kode SyncController.php Anda yang versi menerima data dari request)
    const handleSyncClientSide = async (type) => {
        setLoading(prev => ({ ...prev, [type]: true }));
        setLocalFlash(null);

        let externalApiUrl = '';
        let laravelSaveUrl = '';
        let dataKey = '';

        switch (type) {
            case 'mahasiswa':
                externalApiUrl = `${apiBaseUrl}/ujian/migrations/users/mahasiswa`;
                laravelSaveUrl = route('admin.sync.mahasiswa');
                dataKey = 'data';
                break;
            case 'mataKuliah':
                externalApiUrl = `${apiBaseUrl}/ujian/mata-kuliah/mahasiswa`;
                laravelSaveUrl = route('admin.sync.matakuliah');
                dataKey = 'kelas_kuliah';
                break;
            case 'dosen': case 'prodi': case 'admin':
                setLocalFlash({ type: 'info', message: `Sinkronisasi client-side untuk ${type} belum diimplementasikan.` });
                setLoading(prev => ({ ...prev, [type]: false })); return;
            default: setLoading(prev => ({ ...prev, [type]: false })); return;
        }

        if (!apiBaseUrl) {
            setLocalFlash({ type: 'error', message: 'API Base URL tidak dikonfigurasi.' });
            setLoading(prev => ({ ...prev, [type]: false })); return;
        }

        try {
            const response = await fetch(externalApiUrl, {
                method: 'GET',
                headers: { 'Accept': 'application/json', /* 'Authorization': 'Bearer ...' JIKA PERLU */ },
            });
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({ message: response.statusText }));
                throw new Error(`API Eksternal Error (${response.status}): ${errorData.message || 'Unknown error'}`);
            }
            const fetchedJson = await response.json();
            const dataToSync = fetchedJson[dataKey];

            if (!dataToSync || (Array.isArray(dataToSync) && dataToSync.length === 0)) {
                setLocalFlash({ type: 'info', message: `Tidak ada data ${type} baru dari API eksternal.` });
                setLoading(prev => ({ ...prev, [type]: false })); return;
            }

            router.post(laravelSaveUrl, { api_data: dataToSync }, {
                onFinish: () => setLoading(prev => ({ ...prev, [type]: false })),
                onError: (errors) => setLocalFlash({ type: 'error', message: `Gagal simpan data: ${Object.values(errors).join(', ')}` }),
                preserveState: (page) => Object.keys(page.props.errors || {}).length > 0,
                preserveScroll: true,
            });
        } catch (error) {
            console.error(`Error sinkronisasi ${type}:`, error);
            setLocalFlash({ type: 'error', message: `Error sinkronisasi ${type}: ${error.message}` });
            setLoading(prev => ({ ...prev, [type]: false }));
        }
    };
    
    // Pilih salah satu handleSync untuk digunakan:
    const handleSync = handleSyncServerSide; // Atau handleSyncClientSide

    const userColumns = [
        { field: 'id', headerName: 'ID Internal' },
        { field: 'external_id', headerName: 'ID Eksternal' },
        { field: 'created_at', headerName: 'Ditambahkan', valueGetter: (params) => params.row.created_at ? new Date(params.row.created_at).toLocaleString() : '' },
        { field: 'updated_at', headerName: 'Diperbarui', valueGetter: (params) => params.row.updated_at ? new Date(params.row.updated_at).toLocaleString() : '' },
    ];

    const mataKuliahColumns = [
        { field: 'id', headerName: 'ID Internal' },
        { field: 'external_id', headerName: 'ID Eksternal (mk_id)' },
        { field: 'nama', headerName: 'Nama Mata Kuliah' },
        { field: 'kode', headerName: 'Kode Mata Kuliah' },
        { field: 'created_at', headerName: 'Ditambahkan', valueGetter: (params) => params.row.created_at ? new Date(params.row.created_at).toLocaleString() : '' },
        { field: 'updated_at', headerName: 'Diperbarui', valueGetter: (params) => params.row.updated_at ? new Date(params.row.updated_at).toLocaleString() : '' },
    ];

    const migrationHistoryColumns = [
        { field: 'id', headerName: 'ID' },
        {
            field: 'type', headerName: 'Tipe Sinkronisasi',
            valueGetter: (params) => {
                if (params.row.is_mahasiswa) return 'Mahasiswa';
                if (params.row.is_dosen) return 'Dosen';
                if (params.row.is_prodi) return 'Prodi';
                if (params.row.is_admin) return 'Admin';
                if (params.row.is_mata_kuliah) return 'Mata Kuliah';
                return 'Tidak Diketahui';
            }
        },
        { field: 'created_at', headerName: 'Tanggal Sinkronisasi', valueGetter: (params) => params.row.created_at ? new Date(params.row.created_at).toLocaleString() : '' },
    ];
    
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
                disabled={loading[userType.toLowerCase()] || (userType.toLowerCase() !== 'mahasiswa' && userType.toLowerCase() !== 'matakuliah' && handleSync === handleSyncClientSide)} // Disable jika client-side dan bukan mhs/matkul
            >
                {!loading[userType.toLowerCase()] && <PowerIcon className="h-5 w-5" />}
                {loading[userType.toLowerCase()] ? `Sinkronisasi Berjalan...` : `Sinkronisasi Data ${userType}`}
            </Button>
            <Typography variant="h6" color="blue-gray" className="mt-4">Data {userType} Tersinkronisasi</Typography>
            <BasicTable 
                columns={columnsToUse} 
                rows={data || []} 
                isLoading={loading[userType.toLowerCase()]} 
            />
            <Typography variant="h6" color="blue-gray" className="mt-6">Histori Sinkronisasi {userType}</Typography>
            <BasicTable 
                columns={migrationHistoryColumns} 
                rows={historyData || []} 
            />
        </div>
    );

    return (
        <div className="flex justify-center w-full min-h-screen bg-gray-100 p-4">
            <div className="max-w-screen-2xl w-full">
                <Card className="m-0">
                    <CardBody className="p-4 md:p-6">
                        <Typography variant="h5" color="blue-gray" className="mb-6">
                            Manajemen Integrasi Data Eksternal
                        </Typography>

                        {(localFlash || (flash && flash.message)) && (
                            <Alert
                                color={(localFlash?.type || flash?.type) === 'success' ? 'green' : (localFlash?.type || flash?.type) === 'warning' ? 'amber' : (localFlash?.type || flash?.type) === 'error' ? 'red' : 'blue'}
                                icon={<InformationCircleIcon className="h-6 w-6" />}
                                className="mb-6"
                                open={true} 
                                // onClose hanya untuk localFlash agar tidak konflik jika ada flash dari server
                                onClose={localFlash && (!flash || !flash.message) ? () => setLocalFlash(null) : undefined} 
                            >
                                {localFlash?.message || (flash && flash.message)}
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
                                            <TabPanel value="mahasiswa" className="p-0 pt-4">{userTabContent("Mahasiswa", mahasiswaData, getFilteredHistory('mahasiswa'), userColumns)}</TabPanel>
                                            <TabPanel value="dosen" className="p-0 pt-4">{userTabContent("Dosen", dosenData, getFilteredHistory('dosen'), userColumns)}</TabPanel>
                                            <TabPanel value="prodi" className="p-0 pt-4">{userTabContent("Prodi", prodiData, getFilteredHistory('prodi'), userColumns)}</TabPanel>
                                            <TabPanel value="admin" className="p-0 pt-4">{userTabContent("Admin", adminData, getFilteredHistory('admin'), userColumns)}</TabPanel>
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
                                        <BasicTable columns={mataKuliahColumns} rows={mataKuliahData || []} isLoading={loading.mataKuliah}/>
                                        <Typography variant="h6" color="blue-gray" className="mt-6">Histori Sinkronisasi Mata Kuliah</Typography>
                                        <BasicTable columns={migrationHistoryColumns} rows={migrationHistoryMataKuliah || []} />
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