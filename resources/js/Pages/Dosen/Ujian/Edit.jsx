import React, { useState, useEffect } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, usePage, router } from '@inertiajs/react';
import { Button, Card, Typography, Input, IconButton, Select, Option } from '@material-tailwind/react';
import { ArrowLeftIcon, ArrowRightIcon, TrashIcon } from '@heroicons/react/24/solid';

// Komponen Soal di Panel Kiri (Bank Soal)
function BankSoalItem({ soal, onAdd, isAdded }) {
    return (
        <div className={`flex items-center justify-between p-2 rounded-lg ${isAdded ? 'bg-gray-200' : 'hover:bg-gray-100'}`}>
            <div>
                <Typography variant="small" className="font-bold">{soal.kategori_soal}</Typography>
                <div className="text-sm font-normal" dangerouslySetInnerHTML={{ __html: soal.pertanyaan }} />
            </div>
            <IconButton color="blue" size="sm" onClick={() => onAdd(soal)} disabled={isAdded}>
                <ArrowRightIcon className="h-4 w-4" />
            </IconButton>
        </div>
    );
}

// Komponen Soal di Panel Kanan (Soal Ujian)
function UjianSoalItem({ soal, onRemove, onBobotChange }) {
    return (
        <div className="flex items-center justify-between p-2 border-b">
             <div className="text-sm font-normal flex-grow" dangerouslySetInnerHTML={{ __html: soal.pertanyaan }} />
             <div className="flex items-center gap-2 flex-shrink-0 ml-4">
                <Input type="number" label="Bobot" size="sm" value={soal.bobot_nilai_soal} onChange={e => onBobotChange(soal.id, e.target.value)} className="w-20" />
                <IconButton color="red" variant="text" size="sm" onClick={() => onRemove(soal.id)}>
                    <TrashIcon className="h-4 w-4" />
                </IconButton>
            </div>
        </div>
    );
}

export default function Edit() {
    const { ujian, soalTerpilih, bankSoal, kategoriOptions, filters } = usePage().props;

    const { data, setData, put, errors, processing } = useForm({
        soal_sync_data: Object.values(soalTerpilih),
    });

    const addSoalToUjian = (soal) => {
        // Cek agar tidak duplikat
        if (data.soal_sync_data.find(s => s.id === soal.id)) return;
        
        const newSoal = {
            id: soal.id,
            pertanyaan: soal.pertanyaan,
            tipe_soal: soal.tipe_soal,
            bobot_nilai_soal: 10, // Bobot default
        };
        setData('soal_sync_data', [...data.soal_sync_data, newSoal]);
    };

    const removeSoalFromUjian = (soalId) => {
        setData('soal_sync_data', data.soal_sync_data.filter(s => s.id !== soalId));
    };

    const handleBobotChange = (soalId, bobot) => {
        setData('soal_sync_data', data.soal_sync_data.map(s => 
            s.id === soalId ? { ...s, bobot_nilai_soal: bobot } : s
        ));
    };
    
    const handleFilterChange = (kategori) => {
        router.get(route('dosen.ujian.edit', ujian.id), { kategori }, {
            preserveState: true,
            replace: true,
        });
    };

    const submitSoal = (e) => {
        e.preventDefault();
        put(route('dosen.ujian.update', ujian.id), {
            preserveScroll: true,
        });
    };
    
    const soalTerpilihIds = data.soal_sync_data.map(s => s.id);

    return (
        <AuthenticatedLayout title={`Atur Soal Ujian: ${ujian.judul_ujian}`}>
            <Head title="Perakit Soal Ujian" />
            
            <form onSubmit={submitSoal}>
                <div className="flex justify-between items-center mb-4">
                    <Typography variant="h4">Perakit Soal: {ujian.judul_ujian}</Typography>
                    <Button type="submit" color="green" disabled={processing || data.soal_sync_data.length === 0}>Simpan Soal</Button>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {/* Panel Kiri: Bank Soal */}
                    <Card className="p-4 h-[70vh] flex flex-col">
                        <Typography variant="h6" className="mb-2">Bank Soal</Typography>
                        <div className="mb-4">
                            <Select label="Filter Kategori" value={filters.kategori || ''} onChange={handleFilterChange}>
                                <Option value="">Semua Kategori</Option>
                                {kategoriOptions.map(k => <Option key={k} value={k}>{k}</Option>)}
                            </Select>
                        </div>
                        <div className="overflow-y-auto flex-grow space-y-2">
                            {bankSoal.map(soal => (
                                <BankSoalItem key={soal.id} soal={soal} onAdd={addSoalToUjian} isAdded={soalTerpilihIds.includes(soal.id)} />
                            ))}
                        </div>
                    </Card>

                    {/* Panel Kanan: Soal Ujian */}
                    <Card className="p-4 h-[70vh] flex flex-col">
                         <Typography variant="h6" className="mb-2">Soal Terpilih untuk Ujian ({data.soal_sync_data.length})</Typography>
                         <div className="overflow-y-auto flex-grow">
                             {data.soal_sync_data.length > 0 ? (
                                data.soal_sync_data.map(soal => (
                                    <UjianSoalItem key={soal.id} soal={soal} onRemove={removeSoalFromUjian} onBobotChange={handleBobotChange} />
                                ))
                             ) : (
                                <div className="text-center p-8 text-gray-500">Pilih soal dari panel kiri untuk ditambahkan ke ujian ini.</div>
                             )}
                         </div>
                    </Card>
                </div>
            </form>
        </AuthenticatedLayout>
    );
}