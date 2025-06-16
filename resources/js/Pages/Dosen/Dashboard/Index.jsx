// File: resources/js/Pages/Dosen/Dashboard/Index.jsx

import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, usePage } from '@inertiajs/react';
import { Typography } from '@material-tailwind/react'; // Anda bisa menggantinya jika tidak memakai Material Tailwind
import { SubjectCard } from '@/Components/Dosen/Dashboard/SubjectCard';
import { StudentsDialog } from '@/Components/Dosen/Dashboard/StudentsDialog';

export default function Index() {
    // Ambil `dashboardData` dari props yang dikirim oleh controller
    const { auth, dashboardData } = usePage().props;

    // State untuk mengontrol dialog mahasiswa
    const [selectedSubject, setSelectedSubject] = useState(null);

    const handleShowStudents = (subject) => {
        setSelectedSubject(subject);
    };

    const handleDialogStateChange = (open) => {
        if (!open) {
            setSelectedSubject(null);
        }
    };

    return (
        <AuthenticatedLayout user={auth.user} title="Dashboard Dosen">
            <Head title="Dashboard Dosen" />

            <div className="mb-8">
                <h1 className="text-3xl font-bold tracking-tight">Dashboard Pelaporan</h1>
                <p className="text-muted-foreground mt-1">
                    Selamat datang! Berikut adalah ringkasan performa ujian dari mata kuliah yang Anda ampu.
                </p>
            </div>

            <main>
                {dashboardData && dashboardData.length > 0 ? (
                    <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        {dashboardData.map((subject) => (
                            <SubjectCard
                                key={subject.id}
                                subject={subject}
                                onShowStudents={handleShowStudents}
                            />
                        ))}
                    </div>
                ) : (
                    <div className="text-center py-12">
                        <Typography color="gray" className="font-normal">
                            Belum ada data pengerjaan ujian yang dapat ditampilkan.
                        </Typography>
                    </div>
                )}
            </main>

            <StudentsDialog
                subject={selectedSubject}
                open={!!selectedSubject}
                onOpenChange={handleDialogStateChange}
            />
        </AuthenticatedLayout>
    );
}