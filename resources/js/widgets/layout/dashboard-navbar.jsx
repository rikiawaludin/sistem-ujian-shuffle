import { Link, usePage } from '@inertiajs/react';

// Impor komponen dari Material Tailwind
import {
  Navbar,
  Typography,
  Button,     // Impor jika Anda akan menggunakan tombol Sign In atau lainnya
  IconButton,
  Breadcrumbs,
  Input,      // Impor jika Anda akan menggunakan input Search
  Menu,       // Impor jika Anda akan menggunakan Menu Notifikasi/User
  MenuHandler,
  MenuList,
  MenuItem,
  Avatar,
} from "@material-tailwind/react";

// Impor ikon dari Heroicons (HANYA IKON)
import {
  UserCircleIcon,
  Cog6ToothIcon,
  BellIcon,
  ClockIcon,
  CreditCardIcon,
  Bars3Icon,      // Ikon untuk toggle Sidenav
} from "@heroicons/react/24/solid";

// Impor dari Konteks Material Tailwind Anda
import {
  useMaterialTailwindController,
  setOpenConfigurator, // Jika masih menggunakan configurator
  setOpenSidenav,
} from '@/Context/MaterialTailwindContext'; // Pastikan path ini sudah benar

export function DashboardNavbar() {
  const [controller, dispatch] = useMaterialTailwindController();
  const { fixedNavbar, openSidenav } = controller;
  const { url } = usePage();

  const pathSegments = url.split("/").filter((el) => el !== "");
  let pageTitle = "Dashboard";
  let breadcrumbLayout = "";
  let breadcrumbPage = "";

  if (pathSegments.length > 0) {
    pageTitle = pathSegments[pathSegments.length - 1];
    breadcrumbPage = pageTitle;
    if (pathSegments.length > 1) {
      breadcrumbLayout = pathSegments[0];
    }
  }

  return (
    <Navbar
      color={fixedNavbar ? "white" : "transparent"}
      className={`rounded-xl transition-all ${
        fixedNavbar
          ? "sticky top-4 z-40 py-3 shadow-md shadow-blue-gray-500/5"
          : "px-0 py-1"
      }`}
      fullWidth
      blurred={fixedNavbar}
    >
      {/* Kontainer utama untuk item navbar, menggunakan flexbox */}
      <div className="flex items-center justify-between w-full">

        {/* Bagian Kiri Navbar: Tombol Toggle Sidenav + Breadcrumbs/Judul Halaman */}
        <div className="flex items-center">
          {/* Tombol Toggle Sidenav DIPINDAHKAN KE SINI */}
          <IconButton
            variant="text"
            color="blue-gray"
            className="grid mr-3" // Tambahkan margin kanan jika perlu untuk jarak
            onClick={() => setOpenSidenav(dispatch, !openSidenav)}
          >
            <Bars3Icon strokeWidth={3} className="h-6 w-6 text-blue-gray-500" />
          </IconButton>

          {/* Breadcrumbs dan Judul Halaman */}
          <div className="capitalize">
            <Breadcrumbs
              className={`bg-transparent p-0 transition-all ${
                fixedNavbar ? "mt-1" : ""
              }`}
            >
              {breadcrumbLayout && (
                <Link href={route(breadcrumbLayout) || `/${breadcrumbLayout}`}>
                  <Typography
                    variant="small"
                    color="blue-gray"
                    className="font-normal opacity-50 transition-all hover:text-blue-500 hover:opacity-100"
                  >
                    {breadcrumbLayout}
                  </Typography>
                </Link>
              )}
              <Typography
                variant="small"
                color="blue-gray"
                className="font-normal"
              >
                {breadcrumbPage}
              </Typography>
            </Breadcrumbs>
            <Typography variant="h6" color="blue-gray">
              {pageTitle}
            </Typography>
          </div>
        </div>

        {/* Bagian Kanan Navbar: Search, Tombol Sign In, Menu Notifikasi, dll. */}
        <div className="flex items-center">
          {/* Input Search (Contoh, tampil di layar md ke atas) */}
          <div className="hidden md:mr-4 md:w-56 md:block">
            {/* <Input label="Search" /> */} {/* Aktifkan jika digunakan & pastikan Input diimpor */}
          </div>
        </div>
      </div>
    </Navbar>
  );
}

export default DashboardNavbar;