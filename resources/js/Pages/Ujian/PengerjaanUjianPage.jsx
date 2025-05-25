// resources/js/Pages/Ujian/PengerjaanUjianPage.jsx
import React, { useState, useEffect, useMemo } from 'react';
import {
  Typography, Button, Radio, Textarea, Card, IconButton, Progress, Chip, Dialog, DialogHeader, DialogBody, DialogFooter
} from "@material-tailwind/react";
import {
  ArrowLeftIcon, ArrowRightIcon, ListBulletIcon, XMarkIcon, ClockIcon, FlagIcon, CheckCircleIcon
} from "@heroicons/react/24/solid";
import { router, usePage, Head } from '@inertiajs/react';

// --- Komponen NavigasiSoalSidebar (tidak ada perubahan signifikan dari kode Anda) ---
function NavigasiSoalSidebar({ soalList, soalSekarangIndex, setSoalSekarangIndex, jawabanUser, statusRaguRagu, isOpen, toggleSidebar }) {
    // ... (kode NavigasiSoalSidebar Anda, pastikan menggunakan statusRaguRagu dengan benar)
    return (
    <>
      {isOpen && (<div className="fixed inset-0 z-50 bg-black/30 md:hidden" onClick={toggleSidebar} aria-label="close sidebar"></div>)}
      <div className={`fixed top-0 right-0 h-full w-72 sm:w-80 bg-white shadow-xl transition-transform duration-300 ease-in-out z-[60] border-l border-blue-gray-100 ${isOpen ? "translate-x-0" : "translate-x-full"} flex flex-col`}>
        <div className="flex justify-between items-center p-4 border-b border-blue-gray-100">
          <Typography variant="h6" color="blue-gray">Navigasi Soal</Typography>
          <IconButton variant="text" color="blue-gray" onClick={toggleSidebar}><XMarkIcon className="h-5 w-5" /></IconButton>
        </div>
        <div className="flex-grow p-4 grid grid-cols-4 sm:grid-cols-5 gap-2 overflow-y-auto">
          {soalList.map((soal, index) => {
            const sudahDijawab = jawabanUser[soal.id] !== null && jawabanUser[soal.id] !== "";
            const isRagu = statusRaguRagu[soal.id]; // Gunakan state ragu-ragu yang sudah dikelola
            return (
              <Button key={soal.id} variant={soalSekarangIndex === index ? "filled" : (sudahDijawab ? "gradient" : "outlined")}
                color={soalSekarangIndex === index ? "blue" : (sudahDijawab ? (isRagu ? "amber" : "green") : "blue-gray")}
                size="sm" className="aspect-square p-0 text-xs relative !min-w-[unset] !w-full"
                onClick={() => { setSoalSekarangIndex(index); if (isOpen && window.innerWidth < 768) toggleSidebar(); }}
                title={isRagu ? "Soal Ragu-ragu" : (sudahDijawab ? "Sudah Dijawab" : "Belum Dijawab")}>
                {isRagu && <FlagIcon className="h-3 w-3 text-white absolute top-0.5 right-0.5" />}
                {soal.nomor}
              </Button>
            );
          })}
        </div>
      </div>
    </>
  );
}

