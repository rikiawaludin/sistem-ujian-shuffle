// resources/js/Pages/Ujian/PengerjaanUjianPage.jsx
import React, { useState, useEffect, useCallback, useRef } from 'react';
import {
  Typography, Button as MaterialButton, Spinner,
} from "@material-tailwind/react";
import {
  ArrowLeftIcon, ArrowRightIcon, ListBulletIcon, XMarkIcon, ClockIcon, FlagIcon, CheckCircleIcon, XCircleIcon, InformationCircleIcon,
} from "@heroicons/react/24/solid";
import { router, usePage, Head, Link } from '@inertiajs/react';
import axios from 'axios';

// Pastikan path impor komponen anak sudah benar sesuai struktur folder Anda
// Contoh jika Anda memindahkannya ke direktori @/Components/Ujian/Pengerjaan/
import NavigasiSoalSidebar from '@/Pages/Ujian/PengerjaanComponents/NavigasiSoalSidebar';
import HeaderUjian from '@/Pages/Ujian/PengerjaanComponents/HeaderUjian';
import SoalDisplay from '@/Pages/Ujian/PengerjaanComponents/SoalDisplay';
import NavigasiBawah from '@/Pages/Ujian/PengerjaanComponents/NavigasiBawah';
import DialogKonfirmasiSelesai from '@/Pages/Ujian/PengerjaanComponents/DialogKonfirmasiSelesai';

