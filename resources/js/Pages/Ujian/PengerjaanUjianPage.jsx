import React, { useState, useEffect, useCallback } from 'react';
import {
  Typography, Button as MaterialButton, Spinner, // Mengganti nama Button agar tidak konflik dengan Link Inertia jika ada
} from "@material-tailwind/react";
import {
  ArrowLeftIcon, ArrowRightIcon, ListBulletIcon, XMarkIcon, ClockIcon, FlagIcon, CheckCircleIcon, XCircleIcon, InformationCircleIcon,
} from "@heroicons/react/24/solid";
import { router, usePage, Head, Link } from '@inertiajs/react';
import axios from 'axios';

// Asumsi path komponen anak sudah benar, contoh:
import NavigasiSoalSidebar from '@/Pages/Ujian/PengerjaanComponents/NavigasiSoalSidebar';
import HeaderUjian from '@/Pages/Ujian/PengerjaanComponents/HeaderUjian';
import SoalDisplay from '@/Pages/Ujian/PengerjaanComponents/SoalDisplay';
import NavigasiBawah from '@/Pages/Ujian/PengerjaanComponents/NavigasiBawah';
import DialogKonfirmasiSelesai from '@/Pages/Ujian/PengerjaanComponents/DialogKonfirmasiSelesai';

