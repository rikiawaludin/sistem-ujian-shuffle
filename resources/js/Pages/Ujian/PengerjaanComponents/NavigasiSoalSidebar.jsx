import React from 'react';
import { Typography, Button, IconButton } from "@material-tailwind/react";
import { XMarkIcon, FlagIcon } from "@heroicons/react/24/solid";

export default function NavigasiSoalSidebar({
  soalList,
  soalSekarangIndex,
  setSoalSekarangIndex,
  jawabanUser,
  statusRaguRagu,
  isOpen,
  toggleSidebar
}) {
  return (
    <>
      {/* Overlay (hanya muncul di mobile (<md) saat sidebar terbuka untuk efek menimpa) */}
      {isOpen && (<div className="fixed inset-0 z-50 bg-black/30 md:hidden" onClick={toggleSidebar} aria-label="close sidebar"></div>)}

      {/* Sidebar itu sendiri */}
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
        <div className="flex-grow p-4 grid grid-cols-4 sm:grid-cols-5 gap-2 overflow-y-auto content-start">
          {soalList.map((soal, index) => {
            // Pastikan soal memiliki id, jika tidak, gunakan index sebagai key fallback (kurang ideal)
            const key = soal.id !== undefined ? soal.id : `soal-nav-${index}`;
            const sudahDijawab = soal.id !== undefined && jawabanUser[soal.id] !== null && jawabanUser[soal.id] !== "";
            const isRagu = soal.id !== undefined && statusRaguRagu[soal.id];

            return (
              <Button
                key={key}
                variant={soalSekarangIndex === index ? "filled" : (sudahDijawab ? "gradient" : "outlined")}
                color={soalSekarangIndex === index ? "blue" : (sudahDijawab ? (isRagu ? "amber" : "green") : "blue-gray")}
                size="sm"
                className="aspect-square p-0 text-xs relative !min-w-[unset] !w-full"
                onClick={() => {
                  setSoalSekarangIndex(index);
                  if (isOpen && window.innerWidth < 768) { // 768px adalah breakpoint md Tailwind
                    toggleSidebar();
                  }
                }}
                title={isRagu ? "Soal Ragu-ragu" : (sudahDijawab ? "Sudah Dijawab" : "Belum Dijawab")}
              >
                {isRagu && <FlagIcon className="h-3 w-3 text-white absolute top-0.5 right-0.5" />}
                {soal.nomor || (index + 1)} {/* Menampilkan nomor soal atau fallback ke index + 1 */}
              </Button>
            );
          })}
        </div>
      </div>
    </>
  );
}