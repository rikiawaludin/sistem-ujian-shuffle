// resources/js/Pages/Ujian/KonfirmasiSelesaiUjianPage.jsx
import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardBody, Typography, Button } from "@material-tailwind/react";
import { CheckCircleIcon } from "@heroicons/react/24/solid";
import { Link, usePage, Head } from '@inertiajs/react';

export default function KonfirmasiSelesaiUjianPage() {
  const { namaUjian, namaMataKuliah } = usePage().props;

  const ujianYangSelesai = namaUjian || "Ujian Anda";
  const mataKuliahTerkait = namaMataKuliah || "mata kuliah terkait";

  return (
    <AuthenticatedLayout title="Konfirmasi Ujian Selesai">
      <Head title="Ujian Telah Selesai" />
      <div className="flex flex-col items-center justify-center min-h-[calc(100vh-200px)] text-center p-4">
        <Card className="w-full max-w-lg p-6 sm:p-8 shadow-2xl border border-blue-gray-50">
          <CardBody>
            <div className="mb-6 flex justify-center">
              <CheckCircleIcon className="h-20 w-20 text-green-500" />
            </div>
            <Typography variant="h4" color="blue-gray" className="mb-2 font-semibold">
              {ujianYangSelesai} Telah Berhasil Dikumpulkan!
            </Typography>
            <Typography color="blue-gray" className="mb-6 font-normal opacity-80">
              Terima kasih telah menyelesaikan ujian untuk mata kuliah {mataKuliahTerkait}.
            </Typography>
            <Typography color="blue-gray" className="mb-8 font-normal text-sm opacity-70">
              Anda dapat melihat hasil ujian (jika penilaian sudah tersedia) di halaman{" "}
              <Link 
                href={route().has('ujian.riwayat') ? route('ujian.riwayat') : '#'}
                className="text-blue-600 hover:text-blue-800 hover:underline font-medium"
              >
                Histori Ujian
              </Link>
              .
            </Typography>
            <div className="flex flex-col sm:flex-row justify-center gap-4">
              <Link href={route('home')} className="w-full sm:w-auto">
                <Button color="blue" variant="gradient" fullWidth className="sm:w-auto">
                  Kembali ke Home
                </Button>
              </Link>
              {route().has('matakuliah.index') && (
                <Link href={route('matakuliah.index')} className="w-full sm:w-auto">
                  <Button color="blue-gray" variant="outlined" fullWidth className="sm:w-auto">
                    Ke Daftar Mata Kuliah
                  </Button>
                </Link>
              )}
            </div>
          </CardBody>
        </Card>
      </div>
    </AuthenticatedLayout>
  );
}