export default function PengerjaanUjianPage() {
  const { props } = usePage();
  const idUjianDariProps = props.idUjianAktif;
  const authUser = props.auth.user;

  const [detailUjian, setDetailUjian] = useState(null); // Akan berisi { id, pengerjaanId, namaMataKuliah, judulUjian, soalList }
  const [pengerjaanId, setPengerjaanId] = useState(null);
  const [isLoadingSoal, setIsLoadingSoal] = useState(true);
  const [errorSoal, setErrorSoal] = useState(null);
  const [soalSekarangIndex, setSoalSekarangIndex] = useState(0);
  const [jawabanUser, setJawabanUser] = useState({});
  const [statusRaguRagu, setStatusRaguRagu] = useState({});
  const [sisaWaktuDetik, setSisaWaktuDetik] = useState(0);
  const [navigasiSoalOpen, setNavigasiSoalOpen] = useState(false);
  const [openDialogSelesai, setOpenDialogSelesai] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  
  const autoSubmitTriggered = useRef(false); // Menggunakan useRef untuk flag auto-submit

  const getLocalStorageKey = useCallback((type) => {
    if (!idUjianDariProps || !authUser || !pengerjaanId) return null;
    return `ujian_${idUjianDariProps}_user_${authUser.id}_attempt_${pengerjaanId}_${type}`;
  }, [idUjianDariProps, authUser, pengerjaanId]);

  // useEffect untuk mengambil data ujian
  useEffect(() => {
    if (idUjianDariProps && authUser) {
      setIsLoadingSoal(true);
      setErrorSoal(null);
      autoSubmitTriggered.current = false; // Reset flag saat ujian baru dimuat

      axios.get('/sanctum/csrf-cookie', { withCredentials: true })
        .then(() => {
          const apiUrl = route('api.ujian.ambilsoal', { id_ujian: idUjianDariProps });
          axios.get(apiUrl, { withCredentials: true })
            .then(response => {
              const dataUjianApi = response.data;
              if (!dataUjianApi || !dataUjianApi.soalList) {
                console.error("Respons API tidak valid atau tidak ada soalList:", dataUjianApi);
                setErrorSoal("Gagal memuat data ujian dengan format yang benar.");
                setIsLoadingSoal(false);
                return;
              }

              setDetailUjian({
                id: dataUjianApi.id,
                pengerjaanId: dataUjianApi.pengerjaanId,
                namaMataKuliah: dataUjianApi.namaMataKuliah,
                judulUjian: dataUjianApi.judulUjian,
                soalList: Array.isArray(dataUjianApi.soalList) ? dataUjianApi.soalList : [],
              });
              setPengerjaanId(dataUjianApi.pengerjaanId);
              setSisaWaktuDetik(dataUjianApi.durasiTotalDetik || 0);
              
              const initialAnswers = {};
              const initialRagu = {};
              if (Array.isArray(dataUjianApi.soalList)) {
                dataUjianApi.soalList.forEach(soal => {
                  initialAnswers[soal.id] = (soal.tipe === "pilihan_ganda" || soal.tipe === "benar_salah") ? null : "";
                  initialRagu[soal.id] = false;
                });
              }

              const currentPengerjaanId = dataUjianApi.pengerjaanId;
              const jawabanKey = currentPengerjaanId ? `ujian_${idUjianDariProps}_user_${authUser.id}_attempt_${currentPengerjaanId}_jawaban` : null;
              const raguKey = currentPengerjaanId ? `ujian_${idUjianDariProps}_user_${authUser.id}_attempt_${currentPengerjaanId}_ragu` : null;

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
      if (!idUjianDariProps) setErrorSoal("ID Ujian tidak valid atau tidak ditemukan.");
      if (!authUser) setErrorSoal("Sesi pengguna tidak ditemukan, silakan login ulang.");
      setIsLoadingSoal(false);
    }
  }, [idUjianDariProps, authUser]); // getLocalStorageKey akan dipanggil di dalam effect lain

  // useEffect untuk menyimpan jawaban ke localStorage
  useEffect(() => {
    const jawabanKey = getLocalStorageKey('jawaban'); // getLocalStorageKey sekarang bergantung pada pengerjaanId state
    const raguKey = getLocalStorageKey('ragu');
    if (!isLoadingSoal && detailUjian && pengerjaanId && jawabanKey && raguKey) {
        try {
            localStorage.setItem(jawabanKey, JSON.stringify(jawabanUser));
            localStorage.setItem(raguKey, JSON.stringify(statusRaguRagu));
        } catch (e) { console.error("Gagal simpan ke localStorage:", e); }
    }
  }, [jawabanUser, statusRaguRagu, isLoadingSoal, detailUjian, pengerjaanId, getLocalStorageKey]);

  // Callback untuk submit ujian
  const handleSelesaiUjianCallback = useCallback(() => {
    if (isSubmitting || autoSubmitTriggered.current) {
        console.log("Submit dicegah: isSubmitting atau autoSubmitTriggered sudah true.");
        return;
    }
    
    setIsSubmitting(true);
    if (sisaWaktuDetik <= 0) { // Jika submit dipicu oleh waktu habis atau bersamaan
        autoSubmitTriggered.current = true;
    }
    setOpenDialogSelesai(false);

    const dataDikumpulkan = {
      ujianId: detailUjian?.id,
      pengerjaanId: pengerjaanId, // Kirim pengerjaanId yang aktif
      jawaban: jawabanUser,
      statusRaguRagu: statusRaguRagu,
    };

    console.log("Data Ujian yang Akan Dikumpulkan:", dataDikumpulkan);

    if (detailUjian?.id && authUser && pengerjaanId) {
      router.post(route('ujian.submit'), dataDikumpulkan, {
        preserveScroll: true,
        onSuccess: () => {
          console.log("Submit ujian BERHASIL dari sisi client.");
          const jawabanKey = getLocalStorageKey('jawaban');
          const raguKey = getLocalStorageKey('ragu');
          if (jawabanKey) localStorage.removeItem(jawabanKey);
          if (raguKey) localStorage.removeItem(raguKey);
          // Pengarahan ke halaman konfirmasi sudah dihandle oleh redirect server
        },
        onError: (errors) => {
          console.error("Error submitting ujian dari sisi client:", errors);
          let errorMsg = "Gagal mengumpulkan ujian. Silakan coba lagi.";
          if (errors && errors.submit_error) { errorMsg = errors.submit_error; }
          else if (errors && typeof errors === 'object') { errorMsg = Object.values(errors).join("\n"); }
          else if (typeof errors === 'string') { errorMsg = errors; }
          alert(errorMsg); 
          setIsSubmitting(false);
          autoSubmitTriggered.current = false; // Reset flag jika submit gagal agar bisa coba lagi
        },
        onFinish: () => {
            // Jika tidak ada redirect dari server, setIsSubmitting(false) bisa ditaruh di sini.
            // Tapi karena ada redirect, halaman akan berganti.
            // Jika ada error dan tidak redirect, onError sudah menangani setIsSubmitting.
        }
      });
    } else {
      console.error("Tidak bisa submit, data ujian/user/pengerjaan tidak lengkap", {detailUjian, authUser, pengerjaanId});
      alert("Terjadi kesalahan data. Tidak bisa submit.");
      setIsSubmitting(false);
      autoSubmitTriggered.current = false;
    }
  }, [isSubmitting, jawabanUser, statusRaguRagu, detailUjian, pengerjaanId, router, authUser, getLocalStorageKey, sisaWaktuDetik, autoSubmitTriggered]);

  // useEffect untuk Timer
  useEffect(() => {
    if (!detailUjian || isLoadingSoal || isSubmitting || autoSubmitTriggered.current) {
      return; // Jangan jalankan timer jika loading, submitting, atau sudah auto-submit
    }
    
    // Jika waktu sudah habis saat effect ini pertama kali jalan (setelah loading selesai)
    if (sisaWaktuDetik <= 0) {
      if (detailUjian.soalList && detailUjian.soalList.length > 0 && !openDialogSelesai) {
        console.log("[TIMER EFFECT] Waktu sudah habis saat inisialisasi effect. Memanggil submit.");
        handleSelesaiUjianCallback();
      }
      return; 
    }
    
    // Jika waktu masih ada, set interval
    const timerId = setInterval(() => {
      setSisaWaktuDetik(prevSisaWaktu => {
        const nextSisaWaktu = Math.max(0, prevSisaWaktu - 1);
        // console.log(`[setInterval TICK] Prev: ${prevSisaWaktu}, Next: ${nextSisaWaktu}`);
        
        // Pengecekan waktu habis di dalam tick interval
        if (nextSisaWaktu <= 0 && !autoSubmitTriggered.current && !isSubmitting && !openDialogSelesai) {
          if (detailUjian && detailUjian.soalList && detailUjian.soalList.length > 0) {
            console.log("[setInterval TICK] Waktu baru saja habis. Memanggil submit.");
            // Panggil handleSelesaiUjianCallback di luar setState untuk menghindari pemanggilan dalam update state
            // Kita bisa menggunakan flag atau memanggilnya setelah state diupdate
            // Untuk simplisitas, kita panggil di sini, tapi hati-hati dengan closure
            // Lebih aman menggunakan useEffect lain yang memantau sisaWaktuDetik <= 0
            // Namun, blok if di atas (di luar interval) seharusnya menangani ini pada render berikutnya.
            // Untuk lebih reaktif dari interval:
            // handleSelesaiUjianCallback(); // Ini bisa menyebabkan loop jika dependensi handleSelesaiUjianCallback tidak tepat
            // Daripada panggil langsung, biarkan re-render berikutnya menangani di blok if (sisaWaktuDetik <= 0) di atas.
          }
        }
        return nextSisaWaktu;
      });
    }, 1000);

    return () => clearInterval(timerId);
  }, [sisaWaktuDetik, detailUjian, isLoadingSoal, openDialogSelesai, isSubmitting, autoSubmitTriggered, handleSelesaiUjianCallback]);


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

  // Return untuk Loading, Error, atau Ujian Tidak Tersedia (tetap sama)
  if (isLoadingSoal) {
    return (
      <div className="min-h-screen flex flex-col items-center justify-center bg-blue-gray-50 p-6">
        <Head title="Memuat Ujian..." /><Spinner className="h-12 w-12 text-blue-500/80" /><Typography color="blue-gray" className="mt-4">Memuat soal ujian...</Typography>
      </div>
    );
  }
  if (errorSoal) {
    return (
      <div className="min-h-screen flex flex-col items-center justify-center bg-blue-gray-50 p-6 text-center">
        <Head title="Error Memuat Ujian" /><XCircleIcon className="h-16 w-16 text-red-500 mb-4" /><Typography variant="h5" color="red">Gagal Memuat Soal</Typography><Typography color="blue-gray" className="mt-2 mb-6">{errorSoal}</Typography><Link href={route('dashboard')}><MaterialButton color="blue">Kembali ke Dashboard</MaterialButton></Link>
      </div>
    );
  }
  if (!detailUjian || !soalSekarang || !detailUjian.soalList || detailUjian.soalList.length === 0) {
    return (
      <div className="min-h-screen flex flex-col items-center justify-center bg-blue-gray-50 p-6 text-center">
        <Head title="Ujian Tidak Tersedia" /><InformationCircleIcon className="h-16 w-16 text-amber-500 mb-4" /><Typography variant="h5" color="blue-gray">Ujian Tidak Tersedia</Typography><Typography color="blue-gray" className="mt-2 mb-6">Detail ujian tidak ditemukan atau tidak ada soal yang dapat dimuat saat ini.</Typography><Link href={route('dashboard')}><MaterialButton color="blue">Kembali ke Dashboard</MaterialButton></Link>
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
        <main className={` flex-grow p-4 sm:p-6 md:p-8 overflow-y-auto transition-all duration-300 ease-in-out ${navigasiSoalOpen ? 'md:mr-[20rem]' : 'mr-0'} `}>
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