import React from 'react';
import { Card, CardBody, Typography, Button, Chip } from "@material-tailwind/react";
import { Link } from '@inertiajs/react';
import { DocumentMagnifyingGlassIcon, CheckCircleIcon, XCircleIcon, CalendarDaysIcon, AcademicCapIcon } from "@heroicons/react/24/solid";

export default function HistoriUjianRingkasanCard({ histori }) {
  const lulus = histori.skor >= (histori.kkm || 0); // Default KKM 0 jika tidak ada
  const statusKelulusanText = histori.skor === null || histori.skor === undefined ? "Belum Dinilai" : (lulus ? "Lulus" : "Tidak Lulus");
  const statusKelulusanColor = histori.skor === null || histori.skor === undefined ? "blue-gray" : (lulus ? "green" : "red");

  return (
    <Card className="w-full shadow-md hover:shadow-lg transition-shadow duration-300 border border-blue-gray-100 overflow-hidden flex flex-col">
      <CardBody className="p-5 flex flex-col flex-grow">
        <div className="mb-3">
          <div className="flex justify-between items-start mb-1">
            <Typography variant="h6" color="blue-gray" className="font-semibold leading-tight flex-grow mr-2">
              {histori.namaUjian || "Nama Ujian Tidak Tersedia"}
            </Typography>
            <Chip 
                value={statusKelulusanText}
                color={statusKelulusanColor}
                size="sm"
                icon={
                    (histori.skor !== null && histori.skor !== undefined) ? (
                        lulus ? <CheckCircleIcon className="h-4 w-4 stroke-white stroke-2"/> : <XCircleIcon className="h-4 w-4 stroke-white stroke-2"/>
                    ) : undefined
                }
                className="text-white capitalize flex-shrink-0"
            />
          </div>
          <div className="flex items-center text-xs text-blue-gray-600 mb-1">
            <AcademicCapIcon className="h-4 w-4 mr-1.5 opacity-70" />
            <span>{histori.namaMataKuliah || "N/A"}</span>
          </div>
          <div className="flex items-center text-xs text-gray-600">
            <CalendarDaysIcon className="h-4 w-4 mr-1.5 opacity-70" />
            <span>{histori.tanggalPengerjaan || "N/A"}</span>
          </div>
        </div>
        
        <Typography variant="h4" color={lulus && histori.skor !== null ? "green" : (histori.skor !== null ? "red" : "blue-gray")} className="mb-3 font-bold text-center">
          Skor: {histori.skor === null || histori.skor === undefined ? "-" : histori.skor}
        </Typography>
        
        <div className="mt-auto"> {/* Mendorong tombol ke bawah */}
          <Link href={route('ujian.hasil.detail', { id_attempt: histori.id_pengerjaan })}>
            <Button 
              variant="gradient" 
              color="blue" 
              size="sm" 
              fullWidth
              className="flex items-center justify-center gap-2"
            >
              <DocumentMagnifyingGlassIcon className="h-5 w-5" />
              Lihat Pembahasan
            </Button>
          </Link>
        </div>
      </CardBody>
    </Card>
  );
}