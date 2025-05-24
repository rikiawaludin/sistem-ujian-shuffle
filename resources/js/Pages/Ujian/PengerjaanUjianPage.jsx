import React, { useState, useEffect, useMemo } from 'react';
// HAPUS: import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {
  Typography, Button, Radio, Textarea, Card, IconButton, Progress, Chip, Dialog, DialogHeader, DialogBody, DialogFooter
} from "@material-tailwind/react";
import {
  ArrowLeftIcon, ArrowRightIcon, ListBulletIcon, XMarkIcon, ClockIcon, FlagIcon, CheckCircleIcon
} from "@heroicons/react/24/solid";
import { router, usePage, Head } from '@inertiajs/react'; // Tambahkan Head untuk title

// --- Data Statis Ujian (sama seperti sebelumnya) ---
const detailUjianStatis = {
  id: 201,
  namaMataKuliah: "Fisika Mekanika Lanjutan",
  judulUjian: "Simulasi Ujian Akhir Semester",
  durasiTotalDetik: 30 * 60, // 30 menit
  soalList: [
    { id: 1, nomor: 1, tipe: "pilihan_ganda", pertanyaan: "Sebuah partikel bergerak melingkar dengan kecepatan sudut konstan. Manakah pernyataan berikut yang BENAR mengenai percepatan sentripetalnya?", opsi: ["Besarnya konstan dan arahnya menuju pusat lingkaran", "Besarnya konstan dan arahnya menjauhi pusat lingkaran", "Besarnya berubah dan arahnya menuju pusat lingkaran", "Besarnya berubah dan arahnya menjauhi pusat lingkaran"], jawabanUser: null, raguRagu: false },
    { id: 2, nomor: 2, tipe: "pilihan_ganda", pertanyaan: "Sebuah benda jatuh bebas dari ketinggian H. Jika percepatan gravitasi adalah g, waktu yang dibutuhkan benda untuk mencapai tanah adalah...", opsi: ["√(2H/g)", "√(H/g)", "2H/g", "H/g"], jawabanUser: null, raguRagu: false },
    { id: 3, nomor: 3, tipe: "esai", pertanyaan: "Jelaskan prinsip dasar dari Teorema Usaha-Energi dan berikan satu contoh aplikasinya dalam kehidupan sehari-hari!", jawabanUser: "", raguRagu: false },
  ],
};
// --- Akhir Data Statis Ujian ---

