// Pages/Ujian/PengerjaanUjianPage.jsx
import React, { useState, useEffect, useCallback } from 'react';
import { Typography, Spinner, Button } from "@material-tailwind/react"; // Hanya Button yang mungkin dipakai langsung dari MT jika Link Inertia tidak bisa distyle
import { XCircleIcon, InformationCircleIcon } from "@heroicons/react/24/solid";
import { router, usePage, Head, Link } from '@inertiajs/react';
import axios from 'axios';

// Impor komponen-komponen baru
import NavigasiSoalSidebar from './PengerjaanComponents/NavigasiSoalSidebar';
import HeaderUjian from './PengerjaanComponents/HeaderUjian';
import SoalDisplay from './PengerjaanComponents/SoalDisplay';
import NavigasiBawah from './PengerjaanComponents/NavigasiBawah';
import DialogKonfirmasiSelesai from './PengerjaanComponents/DialogKonfirmasiSelesai';

export default function PengerjaanUjianPage() {
  const { props } = usePage();
  const idUjianDariProps = props.idUjianAktif;

  const [detailUjian, setDetailUjian] = useState(null);
  const [isLoadingSoal, setIsLoadingSoal] = useState(true);
  const [errorSoal, setErrorSoal] = useState(null);
  const [soalSekarangIndex, setSoalSekarangIndex] = useState(0);
  const [jawabanUser, setJawabanUser] = useState({});
  const [statusRaguRagu, setStatusRaguRagu] = useState({});
  const [sisaWaktuDetik, setSisaWaktuDetik] = useState(0);
  const [navigasiSoalOpen, setNavigasiSoalOpen] = useState(false);
  const [openDialogSelesai, setOpenDialogSelesai] = useState(false);

  useEffect(() => {
    if (idUjianDariProps) {
      setIsLoadingSoal(true);
      setErrorSoal(null);
      axios.get('/sanctum/csrf-cookie', { withCredentials: true }) // Pastikan URL CSRF cookie benar
        .then(() => {
          const apiUrl = route('api.ujian.ambilsoal', { id_ujian: idUjianDariProps });
          axios.get(apiUrl, { withCredentials: true })
            .then(response => {
              const dataUjianApi = response.data;
              const initialAnswers = {};
              const initialRagu = {};
              let soalListProcessed = [];

              if (dataUjianApi.soalList && Array.isArray(dataUjianApi.soalList)) {
                soalListProcessed = dataUjianApi.soalList.map(soal => {
                  let parsedOpsi = soal.opsi;
                  if ((soal.tipe === "pilihan_ganda" || soal.tipe === "benar_salah") && typeof soal.opsi === 'string') {
                    try {
                      parsedOpsi = JSON.parse(soal.opsi);
                    } catch (e) {
                      console.error(`Gagal parse JSON opsi u/ soal ID: ${soal.id} (tipe: ${soal.tipe})`, "Error:", e, "Opsi asli:", soal.opsi);
                      parsedOpsi = [];
                    }
                  }
                  // Pastikan soal.pasangan juga di-parse jika perlu dan dikirim sebagai string JSON
                  let parsedPasangan = soal.pasangan;
                  if (soal.tipe === "menjodohkan" && typeof soal.pasangan === 'string') {
                     try {
                        parsedPasangan = JSON.parse(soal.pasangan);
                     } catch (e) {
                        console.error(`Gagal parse JSON pasangan u/ soal ID: ${soal.id}`, "Error:", e, "Pasangan asli:", soal.pasangan);
                        parsedPasangan = [];
                     }
                  }
                  return { ...soal, opsi: parsedOpsi, pasangan: parsedPasangan };
                });

                soalListProcessed.forEach(soal => {
                  initialAnswers[soal.id] = (soal.tipe === "pilihan_ganda" || soal.tipe === "benar_salah") ? null : "";
                  initialRagu[soal.id] = false;
                });
              } else {
                console.warn("dataUjianApi.soalList tidak valid:", dataUjianApi.soalList);
              }

              setJawabanUser(initialAnswers);
              setStatusRaguRagu(initialRagu);
              setDetailUjian({ ...dataUjianApi, soalList: soalListProcessed });
              setSisaWaktuDetik(dataUjianApi.durasiTotalDetik || 0);
              setIsLoadingSoal(false);
            })
            .catch(err => {
              console.error("Gagal mengambil soal dari API Laravel:", err);
              let errorMessage = "Gagal memuat soal dari server.";
              if (err.response) { errorMessage = err.response.data?.message || `Error ${err.response.status}`; }
              else if (err.request) { errorMessage = "Tidak ada respons dari server."; }
              else { errorMessage = err.message; }
              setErrorSoal(errorMessage);
              setIsLoadingSoal(false);
            });
        }).catch(csrfError => {
            console.error("Gagal mengambil CSRF cookie:", csrfError);
            setErrorSoal("Gagal memulai sesi ujian dengan aman. Silakan coba lagi.");
            setIsLoadingSoal(false);
        });
    } else {
      setErrorSoal("ID Ujian tidak valid atau tidak ditemukan.");
      setIsLoadingSoal(false);
    }
  }, [idUjianDariProps]);

  const handleSelesaiUjianCallback = useCallback(() => {
    setOpenDialogSelesai(false);
    const dataDikumpulkan = {
      jawaban: jawabanUser,
      statusRaguRagu: statusRaguRagu,
      ujianId: detailUjian?.id,
    };
    console.log("Data Ujian yang Dikumpulkan (Simulasi):", dataDikumpulkan);
    if (detailUjian?.id) {
      router.post(route('ujian.submit'), dataDikumpulkan, { // Asumsi ada route ujian.submit
        onFinish: () => router.visit(route('ujian.selesai.konfirmasi', { id_ujian: detailUjian.id })),
        onError: (errors) => {
            console.error("Error submitting ujian:", errors);
            // Handle error, mungkin tampilkan notifikasi
        }
      });
    } else {
      console.error("Tidak bisa submit, detailUjian.id tidak ditemukan");
      router.visit(route('dashboard'));
    }
  }, [jawabanUser, statusRaguRagu, detailUjian, router]);

  useEffect(() => {
    if (!detailUjian) return;
    if (sisaWaktuDetik <= 0 && detailUjian.soalList && detailUjian.soalList.length > 0 && !openDialogSelesai) {
        // Hanya submit otomatis jika dialog selesai belum terbuka (mencegah double submit)
        console.log("Waktu habis, ujian akan dikumpulkan otomatis.");
        handleSelesaiUjianCallback();
        return;
    }
    const timer = setInterval(() => { setSisaWaktuDetik(prev => Math.max(0, prev - 1)); }, 1000);
    return () => clearInterval(timer);
  }, [sisaWaktuDetik, detailUjian, handleSelesaiUjianCallback, openDialogSelesai]);

  const soalSekarang = detailUjian?.soalList?.[soalSekarangIndex];

  useEffect(() => {
    if (!isLoadingSoal && soalSekarang) {
      // console.log('%c[useEffect DEBUG] Soal Saat Ini:', 'color: green; font-weight: bold;', soalSekarang);
    }
  }, [soalSekarang, isLoadingSoal]);

  const handlePilihJawaban = (idSoal, jawaban) => { setJawabanUser(prev => ({ ...prev, [idSoal]: jawaban })); };
  const handleTandaiRaguRagu = () => { if(soalSekarang) setStatusRaguRagu(prev => ({ ...prev, [soalSekarang.id]: !prev[soalSekarang.id] })); };
  const handleSoalSebelumnya = () => { setSoalSekarangIndex(prev => Math.max(0, prev - 1)); };
  const handleSoalBerikutnya = () => { if(detailUjian && detailUjian.soalList) setSoalSekarangIndex(prev => Math.min(detailUjian.soalList.length - 1, prev + 1)); };
  
  const progresPersen = detailUjian?.soalList?.length > 0 ? ((soalSekarangIndex + 1) / detailUjian.soalList.length) * 100 : 0;
  const soalListUntukNavigasi = detailUjian?.soalList?.map((soal, index) => ({ 
      id: soal.id, 
      nomor: soal.nomor || (index + 1) 
    })) || [];


  if (isLoadingSoal) {
    return (
      <div className="min-h-screen flex flex-col items-center justify-center bg-blue-gray-50 p-6">
        <Head title="Memuat Ujian..." />
        <Spinner className="h-12 w-12 text-blue-500/80" />
        <Typography color="blue-gray" className="mt-4">Memuat soal ujian...</Typography>
      </div>
    );
  }

  if (errorSoal) {
    return (
      <div className="min-h-screen flex flex-col items-center justify-center bg-blue-gray-50 p-6 text-center">
        <Head title="Error Memuat Ujian" />
        <XCircleIcon className="h-16 w-16 text-red-500 mb-4" />
        <Typography variant="h5" color="red">Gagal Memuat Soal</Typography>
        <Typography color="blue-gray" className="mt-2 mb-6">{errorSoal}</Typography>
        <Link href={route('dashboard')}>
            {/* Ganti Button Material Tailwind dengan Link Inertia jika perlu atau bungkus Button dengan Link */}
            <Button color="blue">Kembali ke Dashboard</Button>
        </Link>
      </div>
    );
  }

  if (!detailUjian || !soalSekarang || !detailUjian.soalList || detailUjian.soalList.length === 0) {
    return (
      <div className="min-h-screen flex flex-col items-center justify-center bg-blue-gray-50 p-6 text-center">
        <Head title="Ujian Tidak Tersedia" />
        <InformationCircleIcon className="h-16 w-16 text-amber-500 mb-4" />
        <Typography variant="h5" color="blue-gray">Ujian Tidak Tersedia</Typography>
        <Typography color="blue-gray" className="mt-2 mb-6">Detail ujian tidak ditemukan atau tidak ada soal yang dapat dimuat.</Typography>
        <Link href={route('dashboard')}>
            <Button color="blue">Kembali ke Dashboard</Button>
        </Link>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-blue-gray-50 flex flex-col">
      <Head title={`Mengerjakan: ${detailUjian.judulUjian || "Ujian"}`} />

      <HeaderUjian
        judulUjian={detailUjian.judulUjian}
        namaMataKuliah={detailUjian.namaMataKuliah}
        nomorSoalSekarang={soalSekarang.nomor || (soalSekarangIndex + 1)}
        totalSoal={detailUjian.soalList.length}
        sisaWaktuDetik={sisaWaktuDetik}
        onToggleNavigasiSoal={() => setNavigasiSoalOpen(prev => !prev)}
        progresPersen={progresPersen}
        navigasiSoalOpen={navigasiSoalOpen}
      />

      <div className="flex-grow w-full flex overflow-hidden relative">
        <main className={`
          flex-grow p-4 sm:p-6 md:p-8 overflow-y-auto 
          transition-all duration-300 ease-in-out
          ${navigasiSoalOpen ? 'md:mr-[20rem]' : 'mr-0'}
        `}>
          <SoalDisplay
            soal={soalSekarang}
            jawabanUserSoalIni={jawabanUser[soalSekarang.id]}
            statusRaguSoalIni={statusRaguRagu[soalSekarang.id]}
            onPilihJawaban={handlePilihJawaban}
            nomorTampil={soalSekarang.nomor || (soalSekarangIndex + 1)}
          />
          <NavigasiBawah
            soalSekarangIndex={soalSekarangIndex}
            totalSoal={detailUjian.soalList.length}
            isRagu={statusRaguRagu[soalSekarang.id]}
            onSoalSebelumnya={handleSoalSebelumnya}
            onSoalBerikutnya={handleSoalBerikutnya}
            onTandaiRagu={handleTandaiRaguRagu}
            onSelesaiUjian={() => setOpenDialogSelesai(true)}
          />
        </main>
        
        <NavigasiSoalSidebar
            soalList={soalListUntukNavigasi} // Kirim soalList yang sudah diproses untuk navigasi
            soalSekarangIndex={soalSekarangIndex}
            setSoalSekarangIndex={setSoalSekarangIndex}
            jawabanUser={jawabanUser}
            statusRaguRagu={statusRaguRagu}
            isOpen={navigasiSoalOpen}
            toggleSidebar={() => setNavigasiSoalOpen(prev => !prev)}
        />
      </div>

      <DialogKonfirmasiSelesai
        open={openDialogSelesai}
        onClose={() => setOpenDialogSelesai(false)}
        onConfirm={handleSelesaiUjianCallback}
      />
    </div>
  );
}