export default function PengerjaanUjianPage() {
  const { props } = usePage();
  const idUjianDariProps = props.idUjianAktif;
  
  // Gunakan detailUjianProp dari props jika ada, jika tidak fallback ke data statis internal
  const detailUjian = useMemo(() => {
    if (props.detailUjianProp) {
      console.log("Menggunakan detail ujian dari props:", props.detailUjianProp.judulUjian);
      return props.detailUjianProp;
    }
    console.warn("Menggunakan detail ujian statis fallback di PengerjaanUjianPage.jsx. Pastikan prop 'detailUjianProp' dikirim dari controller.");
    // Data statis fallback jika tidak ada dari props (sebaiknya dihindari untuk produksi)
    return {
      id: idUjianDariProps || 999, // Gunakan ID dari props jika ada, atau ID default
      namaMataKuliah: "Mata Kuliah Contoh (Fallback)",
      judulUjian: `Ujian Contoh (ID: ${idUjianDariProps || 999})`,
      durasiTotalDetik: 10 * 60, // 10 menit fallback
      soalList: [
        { id: 1, nomor: 1, tipe: "pilihan_ganda", pertanyaan: "Ini adalah pertanyaan fallback 1.", opsi: ["A", "B", "C", "D"], jawabanUser: null, raguRagu: false, kunciJawaban: "A" },
        { id: 2, nomor: 2, tipe: "esai", pertanyaan: "Ini adalah pertanyaan esai fallback.", jawabanUser: "", raguRagu: false, kunciJawaban: "Jawaban esai fallback." },
      ],
    };
  }, [props.detailUjianProp, idUjianDariProps]);

  const [soalSekarangIndex, setSoalSekarangIndex] = useState(0);
  const [jawabanUser, setJawabanUser] = useState(() => {
    const initialAnswers = {};
    detailUjian.soalList.forEach(soal => { initialAnswers[soal.id] = soal.jawabanUser || (soal.tipe === "pilihan_ganda" ? null : ""); });
    return initialAnswers;
  });
  const [statusRaguRagu, setStatusRaguRagu] = useState(() => {
    const initialRagu = {};
    detailUjian.soalList.forEach(soal => { initialRagu[soal.id] = soal.raguRagu || false; });
    return initialRagu;
  });
  const [sisaWaktuDetik, setSisaWaktuDetik] = useState(detailUjian.durasiTotalDetik || 10 * 60); // Fallback durasi
  const [navigasiSoalOpen, setNavigasiSoalOpen] = useState(false);
  const [openDialogSelesai, setOpenDialogSelesai] = useState(false);

  const soalSekarang = detailUjian.soalList[soalSekarangIndex];

  const handleSelesaiUjianCallback = React.useCallback(() => {
    setOpenDialogSelesai(false);
    console.log("Data Ujian yang Dikumpulkan (Simulasi):", { jawabanUser, statusRaguRagu, ujianId: detailUjian.id });
    // Mengarahkan ke halaman konfirmasi dengan ID ujian yang baru saja selesai
    router.visit(route('ujian.selesai.konfirmasi', { id_ujian: detailUjian.id }));
  }, [jawabanUser, statusRaguRagu, detailUjian.id]);

  useEffect(() => {
    if (sisaWaktuDetik <= 0) {
      handleSelesaiUjianCallback(true); 
      return;
    }
    const timer = setInterval(() => { setSisaWaktuDetik(prev => prev - 1); }, 1000);
    return () => clearInterval(timer);
  }, [sisaWaktuDetik, handleSelesaiUjianCallback]);

  const formatWaktu = (totalDetik) => { /* ... (sama) ... */ };
  const handlePilihJawaban = (idSoal, jawaban) => { /* ... (sama) ... */ };
  const handleTandaiRaguRagu = () => { /* ... (sama) ... */ };
  const handleSoalSebelumnya = () => { /* ... (sama) ... */ };
  const handleSoalBerikutnya = () => { /* ... (sama) ... */ };
  
  const progresPersen = detailUjian.soalList.length > 0 ? ((soalSekarangIndex + 1) / detailUjian.soalList.length) * 100 : 0;
  const soalListDenganStatusRagu = detailUjian.soalList.map(soal => ({ ...soal, raguRagu: statusRaguRagu[soal.id] || false }));

  if (!soalSekarang) { // Fallback jika soalList kosong atau index salah
    return (
        <div className="min-h-screen bg-blue-gray-50 flex flex-col items-center justify-center">
            <Head title="Error Memuat Ujian" />
            <Typography variant="h4" color="red">Error</Typography>
            <Typography color="blue-gray">Detail soal tidak dapat dimuat.</Typography>
            <Link href={route('dashboard')}>
                <Button color="blue" className="mt-4">Kembali ke Dashboard</Button>
            </Link>
        </div>
    );
  }

  return (
    <div className="min-h-screen bg-blue-gray-50 flex flex-col">
      <Head title={`Mengerjakan: ${detailUjian.judulUjian}`} />
      <header className="sticky top-0 z-30 bg-white shadow px-4 py-3 w-full">
        <div className="flex items-center justify-between">
          <div>
            <Typography variant="small" color="blue-gray" className="font-normal opacity-75">{detailUjian.namaMataKuliah}</Typography>
            <Typography variant="h6" color="blue-gray">{detailUjian.judulUjian}</Typography>
          </div>
          <div className="flex items-center gap-2 sm:gap-4">
            <Chip value={`Soal ${soalSekarang.nomor || (soalSekarangIndex + 1)}/${detailUjian.soalList.length}`} variant="ghost" className="hidden sm:inline-block"/>
            <Chip icon={<ClockIcon className="h-4 w-4"/>} value={formatWaktu(sisaWaktuDetik)} color={sisaWaktuDetik < (5 * 60) ? "red" : "green"} variant="ghost"/>
            <IconButton variant="text" color="blue-gray" onClick={() => setNavigasiSoalOpen(prev => !prev)}>
              <ListBulletIcon className="h-6 w-6" />
            </IconButton>
          </div>
        </div>
        {detailUjian.soalList.length > 0 && <Progress value={progresPersen} color="blue" size="sm" className="mt-2 absolute bottom-0 left-0 right-0 rounded-none" />}
      </header>

      <div className="flex-grow w-full flex flex-row overflow-hidden relative">
        <main className="flex-grow p-4 sm:p-6 md:p-8 overflow-y-auto">
          {/* ... (Isi Main: Card Soal, Tombol Navigasi Soal) sama seperti kode Anda ... */}
          <Card className="p-6 mb-6 shadow">
            <div className="flex justify-between items-center">
                <Typography variant="h6" color="blue-gray" className="mb-1">Soal No. {soalSekarang.nomor || (soalSekarangIndex + 1)}</Typography>
                {statusRaguRagu[soalSekarang.id] && <Chip value="Ragu-ragu" color="amber" size="sm" icon={<FlagIcon className="h-3 w-3"/>} />}
            </div>
            <hr className="my-2 border-blue-gray-100" />
            <Typography variant="paragraph" className="mb-6 whitespace-pre-line">{soalSekarang.pertanyaan}</Typography>
            {soalSekarang.tipe === "pilihan_ganda" && (
              <div className="flex flex-col gap-2">
                {soalSekarang.opsi.map((opsi, index) => (
                  <Radio key={index} id={`opsi-${soalSekarang.id}-${index}`} name={`soal-${soalSekarang.id}`}
                    label={<Typography color="blue-gray" className="font-normal">{String.fromCharCode(65 + index)}. {opsi}</Typography>}
                    value={opsi} checked={jawabanUser[soalSekarang.id] === opsi}
                    onChange={() => handlePilihJawaban(soalSekarang.id, opsi)} ripple={true} className="hover:before:opacity-0"
                    containerProps={{ className: "p-0 -ml-2" }} labelProps={{className: "ml-2"}}
                  />
                ))}
              </div>
            )}
            {soalSekarang.tipe === "esai" && (
              <Textarea label="Jawaban Anda" value={jawabanUser[soalSekarang.id] || ""} onChange={(e) => handlePilihJawaban(soalSekarang.id, e.target.value)} rows={5}/>
            )}
          </Card>
          <div className="flex flex-col sm:flex-row items-center justify-between gap-3 mt-8">
             <Button variant="text" color="blue-gray" onClick={handleSoalSebelumnya} disabled={soalSekarangIndex === 0} className="flex items-center gap-2">
              <ArrowLeftIcon className="h-5 w-5" /> Soal Sebelumnya
            </Button>
            <Button variant={statusRaguRagu[soalSekarang.id] ? "filled" : "outlined"} color="amber" onClick={handleTandaiRaguRagu} className="flex items-center gap-2">
              <FlagIcon className="h-5 w-5"/> Ragu-ragu
            </Button>
            {soalSekarangIndex === detailUjian.soalList.length - 1 ? (
              <Button color="green" onClick={() => setOpenDialogSelesai(true)} className="flex items-center gap-2">
                <CheckCircleIcon className="h-5 w-5"/> Selesai Ujian
              </Button>
            ) : (
              <Button color="blue" onClick={handleSoalBerikutnya} className="flex items-center gap-2">
                Soal Berikutnya <ArrowRightIcon className="h-5 w-5" />
              </Button>
            )}
          </div>
        </main>
        <NavigasiSoalSidebar soalList={soalListDenganStatusRagu} soalSekarangIndex={soalSekarangIndex} setSoalSekarangIndex={setSoalSekarangIndex} jawabanUser={jawabanUser} statusRaguRagu={statusRaguRagu} isOpen={navigasiSoalOpen} toggleSidebar={() => setNavigasiSoalOpen(prev => !prev)}/>
      </div>
      <Dialog open={openDialogSelesai} handler={() => setOpenDialogSelesai(false)} size="xs">
        <DialogHeader>Konfirmasi Selesai Ujian</DialogHeader>
        <DialogBody divider>Apakah Anda yakin ingin menyelesaikan ujian ini? Jawaban tidak bisa diubah lagi.</DialogBody>
        <DialogFooter>
          <Button variant="text" color="blue-gray" onClick={() => setOpenDialogSelesai(false)} className="mr-1"><span>Batal</span></Button>
          <Button variant="gradient" color="green" onClick={() => handleSelesaiUjianCallback()}><span>Ya, Kumpulkan</span></Button>
        </DialogFooter>
      </Dialog>
    </div>
  );
}