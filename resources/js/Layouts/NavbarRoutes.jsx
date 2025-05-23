// Contoh ikon jika diperlukan (opsional untuk navbar atas sederhana)
// import { DocumentTextIcon, UserCircleIcon, ArrowLeftOnRectangleIcon } from "@heroicons/react/24/outline";

// const iconStyle = "w-5 h-5"; // Jika menggunakan ikon

export const navMenuItems = [
  {
    name: "Mulai Ujian",
    path: "/ujian/mulai", // Sesuaikan dengan path Anda
    // icon: <DocumentTextIcon className={iconStyle} />, // Opsional
  },
  {
    name: "Daftar Ujian",
    path: "/ujian/daftar", // Sesuaikan dengan path Anda
    // icon: <UserCircleIcon className={iconStyle} />, // Opsional
  },
  {
    name: "Riwayat Ujian",
    path: "/ujian/riwayat", // Sesuaikan dengan path Anda
    // icon: <UserCircleIcon className={iconStyle} />, // Opsional
  },
  {
    name: "Profil Saya",
    path: "/profile", // Menggunakan path profile yang mungkin sudah ada
    // icon: <UserCircleIcon className={iconStyle} />, // Opsional
  },
];

export default navMenuItems;