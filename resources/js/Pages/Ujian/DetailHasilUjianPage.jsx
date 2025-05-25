// resources/js/Pages/Ujian/DetailHasilUjianPage.jsx
import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Typography, Card, CardBody, Button, IconButton, Chip } from "@material-tailwind/react";
// Tambahkan semua ikon yang digunakan ke dalam impor ini:
import { ArrowLeftIcon, CheckIcon, XMarkIcon, InformationCircleIcon, AcademicCapIcon, CalendarDaysIcon, ClockIcon as ClockSolidIcon } from "@heroicons/react/24/solid";
import { Link, usePage, Head, router } from '@inertiajs/react';

// Sub-komponen untuk menampilkan setiap item soal dan jawaban
function SoalReviewItem({ soal, nomorUrut }) {
  const getOptionLetter = (index) => String.fromCharCode(65 + index);

  return (
    <Card className="mb-6 border border-blue-gray-100 shadow-sm">
      <CardBody className="p-5">
        <div className="flex justify-between items-start mb-3">
          <Typography variant="h6" color="blue-gray" className="font-semibold">
            Soal No. {soal.nomorSoal || nomorUrut}
          </Typography>
          {/* Penggunaan CheckIcon dan XMarkIcon sudah benar di sini karena sudah diimpor di atas */}
          {soal.isBenar === true && <Chip color="green" value="Benar" size="sm" icon={<CheckIcon className="h-4 w-4 stroke-white stroke-2"/>} className="text-white"/>}
          {soal.isBenar === false && <Chip color="red" value="Salah" size="sm" icon={<XMarkIcon className="h-4 w-4 stroke-white stroke-2"/>} className="text-white"/>}
          {soal.isBenar === null && soal.tipeSoal === "esai" && <Chip color="amber" value="Perlu Penilaian Manual" size="sm" />}
        </div>
        <Typography variant="paragraph" color="blue-gray" className="mb-4 whitespace-pre-line leading-relaxed">
          {soal.pertanyaan}
        </Typography>

        {soal.tipeSoal === "pilihan_ganda" && soal.opsiJawaban && (
          <div className="space-y-2 mb-4">
            <Typography variant="small" className="font-semibold text-blue-gray-700">Opsi Jawaban:</Typography>
            {soal.opsiJawaban.map((opsi, index) => (
              <div 
                key={index} 
                className={`p-3 rounded-lg text-sm border flex items-start gap-2
                  ${opsi === soal.kunciJawaban ? 
                    "bg-green-50 border-green-300 text-green-700" : 
                    (opsi === soal.jawabanPengguna ? "bg-red-50 border-red-300 text-red-700" : "border-blue-gray-200 bg-blue-gray-50/50 text-blue-gray-700")
                  }
                  ${opsi === soal.jawabanPengguna && opsi === soal.kunciJawaban ? "font-bold" : ""}
                `}
              >
                <span className={`font-medium mr-2 ${opsi === soal.kunciJawaban || opsi === soal.jawabanPengguna ? 'text-inherit' : 'text-blue-gray-800'}`}>
                  {getOptionLetter(index)}.
                </span>
                <span className="flex-1">{opsi}</span>
                {opsi === soal.jawabanPengguna && <Chip value="Jawaban Anda" size="sm" variant="ghost" className={`ml-auto ${opsi === soal.kunciJawaban ? "text-green-700 !bg-green-100/50" : "text-red-700 !bg-red-100/50" }`} />}
                {opsi === soal.kunciJawaban && opsi !== soal.jawabanPengguna && <Chip value="Kunci" size="sm" variant="ghost" color="green" className="ml-auto !bg-green-100/50" />}
              </div>
            ))}
          </div>
        )}

        {soal.tipeSoal === "esai" && (
          <>
            <div className="mb-3">
              <Typography variant="small" className="font-semibold text-blue-gray-700 mb-1">Jawaban Anda:</Typography>
              <Card variant="outlined" className="p-3 bg-blue-gray-50/70 border-blue-gray-200">
                <Typography variant="paragraph" className="text-sm text-blue-gray-800 whitespace-pre-line">
                  {soal.jawabanPengguna || "- Tidak Dijawab -"}
                </Typography>
              </Card>
            </div>
            {soal.kunciJawaban && (
              <div className="mb-4">
                  <Typography variant="small" className="font-semibold text-green-700 mb-1">Kriteria/Kunci Jawaban Esai:</Typography>
                  <Card variant="outlined" className="p-3 bg-green-50 border-green-200">
                    <Typography variant="paragraph" className="text-sm text-green-800 whitespace-pre-line">
                      {soal.kunciJawaban}
                    </Typography>
                  </Card>
              </div>
            )}
          </>
        )}

        {soal.penjelasan && (
          <div className="mt-4 p-4 bg-sky-50 border border-sky-200 rounded-lg">
            <div className="flex items-center gap-2 mb-1">
                {/* InformationCircleIcon juga sudah diimpor */}
                <InformationCircleIcon className="h-5 w-5 text-sky-700"/>
                <Typography variant="small" color="blue-gray" className="font-semibold">Penjelasan:</Typography>
            </div>
            <Typography variant="small" color="blue-gray" className="font-normal whitespace-pre-line">
              {soal.penjelasan}
            </Typography>
          </div>
        )}
      </CardBody>
    </Card>
  );
}


