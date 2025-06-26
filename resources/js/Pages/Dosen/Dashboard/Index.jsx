import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, usePage } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";

// Impor ikon dari lucide-react
import { BookOpen, Users, FileText, BarChart3, Settings, Calendar } from 'lucide-react';

// Komponen Kartu Statistik (diletakkan di file yang sama untuk kemudahan)
const StatCard = ({ title, value, icon: Icon, iconColor }) => (
    <Card className="bg-white shadow-md hover:shadow-lg transition-shadow">
        <CardContent className="p-6">
            <div className="flex items-center justify-between">
                <div>
                    <p className="text-sm font-medium text-gray-600">{title}</p>
                    <p className={`text-3xl font-bold ${iconColor || 'text-gray-800'}`}>{value}</p>
                </div>
                <Icon className={`h-8 w-8 ${iconColor || 'text-gray-500'}`} />
            </div>
        </CardContent>
    </Card>
);

// Komponen Kartu Mata Kuliah (diletakkan di file yang sama untuk kemudahan)
const CourseCard = ({ course }) => {
    return (
        <Card className="bg-white shadow-md hover:shadow-xl transition-all duration-300 hover:-translate-y-1 flex flex-col">
            <CardHeader className="pb-3">
                <CardTitle className="text-lg font-bold text-gray-800 mb-1 line-clamp-2 h-14" title={course.nama}>
                    {course.nama}
                </CardTitle>
                <CardDescription className="text-gray-600">
                    {course.kode} • Semester {course.semester}
                </CardDescription>
            </CardHeader>

            <CardContent className="space-y-4 flex-grow">
                <div className="grid grid-cols-3 gap-4 text-center">
                    <div className="bg-blue-50 p-3 rounded-lg">
                        <div className="flex items-center justify-center mb-1">
                            <Users className="h-4 w-4 text-blue-600" />
                        </div>
                        <p className="text-xl font-bold text-blue-600">{course.students_count}</p>
                        <p className="text-xs text-gray-600">Mahasiswa</p>
                    </div>
                    <div className="bg-green-50 p-3 rounded-lg">
                        <div className="flex items-center justify-center mb-1">
                            <FileText className="h-4 w-4 text-green-600" />
                        </div>
                        <p className="text-xl font-bold text-green-600">{course.ujian_count}</p>
                        <p className="text-xs text-gray-600">Total Ujian</p>
                    </div>
                    <div className="bg-purple-50 p-3 rounded-lg">
                        <div className="flex items-center justify-center mb-1">
                            <BarChart3 className="h-4 w-4 text-purple-600" />
                        </div>
                        <p className="text-xl font-bold text-purple-600">{course.soal_count}</p>
                        <p className="text-xs text-gray-600">Bank Soal</p>
                    </div>
                </div>
            </CardContent>

            <CardFooter className="pt-4">
                {/* Arahkan ke rute yang akan kita buat nanti */}
                <Link href={route('dosen.matakuliah.show', course.id)} className="w-full"> 
                    <Button className="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium">
                        <Settings className="h-4 w-4 mr-2" />
                        Kelola Mata Kuliah
                    </Button>
                </Link>
            </CardFooter>
        </Card>
    );
};

export default function Index() {
    const { auth, dashboardData, stats } = usePage().props;

    return (
        // Kita akan asumsikan layout bisa di-override, jika tidak, kita bisa abaikan layout ini
        // dan langsung render kontennya di dalam <AuthenticatedLayout>
        <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 p-4">
            <Head title="Dashboard Dosen" />

            {/* Header Internal */}
            <div className="bg-gradient-to-r from-blue-600 to-indigo-700 text-white p-6 shadow-lg rounded-xl mb-8">
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-4">
                        <div className="bg-white/20 p-3 rounded-full">
                            <Users className="h-8 w-8" />
                        </div>
                        <div>
                            <h1 className="text-2xl font-bold">Dashboard Dosen</h1>
                            <p className="text-blue-100">{auth.user.name} {auth.user.gelar}</p>
                        </div>
                    </div>
                </div>
            </div>

            {/* Stats Overview */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <StatCard title="Total Mata Kuliah" value={stats.total_courses} icon={BookOpen} iconColor="text-blue-600" />
                <StatCard title="Total Mahasiswa" value={stats.total_students} icon={Users} iconColor="text-green-600" />
                <StatCard title="Total Ujian" value={stats.total_exams} icon={FileText} iconColor="text-orange-600" />
                <StatCard title="Total Bank Soal" value={stats.total_questions} icon={BarChart3} iconColor="text-purple-600" />
            </div>

            {/* Course Cards */}
            <div>
                <h2 className="text-2xl font-bold text-gray-800 mb-4">Mata Kuliah Aktif</h2>
                {dashboardData && dashboardData.length > 0 ? (
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        {dashboardData.map((course) => (
                            <CourseCard key={course.id} course={course} />
                        ))}
                    </div>
                ) : (
                    <div className="text-center p-12 bg-white rounded-lg shadow-md">
                        <p className="text-gray-500">Tidak ada mata kuliah yang diampu saat ini.</p>
                    </div>
                )}
            </div>
        </div>
    );
}