// --- Komponen Navigasi Soal (Sidebar Kanan Drawer Style) ---
function NavigasiSoalSidebar({
  soalList,
  soalSekarangIndex,
  setSoalSekarangIndex,
  jawabanUser,
  statusRaguRagu, // Tambahkan prop ini
  isOpen,
  toggleSidebar,
}) {
  return (
    <>
      {/* Overlay untuk menutup sidebar saat diklik di luar (mobile) */}
      {isOpen && (
        <div
          className="fixed inset-0 z-50 bg-black/30 md:hidden"
          onClick={toggleSidebar}
          aria-label="close sidebar"
        ></div>
      )}

      {/* Konten Sidebar */}
      <div
        className={`fixed top-0 right-0 h-full w-72 sm:w-80 bg-white shadow-xl transition-transform duration-300 ease-in-out z-[60] border-l border-blue-gray-100
                   ${isOpen ? "translate-x-0" : "translate-x-full"} 
                   flex flex-col`}
        // Untuk desktop, kita bisa buat dia selalu visible atau toggleable juga.
        // Jika ingin selalu visible di desktop dan hanya drawer di mobile:
        // md:translate-x-0 md:relative md:h-auto md:shadow-none md:border-l md:flex-shrink-0
      >
        <div className="flex justify-between items-center p-4 border-b border-blue-gray-100">
          <Typography variant="h6" color="blue-gray">Navigasi Soal</Typography>
          <IconButton variant="text" color="blue-gray" onClick={toggleSidebar}>
            <XMarkIcon className="h-5 w-5" />
          </IconButton>
        </div>
        <div className="flex-grow p-4 grid grid-cols-5 gap-2 overflow-y-auto">
          {soalList.map((soal, index) => {
            const sudahDijawab = jawabanUser[soal.id] !== null && jawabanUser[soal.id] !== "";
            const isRagu = statusRaguRagu[soal.id]; // Gunakan state ragu-ragu dari parent
            return (
              <Button
                key={soal.id}
                variant={soalSekarangIndex === index ? "filled" : (sudahDijawab ? "gradient" : "outlined")}
                color={soalSekarangIndex === index ? "blue" : (sudahDijawab ? (isRagu ? "amber" : "green") : "blue-gray")}
                size="sm"
                className="aspect-square p-0 text-xs relative !min-w-[unset] !w-full" // Style agar tombol responsif dan kotak
                onClick={() => { setSoalSekarangIndex(index); if (isOpen && window.innerWidth < 768) toggleSidebar(); }} // Tutup sidebar di mobile setelah klik
                title={isRagu ? "Soal Ragu-ragu" : (sudahDijawab ? "Sudah Dijawab" : "Belum Dijawab")}
              >
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
// --- Akhir Komponen Navigasi Soal ---


export default function PengerjaanUjianPage() {
  const { props } = usePage();
  const idUjianDariProps = props.idUjianAktif;

  const detailUjian = useMemo(() => {
    console.log("ID Ujian dari Props (jika ada):", idUjianDariProps);
    return detailUjianStatis;
  }, [idUjianDariProps]);

  const [soalSekarangIndex, setSoalSekarangIndex] = useState(0);
  const [jawabanUser, setJawabanUser] = useState(() => {
    const initialAnswers = {};
    detailUjian.soalList.forEach(soal => {
      initialAnswers[soal.id] = soal.jawabanUser || (soal.tipe === "pilihan_ganda" ? null : "");
    });
    return initialAnswers;
  });
  const [statusRaguRagu, setStatusRaguRagu] = useState(() => {
    const initialRagu = {};
    detailUjian.soalList.forEach(soal => { initialRagu[soal.id] = soal.raguRagu || false; });
    return initialRagu;
  });
  const [sisaWaktuDetik, setSisaWaktuDetik] = useState(detailUjian.durasiTotalDetik);
  const [navigasiSoalOpen, setNavigasiSoalOpen] = useState(false); // Default tertutup di mobile
  const [openDialogSelesai, setOpenDialogSelesai] = useState(false);

  const soalSekarang = detailUjian.soalList[soalSekarangIndex];

  useEffect(() => {
    if (sisaWaktuDetik <= 0) {
      handleSelesaiUjian(true); return;
    }
    const timer = setInterval(() => { setSisaWaktuDetik(prev => prev - 1); }, 1000);
    return () => clearInterval(timer);
  }, [sisaWaktuDetik]);

  const formatWaktu = (totalDetik) => {
    const jam = Math.floor(totalDetik / 3600);
    const menit = Math.floor((totalDetik % 3600) / 60);
    const detik = totalDetik % 60;
    return `${jam.toString().padStart(2, '0')}:${menit.toString().padStart(2, '0')}:${detik.toString().padStart(2, '0')}`;
  };

  const handlePilihJawaban = (idSoal, jawaban) => { setJawabanUser(prev => ({ ...prev, [idSoal]: jawaban })); };
  const handleTandaiRaguRagu = () => { setStatusRaguRagu(prev => ({ ...prev, [soalSekarang.id]: !prev[soalSekarang.id] })); };
  const handleSoalSebelumnya = () => { setSoalSekarangIndex(prev => Math.max(0, prev - 1)); };
  const handleSoalBerikutnya = () => { setSoalSekarangIndex(prev => Math.min(detailUjian.soalList.length - 1, prev + 1)); };
  const handleSelesaiUjian = (otomatisKarenaWaktuHabis = false) => {
    setOpenDialogSelesai(false);
    console.log("Jawaban Terkumpul:", jawabanUser);
    console.log("Status Ragu-ragu:", statusRaguRagu);
    alert(`Ujian Selesai ${otomatisKarenaWaktuHabis ? '(Waktu Habis)' : ''} (Simulasi)`);
    // router.post(route('ujian.submit', { ujianId: detailUjian.id }), { jawaban: jawabanUser, statusRagu: statusRaguRagu });
    router.visit(route('dashboard')); // Redirect setelah submit
  };

  const progresPersen = ((soalSekarangIndex + 1) / detailUjian.soalList.length) * 100;
  const soalListDenganStatusRagu = detailUjian.soalList.map(soal => ({ ...soal, raguRagu: statusRaguRagu[soal.id] || false }));

  return (
    <div className="min-h-screen bg-blue-gray-50 flex flex-col">
      <Head title={`Mengerjakan: ${detailUjian.judulUjian}`} /> {/* Title untuk tab browser */}
      
      {/* Header Ujian (Sticky) */}
      <header className="sticky top-0 z-30 bg-white shadow px-4 py-3 w-full"> {/* w-full agar header mengambil lebar penuh */}
        <div className="flex items-center justify-between"> {/* Tidak perlu max-w-screen-2xl agar full width */}
          <div>
            <Typography variant="small" color="blue-gray" className="font-normal opacity-75">{detailUjian.namaMataKuliah}</Typography>
            <Typography variant="h6" color="blue-gray">{detailUjian.judulUjian}</Typography>
          </div>
          <div className="flex items-center gap-2 sm:gap-4">
            <Chip value={`Soal ${soalSekarang.nomor}/${detailUjian.soalList.length}`} variant="ghost" className="hidden sm:inline-block"/>
            <Chip icon={<ClockIcon className="h-4 w-4"/>} value={formatWaktu(sisaWaktuDetik)} color={sisaWaktuDetik < (5 * 60) ? "red" : "green"} variant="ghost"/>
            <IconButton variant="text" color="blue-gray" onClick={() => setNavigasiSoalOpen(prev => !prev)}>
              <ListBulletIcon className="h-6 w-6" />
            </IconButton>
          </div>
        </div>
        <Progress value={progresPersen} color="blue" size="sm" className="mt-2 absolute bottom-0 left-0 right-0 rounded-none" />
      </header>

      {/* Konten Utama dan Sidebar Navigasi Soal */}
      <div className="flex-grow w-full flex flex-row overflow-hidden relative"> {/* relative untuk positioning sidebar */}
        {/* Area Soal Utama */}
        <main className={`flex-grow p-4 sm:p-6 md:p-8 overflow-y-auto transition-all duration-300 ease-in-out ${navigasiSoalOpen && window.innerWidth >= 768 ? "md:mr-72 lg:mr-80" : "mr-0"}`}>
        {/* Di desktop, beri margin kanan jika sidebar navigasi soal terbuka dan bukan mode drawer mobile */}
        {/* Kelas md:mr-72 lg:mr-80 di atas hanya contoh jika Anda ingin sidebar desktop mendorong konten,
            namun untuk drawer style seperti DaisyUI, sidebar akan overlay, jadi margin ini tidak perlu.
            Saya akan menghapusnya untuk perilaku overlay yang lebih konsisten dengan drawer.
        */}
        {/* <main className="flex-grow p-4 sm:p-6 md:p-8 overflow-y-auto"> */}

          <Card className="p-6 mb-6 shadow">
            <div className="flex justify-between items-center">
                <Typography variant="h6" color="blue-gray" className="mb-1">Soal No. {soalSekarang.nomor}</Typography>
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

        {/* Navigasi Soal (Sidebar Kanan/Drawer) */}
        {/* Tidak ada placeholder div md:hidden di sini lagi, karena NavigasiSoalSidebar sudah fixed */}
        <NavigasiSoalSidebar
            soalList={soalListDenganStatusRagu}
            soalSekarangIndex={soalSekarangIndex}
            setSoalSekarangIndex={setSoalSekarangIndex}
            jawabanUser={jawabanUser}
            statusRaguRagu={statusRaguRagu} // Kirim state ragu-ragu
            isOpen={navigasiSoalOpen}
            toggleSidebar={() => setNavigasiSoalOpen(prev => !prev)} // Toggle state
        />
      </div>

      <Dialog open={openDialogSelesai} handler={() => setOpenDialogSelesai(false)} size="xs">
        {/* ... Dialog Konten ... */}
        <DialogHeader>Konfirmasi Selesai Ujian</DialogHeader>
        <DialogBody divider>Apakah Anda yakin ingin menyelesaikan ujian ini? Jawaban tidak bisa diubah lagi.</DialogBody>
        <DialogFooter>
          <Button variant="text" color="blue-gray" onClick={() => setOpenDialogSelesai(false)} className="mr-1"><span>Batal</span></Button>
          <Button variant="gradient" color="green" onClick={() => handleSelesaiUjian()}><span>Ya, Kumpulkan</span></Button>
        </DialogFooter>
      </Dialog>
    </div>
  );
}