export default function PengerjaanUjianPage() {
  const { props } = usePage();
  const idUjianDariProps = props.idUjianAktif;
  const authUser = props.auth.user;

  const [detailUjian, setDetailUjian] = useState(null);
  const [isLoadingSoal, setIsLoadingSoal] = useState(true);
  const [errorSoal, setErrorSoal] = useState(null);
  const [soalSekarangIndex, setSoalSekarangIndex] = useState(0);
  const [jawabanUser, setJawabanUser] = useState({});
  const [statusRaguRagu, setStatusRaguRagu] = useState({});
  const [sisaWaktuDetik, setSisaWaktuDetik] = useState(0);
  const [navigasiSoalOpen, setNavigasiSoalOpen] = useState(false);
  const [openDialogSelesai, setOpenDialogSelesai] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [hasTimeUpSubmitted, setHasTimeUpSubmitted] = useState(false); // Flag untuk mencegah submit ganda karena waktu habis

  const getLocalStorageKey = useCallback((type) => {
    if (!idUjianDariProps || !authUser) return null;
    return `ujian_${idUjianDariProps}_user_${authUser.id}_${type}`;
  }, [idUjianDariProps, authUser]);

  // useEffect untuk mengambil data ujian
  useEffect(() => {
    if (idUjianDariProps && authUser) {
      setIsLoadingSoal(true);
      setErrorSoal(null);
      setHasTimeUpSubmitted(false); // Reset flag submit waktu habis saat memuat ujian baru/refresh

      axios.get('/sanctum/csrf-cookie', { withCredentials: true })
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
                  // Asumsi API sudah mengirim opsi & pasangan sebagai array
                  return { ...soal };
                });
                soalListProcessed.forEach(soal => {
                  initialAnswers[soal.id] = (soal.tipe === "pilihan_ganda" || soal.tipe === "benar_salah") ? null : "";
                  initialRagu[soal.id] = false;
                });
              } else {
                console.warn("dataUjianApi.soalList tidak valid:", dataUjianApi.soalList);
              }

              const jawabanKey = getLocalStorageKey('jawaban');
              const raguKey = getLocalStorageKey('ragu');
              let restoredJawaban = initialAnswers;
              let restoredRagu = initialRagu;

              if (jawabanKey) {
                const savedJawaban = localStorage.getItem(jawabanKey);
                if (savedJawaban) {
                    try { restoredJawaban = JSON.parse(savedJawaban); }
                    catch (e) { console.error('Gagal parse jawaban dari localStorage', e); }
                }
              }
              if (raguKey) {
                const savedRagu = localStorage.getItem(raguKey);
                if (savedRagu) {
                    try { restoredRagu = JSON.parse(savedRagu); }
                    catch (e) { console.error('Gagal parse status ragu dari localStorage', e); }
                }
              }
              
              setJawabanUser(restoredJawaban);
              setStatusRaguRagu(restoredRagu);
              setDetailUjian({ ...dataUjianApi, soalList: soalListProcessed });
              setSisaWaktuDetik(dataUjianApi.durasiTotalDetik); // Ini adalah SISA WAKTU dari server
              setIsLoadingSoal(false);
            })
            .catch(err => { /* ... error handling ... */ });
        }).catch(csrfError => { /* ... error handling ... */ });
    } else { /* ... error handling ... */ }
  }, [idUjianDariProps, authUser, getLocalStorageKey]);

  // useEffect untuk menyimpan jawaban ke localStorage
  useEffect(() => {
    const jawabanKey = getLocalStorageKey('jawaban');
    const raguKey = getLocalStorageKey('ragu');
    if (!isLoadingSoal && detailUjian && jawabanKey && raguKey) {
        try {
            localStorage.setItem(jawabanKey, JSON.stringify(jawabanUser));
            localStorage.setItem(raguKey, JSON.stringify(statusRaguRagu));
        } catch (e) { console.error("Gagal simpan ke localStorage:", e); }
    }
  }, [jawabanUser, statusRaguRagu, isLoadingSoal, detailUjian, getLocalStorageKey]);

  // Callback untuk submit ujian
  const handleSelesaiUjianCallback = useCallback(() => {
    if (isSubmitting || hasTimeUpSubmitted) return; // Mencegah double submit
    
    setIsSubmitting(true);
    if (sisaWaktuDetik <= 0 && !hasTimeUpSubmitted) { // Tandai jika disubmit karena waktu habis
        setHasTimeUpSubmitted(true);
    }
    setOpenDialogSelesai(false);

    const dataDikumpulkan = {
      ujianId: detailUjian?.id,
      jawaban: jawabanUser,
      statusRaguRagu: statusRaguRagu,
    };

    if (detailUjian?.id && authUser) {
      router.post(route('ujian.submit'), dataDikumpulkan, {
        preserveScroll: true,
        onSuccess: () => {
          const jawabanKey = getLocalStorageKey('jawaban');
          const raguKey = getLocalStorageKey('ragu');
          if (jawabanKey) localStorage.removeItem(jawabanKey);
          if (raguKey) localStorage.removeItem(raguKey);
          router.visit(route('ujian.selesai.konfirmasi', { id_ujian: detailUjian.id }), { replace: true });
        },
        onError: (errors) => {
          console.error("Error submitting ujian:", errors);
          alert("Gagal mengumpulkan ujian: " + (errors.message || Object.values(errors).join("\n")));
          setIsSubmitting(false);
          setHasTimeUpSubmitted(false); // Reset flag jika submit gagal agar bisa coba lagi (jika relevan)
        },
      });
    } else {
      console.error("Tidak bisa submit, detailUjian.id atau user tidak ditemukan");
      alert("Terjadi kesalahan, ID ujian atau data pengguna tidak ditemukan.");
      setIsSubmitting(false);
    }
  }, [isSubmitting, hasTimeUpSubmitted, jawabanUser, statusRaguRagu, detailUjian, router, authUser, getLocalStorageKey, sisaWaktuDetik]);

  // useEffect untuk Timer
  useEffect(() => {
    // console.log(`[TIMER EFFECT] Start. Sisa: ${sisaWaktuDetik}, Loading: ${isLoadingSoal}, Submitting: ${isSubmitting}, Dialog: ${openDialogSelesai}, TimeUpSubmitted: ${hasTimeUpSubmitted}`);

    if (!detailUjian || isLoadingSoal || isSubmitting || hasTimeUpSubmitted) {
    //   console.log("[TIMER EFFECT] Guarded or already submitted by time up.");
      return;
    }
    
    if (sisaWaktuDetik <= 0) {
    //   console.log(`[TIMER EFFECT] Time is up (sisa: ${sisaWaktuDetik}). Attempting auto-submit.`);
      if (!openDialogSelesai) { // Hanya submit otomatis jika dialog konfirmasi tidak sedang terbuka
        handleSelesaiUjianCallback();
      }
      return; 
    }
    
    // console.log(`[TIMER EFFECT] Setting interval for sisaWaktuDetik: ${sisaWaktuDetik}`);
    const timerId = setInterval(() => {
      setSisaWaktuDetik(prevSisaWaktu => {
        const nextSisaWaktu = Math.max(0, prevSisaWaktu - 1);
        console.log(`[setInterval TICK] Prev: ${prevSisaWaktu}, Next: ${nextSisaWaktu}`); // <-- LOG PENTING
        return nextSisaWaktu;
      });
    }, 1000);

    return () => {
    //   console.log(`[TIMER EFFECT CLEANUP] Clearing interval ID: ${timerId}`);
      clearInterval(timerId);
    };
    // Hapus handleSelesaiUjianCallback dari dependensi jika menyebabkan terlalu banyak reset.
    // Logika auto-submit saat sisaWaktuDetik <= 0 sudah ada di atas.
  }, [sisaWaktuDetik, detailUjian, isLoadingSoal, openDialogSelesai, isSubmitting, hasTimeUpSubmitted, handleSelesaiUjianCallback]); 
  // Perhatikan dependensi handleSelesaiUjianCallback di sini. Jika ia sering berubah, timer akan reset.
  // Kita bisa memanggilnya berdasarkan pengecekan sisaWaktuDetik di dalam interval atau di awal effect.

  const soalSekarang = detailUjian?.soalList?.[soalSekarangIndex];

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
            <MaterialButton color="blue">Kembali ke Dashboard</MaterialButton>
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
        <Typography color="blue-gray" className="mt-2 mb-6">Detail ujian tidak ditemukan atau tidak ada soal yang dapat dimuat saat ini.</Typography>
        <Link href={route('dashboard')}>
            <MaterialButton color="blue">Kembali ke Dashboard</MaterialButton>
        </Link>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-blue-gray-50 flex flex-col">
      <Head title={`Mengerjakan: ${detailUjian?.judulUjian || "Ujian"}`} />

      <HeaderUjian
        judulUjian={detailUjian?.judulUjian}
        namaMataKuliah={detailUjian?.namaMataKuliah}
        nomorSoalSekarang={soalSekarang?.nomor || (soalSekarangIndex + 1)}
        totalSoal={detailUjian?.soalList?.length || 0}
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
            isSubmitting={isSubmitting}
          />
        </main>
        
        <NavigasiSoalSidebar
            soalList={soalListUntukNavigasi}
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
        onClose={() => !isSubmitting && setOpenDialogSelesai(false)}
        onConfirm={handleSelesaiUjianCallback}
      />
    </div>
  );
}