export default function DetailHasilUjianPage() {
  const { hasilUjian } = usePage().props;

  const handleKembali = () => {
    if (route().has('ujian.riwayat')) {
      router.get(route('ujian.riwayat'));
    } else if (route().has('dashboard')) {
      router.get(route('dashboard'));
    } else {
      window.history.back();
    }
  };

  if (!hasilUjian || !hasilUjian.detailSoalJawaban) {
    return (
      <AuthenticatedLayout title="Error Hasil Ujian">
        <Head title="Error Hasil Ujian" />
        <div className="flex flex-col items-center justify-center min-h-[calc(100vh-200px)] p-4">
          <Card className="p-8 text-center">
            <Typography variant="h4" color="red" className="mb-4">Error Memuat Detail Ujian</Typography>
            <Typography color="blue-gray">Maaf, detail hasil ujian tidak dapat ditemukan atau tidak lengkap.</Typography>
            <Button onClick={() => router.get(route('dashboard'))} color="blue" className="mt-6">
              Kembali ke Dashboard
            </Button>
          </Card>
        </div>
      </AuthenticatedLayout>
    );
  }

  const lulus = hasilUjian.skorTotal >= hasilUjian.kkm;

  return (
    <AuthenticatedLayout title={`Hasil Ujian: ${hasilUjian.judulUjian}`}>
      <Head title={`Hasil Ujian: ${hasilUjian.judulUjian}`} />

      <div className="flex items-center justify-between mb-6">
        <div className="flex items-center">
          {/* ArrowLeftIcon sudah diimpor */}
          <IconButton variant="text" color="blue-gray" onClick={handleKembali} className="mr-3">
            <ArrowLeftIcon strokeWidth={2.5} className="h-5 w-5" />
          </IconButton>
          <Typography variant="h4" color="blue-gray" className="font-bold">
            Detail Hasil Ujian
          </Typography>
        </div>
      </div>

      <Card className="mb-8 shadow-lg border border-blue-gray-100">
        <CardBody className="p-6">
          <Typography variant="h5" color="blue-gray" className="mb-1 font-semibold">{hasilUjian.judulUjian}</Typography>
          <div className="flex items-center text-sm text-blue-gray-600 mb-4">
            {/* AcademicCapIcon sudah diimpor */}
            <AcademicCapIcon className="h-5 w-5 mr-1.5 opacity-80"/> {hasilUjian.namaMataKuliah}
          </div>
          
          <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-x-6 gap-y-4 text-sm mb-4 border-t border-blue-gray-100 pt-4">
            <div>
              <Typography color="gray" className="font-normal text-xs uppercase">Tanggal:</Typography>
              <Typography color="blue-gray" className="font-medium">{hasilUjian.tanggalPengerjaan}</Typography>
            </div>
            <div>
              <Typography color="gray" className="font-normal text-xs uppercase">Durasi Pengerjaan:</Typography>
              <Typography color="blue-gray" className="font-medium">{hasilUjian.waktuDihabiskan}</Typography>
            </div>
            <div>
              <Typography color="gray" className="font-normal text-xs uppercase">KKM:</Typography>
              <Typography color="blue-gray" className="font-medium">{hasilUjian.kkm}</Typography>
            </div>
            <div>
              <Typography color="gray" className="font-normal text-xs uppercase">Skor Anda:</Typography>
              <Typography variant="h5" color={lulus ? "green" : "red"} className="font-bold">
                {hasilUjian.skorTotal}
              </Typography>
            </div>
            <div className="col-span-2 sm:col-span-1">
              <Typography color="gray" className="font-normal text-xs uppercase">Status Kelulusan:</Typography>
              <Chip 
                value={hasilUjian.statusKelulusan} 
                color={lulus ? "green" : "red"} 
                size="sm" 
                className="mt-1 capitalize"
                // CheckIcon dan XMarkIcon sudah diimpor
                icon={lulus ? <CheckIcon className="h-4 w-4 stroke-white stroke-2"/> : <XMarkIcon className="h-4 w-4 stroke-white stroke-2"/>}
              />
            </div>
            <div className="col-span-full sm:col-span-2 md:col-span-3 lg:col-span-2 border-t border-blue-gray-50 pt-3 mt-3 md:border-t-0 md:pt-0 md:mt-0">
                <Typography color="gray" className="font-normal text-xs uppercase">Rincian Jawaban:</Typography>
                <div className="flex gap-x-4">
                    <Typography color="green" className="font-medium">Benar: {hasilUjian.jumlahSoalBenar}</Typography>
                    <Typography color="red" className="font-medium">Salah: {hasilUjian.jumlahSoalSalah}</Typography>
                    <Typography color="blue-gray" className="font-medium">Tidak Dijawab: {hasilUjian.jumlahSoalTidakDijawab}</Typography>
                </div>
            </div>
          </div>
        </CardBody>
      </Card>

      <div>
        <Typography variant="h5" color="blue-gray" className="mb-5 font-semibold">
          Pembahasan Jawaban
        </Typography>
        {hasilUjian.detailSoalJawaban && hasilUjian.detailSoalJawaban.length > 0 ? (
          <div className="space-y-6">
            {hasilUjian.detailSoalJawaban.map((soal, index) => (
              <SoalReviewItem key={soal.idSoal} soal={soal} nomorUrut={index + 1} />
            ))}
          </div>
        ) : (
          <Card className="p-6 text-center shadow">
            <Typography color="gray">Detail pembahasan jawaban tidak tersedia.</Typography>
          </Card>
        )}
      </div>

      <div className="mt-10 flex justify-center">
        <Button color="blue-gray" variant='outlined' onClick={handleKembali} className="flex items-center gap-2">
          {/* ArrowLeftIcon sudah diimpor */}
          <ArrowLeftIcon className="h-5 w-5"/>
          Kembali
        </Button>
      </div>
    </AuthenticatedLayout>
  );
}