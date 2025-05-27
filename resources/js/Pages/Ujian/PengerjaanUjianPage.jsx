import React, { useState, useEffect, useMemo } from 'react';
import {
  Typography, Button, Radio, Textarea, Card, IconButton, Progress, Chip, Dialog, DialogHeader, DialogBody, DialogFooter, Spinner,
} from "@material-tailwind/react";
import {
  ArrowLeftIcon, ArrowRightIcon, ListBulletIcon, XMarkIcon, ClockIcon, FlagIcon, CheckCircleIcon, XCircleIcon, InformationCircleIcon,
} from "@heroicons/react/24/solid";
import { router, usePage, Head, Link } from '@inertiajs/react';
import axios from 'axios';

// --- Komponen NavigasiSoalSidebar --- (Tetap sama)
function NavigasiSoalSidebar({ soalList, soalSekarangIndex, setSoalSekarangIndex, jawabanUser, statusRaguRagu, isOpen, toggleSidebar }) {
  // ... (kode sidebar tidak berubah) ...
  return (
    <>
      {isOpen && (<div className="fixed inset-0 z-50 bg-black/30 md:hidden" onClick={toggleSidebar} aria-label="close sidebar"></div>)}
      <div className={`
        fixed top-0 h-full w-72 sm:w-80 bg-white shadow-xl transition-transform duration-300 ease-in-out z-[60] border-l border-blue-gray-100
        ${isOpen ? "translate-x-0 right-0" : "translate-x-full -right-72 sm:-right-80"}
        flex flex-col
      `}>
        <div className="flex justify-between items-center p-4 border-b border-blue-gray-100">
          <Typography variant="h6" color="blue-gray">Navigasi Soal</Typography>
          <IconButton variant="text" color="blue-gray" onClick={toggleSidebar}>
            <XMarkIcon className="h-5 w-5" />
          </IconButton>
        </div>
        <div className="flex-grow p-4 grid grid-cols-4 sm:grid-cols-5 gap-2 overflow-y-auto">
          {soalList.map((soal, index) => {
            const sudahDijawab = jawabanUser[soal.id] !== null && jawabanUser[soal.id] !== "";
            const isRagu = statusRaguRagu[soal.id];
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

      axios.get('http://localhost:8000/sanctum/csrf-cookie', { withCredentials: true })
        .then(() => {
          const apiUrl = route('api.ujian.ambilsoal', { id_ujian: idUjianDariProps });
          axios.get(apiUrl, {
            withCredentials: true,
          })
          .then(response => {
            const dataUjianApi = response.data;
            const initialAnswers = {};
            const initialRagu = {};
            let soalListProcessed = [];

            if (dataUjianApi.soalList && Array.isArray(dataUjianApi.soalList)) {
              soalListProcessed = dataUjianApi.soalList.map(soal => {
                let parsedOpsi = soal.opsi;
                // --- [MODIFIKASI CONDIITIONAL PARSING] ---
                if ((soal.tipe === "pilihan_ganda" || soal.tipe === "benar_salah") && typeof soal.opsi === 'string') {
                  try {
                    parsedOpsi = JSON.parse(soal.opsi);
                  } catch (e) {
                    console.error(`Gagal parse JSON opsi untuk soal ID: ${soal.id} (tipe: ${soal.tipe})`, "Error:", e, "Opsi asli:", soal.opsi);
                    parsedOpsi = [];
                  }
                }
                return { ...soal, opsi: parsedOpsi };
              });

              soalListProcessed.forEach(soal => {
                // --- [MODIFIKASI INITIAL ANSWERS] ---
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
            // ... (error handling tetap sama) ...
            console.error("Gagal mengambil soal dari API Laravel:", err);
            let errorMessage = "Gagal memuat soal dari server.";
            if (err.response) {
              errorMessage = err.response.data?.message || `Error ${err.response.status}`;
            } else if (err.request) {
              errorMessage = "Tidak ada respons dari server. Periksa koneksi atau API backend.";
            } else {
              errorMessage = err.message;
            }
            setErrorSoal(errorMessage);
            setIsLoadingSoal(false);
          });
        });
    } else {
      setErrorSoal("ID Ujian tidak valid atau tidak ditemukan untuk memulai ujian.");
      setIsLoadingSoal(false);
    }
  }, [idUjianDariProps]);

  const handleSelesaiUjianCallback = React.useCallback(() => {
    // ... (fungsi tetap sama) ...
    setOpenDialogSelesai(false);
    const dataDikumpulkan = {
      jawaban: jawabanUser,
      statusRaguRagu: statusRaguRagu,
      ujianId: detailUjian?.id,
    };
    console.log("Data Ujian yang Dikumpulkan (Simulasi):", dataDikumpulkan);
    if (detailUjian?.id) {
      router.visit(route('ujian.selesai.konfirmasi', { id_ujian: detailUjian.id }));
    } else {
      console.error("Tidak bisa submit, detailUjian.id tidak ditemukan");
      router.visit(route('dashboard'));
    }
  }, [jawabanUser, statusRaguRagu, detailUjian]);

  useEffect(() => {
    // ... (timer tetap sama) ...
    if (!detailUjian) return;
    if (sisaWaktuDetik <= 0 && detailUjian.soalList && detailUjian.soalList.length > 0) {
      handleSelesaiUjianCallback();
      return;
    }
    const timer = setInterval(() => { setSisaWaktuDetik(prev => Math.max(0, prev - 1)); }, 1000);
    return () => clearInterval(timer);
  }, [sisaWaktuDetik, detailUjian, handleSelesaiUjianCallback]);

  const soalSekarang = detailUjian?.soalList?.[soalSekarangIndex];

  useEffect(() => {
    if (!isLoadingSoal && soalSekarang) {
      console.log('%c[useEffect DEBUG] Soal Saat Ini:', 'color: green; font-weight: bold;', soalSekarang);
      console.log(`[useEffect DEBUG] Tipe Soal: ${soalSekarang.tipe}`);
      console.log('[useEffect DEBUG] Opsi Soal:', soalSekarang.opsi);
      console.log(`[useEffect DEBUG] Apakah opsi array?: ${Array.isArray(soalSekarang.opsi)}`);
    }
  }, [soalSekarang, isLoadingSoal]);

  const formatWaktu = (totalDetik) => {
    // ... (fungsi tetap sama) ...
    const jam = Math.floor(totalDetik / 3600);
    const menit = Math.floor((totalDetik % 3600) / 60);
    const detik = totalDetik % 60;
    return `${jam.toString().padStart(2, '0')}:${menit.toString().padStart(2, '0')}:${detik.toString().padStart(2, '0')}`;
  };
  const handlePilihJawaban = (idSoal, jawaban) => { setJawabanUser(prev => ({ ...prev, [idSoal]: jawaban })); };
  const handleTandaiRaguRagu = () => { if(soalSekarang) setStatusRaguRagu(prev => ({ ...prev, [soalSekarang.id]: !prev[soalSekarang.id] })); };
  const handleSoalSebelumnya = () => { setSoalSekarangIndex(prev => Math.max(0, prev - 1)); };
  const handleSoalBerikutnya = () => { if(detailUjian && detailUjian.soalList) setSoalSekarangIndex(prev => Math.min(detailUjian.soalList.length - 1, prev + 1)); };

  const progresPersen = detailUjian?.soalList?.length > 0 ? ((soalSekarangIndex + 1) / detailUjian.soalList.length) * 100 : 0;
  const soalListDenganStatusRagu = detailUjian?.soalList?.map(soal => ({ ...soal, raguRagu: statusRaguRagu[soal.id] || false })) || [];

  // ... (Tampilan Loading, Error, data belum siap tetap sama) ...
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
      <header className={`
        sticky top-0 z-30 bg-white shadow px-4 py-3 w-full
        transition-all duration-300 ease-in-out
        ${navigasiSoalOpen ? 'md:pr-[20rem]' : 'pr-4'}
      `}>
        {/* ... (isi header tetap sama) ... */}
        <div className="flex items-center justify-between">
          <div>
            <Typography variant="small" color="blue-gray" className="font-normal opacity-75">{detailUjian.namaMataKuliah || "Mata Kuliah"}</Typography>
            <Typography variant="h6" color="blue-gray">{detailUjian.judulUjian || "Judul Ujian"}</Typography>
          </div>
          <div className="flex items-center gap-2 sm:gap-4">
            <Chip value={`Soal ${soalSekarang.nomor || (soalSekarangIndex + 1)}/${detailUjian.soalList.length}`} variant="ghost" className="hidden sm:inline-block"/>
            <Chip icon={<ClockIcon className="h-4 w-4"/>} value={formatWaktu(sisaWaktuDetik)} color={sisaWaktuDetik < (5 * 60) ? "red" : (sisaWaktuDetik < (15*60) ? "amber" : "green")} variant="ghost"/>
            <IconButton variant="text" color="blue-gray" onClick={() => setNavigasiSoalOpen(prev => !prev)}>
              <ListBulletIcon className="h-6 w-6" />
            </IconButton>
          </div>
        </div>
        {detailUjian.soalList.length > 0 && <Progress value={progresPersen} color="blue" size="sm" className="mt-2 absolute bottom-0 left-0 right-0 rounded-none" />}
      </header>

      <div className="flex-grow w-full flex overflow-hidden relative">
        <main className={`
          flex-grow p-4 sm:p-6 md:p-8 overflow-y-auto
          transition-all duration-300 ease-in-out
          ${navigasiSoalOpen ? 'md:mr-[20rem]' : 'mr-0'}
        `}>
          <Card className="p-6 mb-6 shadow-md border border-blue-gray-100">
            <div className="flex justify-between items-center">
                <Typography variant="h6" color="blue-gray" className="mb-1">Soal No. {soalSekarang.nomor || (soalSekarangIndex + 1)}</Typography>
                {statusRaguRagu[soalSekarang.id] && <Chip value="Ragu-ragu" color="amber" size="sm" icon={<FlagIcon className="h-3 w-3"/>} />}
            </div>
            <hr className="my-3 border-blue-gray-100" />
            <Typography variant="paragraph" className="mb-6 whitespace-pre-line leading-relaxed">
              {soalSekarang.pertanyaan}
            </Typography>

            {/* Render Pilihan Ganda */}
            {soalSekarang.tipe === "pilihan_ganda" && (
              Array.isArray(soalSekarang.opsi) && soalSekarang.opsi.length > 0 ? (
                <div className="flex flex-col gap-3">
                  {soalSekarang.opsi.map((opsiItem, index) => {
                    const optionText = typeof opsiItem === 'object' && opsiItem !== null ? opsiItem.teks : opsiItem;
                    const optionValue = typeof opsiItem === 'object' && opsiItem !== null ? opsiItem.id : opsiItem;
                    return (
                      <Radio
                        key={`${soalSekarang.id}-pg-opsi-${index}`} // Key lebih spesifik
                        id={`opsi-${soalSekarang.id}-${index}`}
                        name={`soal-${soalSekarang.id}`}
                        label={
                          <Typography color="blue-gray" className="font-normal flex items-center">
                              <span className="font-semibold mr-2">{String.fromCharCode(65 + index)}.</span> {optionText}
                          </Typography>
                        }
                        value={optionValue}
                        checked={jawabanUser[soalSekarang.id] === optionValue}
                        onChange={() => handlePilihJawaban(soalSekarang.id, optionValue)}
                        ripple={true}
                        className="hover:before:opacity-0 border-blue-gray-300"
                        containerProps={{ className: "p-0 -ml-0.5" }}
                        labelProps={{className: "ml-2 text-sm"}}
                      />
                    );
                  })}
                </div>
              ) : (
                <Typography color="orange" className="text-sm my-4">
                  Soal pilihan ganda ini tidak memiliki opsi jawaban yang valid.
                </Typography>
              )
            )}

            {/* --- [BLOK BARU UNTUK SOAL BENAR/SALAH] --- */}
            {soalSekarang.tipe === "benar_salah" && (
              Array.isArray(soalSekarang.opsi) && soalSekarang.opsi.length > 0 ? (
                <div className="flex flex-col gap-3">
                  {soalSekarang.opsi.map((opsiItem, index) => {
                    // Asumsi format opsi untuk benar/salah adalah [{"id":"Benar","teks":"Benar"}, {"id":"Salah","teks":"Salah"}]
                    // atau bisa juga cuma ["Benar", "Salah"]
                    const optionText = typeof opsiItem === 'object' && opsiItem !== null ? opsiItem.teks : opsiItem;
                    const optionValue = typeof opsiItem === 'object' && opsiItem !== null ? opsiItem.id : opsiItem;
                    return (
                      <Radio
                        key={`${soalSekarang.id}-bs-opsi-${index}`} // Key lebih spesifik
                        id={`opsi-bs-${soalSekarang.id}-${index}`}
                        name={`soal-bs-${soalSekarang.id}`}
                        label={
                          <Typography color="blue-gray" className="font-normal">
                            {optionText}
                          </Typography>
                        }
                        value={optionValue} // Ini akan menjadi "Benar" atau "Salah" jika formatnya {"id":"Benar", ...}
                        checked={jawabanUser[soalSekarang.id] === optionValue}
                        onChange={() => handlePilihJawaban(soalSekarang.id, optionValue)}
                        ripple={true}
                        className="hover:before:opacity-0 border-blue-gray-300"
                        containerProps={{ className: "p-0 -ml-0.5" }}
                        labelProps={{className: "ml-2 text-sm"}}
                      />
                    );
                  })}
                </div>
              ) : (
                <Typography color="orange" className="text-sm my-4">
                  Soal Benar/Salah ini tidak memiliki opsi jawaban yang valid.
                </Typography>
              )
            )}
            {/* --- [AKHIR BLOK BARU UNTUK SOAL BENAR/SALAH] --- */}


            {/* Render Esai */}
            {soalSekarang.tipe === "esai" && (
              <Textarea
                label="Ketik Jawaban Anda di Sini..."
                value={jawabanUser[soalSekarang.id] || ""}
                onChange={(e) => handlePilihJawaban(soalSekarang.id, e.target.value)}
                rows={6}
                className="text-sm"
              />
            )}
          </Card>
          {/* ... (Tombol navigasi bawah tetap sama) ... */}
          <div className="flex flex-col sm:flex-row items-center justify-between gap-3 mt-8">
             <Button variant="text" color="blue-gray" onClick={handleSoalSebelumnya} disabled={soalSekarangIndex === 0} className="flex items-center gap-2">
              <ArrowLeftIcon className="h-5 w-5" /> Soal Sebelumnya
            </Button>
            <Button variant={statusRaguRagu[soalSekarang.id] ? "filled" : "outlined"} color="amber" onClick={handleTandaiRaguRagu} className="flex items-center gap-2">
              <FlagIcon className="h-5 w-5"/> {statusRaguRagu[soalSekarang.id] ? "Batal Ragu" : "Ragu-ragu"}
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
        <NavigasiSoalSidebar
            soalList={soalListDenganStatusRagu}
            soalSekarangIndex={soalSekarangIndex}
            setSoalSekarangIndex={setSoalSekarangIndex}
            jawabanUser={jawabanUser}
            statusRaguRagu={statusRaguRagu}
            isOpen={navigasiSoalOpen}
            toggleSidebar={() => setNavigasiSoalOpen(prev => !prev)}
        />
      </div>
      <Dialog open={openDialogSelesai} handler={() => setOpenDialogSelesai(false)} size="xs">
        {/* ... (Dialog tetap sama) ... */}
        <DialogHeader><Typography variant="h5" color="blue-gray">Konfirmasi Selesai Ujian</Typography></DialogHeader>
        <DialogBody divider className="text-gray-700">Apakah Anda yakin ingin menyelesaikan dan mengumpulkan ujian ini? Jawaban tidak bisa diubah lagi.</DialogBody>
        <DialogFooter>
          <Button variant="text" color="blue-gray" onClick={() => setOpenDialogSelesai(false)} className="mr-1"><span>Batal</span></Button>
          <Button variant="gradient" color="green" onClick={() => handleSelesaiUjianCallback()}><span>Ya, Kumpulkan</span></Button>
        </DialogFooter>
      </Dialog>
    </div>
  );
}