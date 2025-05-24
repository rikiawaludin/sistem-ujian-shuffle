import React from "react";
import { Link, usePage, router } from '@inertiajs/react';
import {
  Navbar as MaterialTailwindNavbar,
  Collapse, // Mengganti MobileNav dengan Collapse
  Typography,
  Button,
  IconButton,
  Menu,
  MenuHandler,
  MenuList,
  MenuItem,
  Avatar,
} from "@material-tailwind/react";
import { Bars3Icon, XMarkIcon, UserCircleIcon, ArrowLeftIcon } from "@heroicons/react/24/outline";


import navMenuItems from "@/Layouts/NavbarRoutes"; // Saya asumsikan path ini benar berdasarkan unggahan Anda sebelumnya

export function AppNavbar() {
  const [openNav, setOpenNav] = React.useState(false);
  const { props } = usePage();
  const { auth } = props;

  React.useEffect(() => {
    window.addEventListener(
      "resize",
      () => window.innerWidth >= 960 && setOpenNav(false),
    );
    // Cleanup event listener saat komponen di-unmount
    return () => {
      window.removeEventListener(
        "resize",
        () => window.innerWidth >= 960 && setOpenNav(false),
      );
    };
  }, []);

  // Fungsi untuk menangani klik pada item navigasi (termasuk smooth scroll)
  const handleNavClick = (e, path) => {
    if (path.startsWith('#')) {
      e.preventDefault(); // Mencegah default anchor jump
      const targetId = path.substring(1);
      const targetElement = document.getElementById(targetId);
      if (targetElement) {
        targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    }
    // Untuk navigasi Inertia atau anchor, tutup MobileNav/Collapse jika terbuka
    setOpenNav(false);
  };

  const navList = (
    <ul className="mt-2 mb-4 flex flex-col gap-2 lg:mb-0 lg:mt-0 lg:flex-row lg:items-center lg:gap-6">
      {navMenuItems.map(({ name, path }) => (
        <Typography
          key={name}
          as="li"
          variant="small"
          color="blue-gray"
          className="p-1 font-normal"
        >
          {path.startsWith('#') ? (
            <a // Menggunakan tag <a> untuk anchor link
              href={path}
              className="flex items-center transition-colors hover:text-gray-600 cursor-pointer"
              onClick={(e) => handleNavClick(e, path)}
            >
              {name}
            </a>
          ) : (
            <Link // Menggunakan Link Inertia untuk navigasi halaman
              href={path}
              className="flex items-center transition-colors hover:text-gray-600"
              onClick={(e) => handleNavClick(e, path)} // Tetap panggil untuk menutup nav di mobile
            >
              {name}
            </Link>
          )}
        </Typography>
      ))}
    </ul>
  );

  const handleLogout = () => {
    router.post(route('logout'));
  };

  const handleProfile = () => {
    // Pastikan rute 'profile.show' ada atau ganti dengan nama rute profil yang benar
    if (route().has('profile.show')) {
        router.get(route('profile.show'));
    } else {
        console.warn("Rute 'profile.show' tidak ditemukan. Silakan periksa konfigurasi Ziggy atau nama rute Anda.");
        // Alternatif: router.get('/user/profile'); // jika path manual
    }
  };

  return (
    <MaterialTailwindNavbar
      className="sticky top-0 z-50 h-max max-w-full rounded-none px-4 py-2 lg:px-8 lg:py-3 border-b border-blue-gray-100"
    >
      <div className="max-w-screen-xl mx-auto"> {/* Container untuk membatasi lebar konten navbar */}
        <div className="flex items-center w-full"> {/* Kontainer flex utama untuk item-item navbar */}
          <div className="flex-shrink-0">
            <Link
              href={route('dashboard')} // Logo mengarah ke dashboard utama
              // Jika ingin logo juga smooth scroll ke atas di halaman dashboard yang sama:
              // href="#dashboard-top-content" // Pastikan ID ini ada di Dashboard.jsx
              // onClick={(e) => handleNavClick(e, "#dashboard-top-content")}
              className="cursor-pointer flex items-center"
            >
              <img
                src="/images/stmik.png" // Anda sudah mengganti ini, bagus!
                alt="Logo STMIK Bandung"
                className="h-8 w-auto" // Sesuaikan tinggi jika perlu
              />
            </Link>
          </div>

          <div className="hidden lg:flex flex-grow justify-center">
            {navList}
          </div>

          <div className="flex items-center gap-x-2 flex-shrink-0">
            <div className="hidden lg:flex lg:items-center lg:gap-x-1">
              {auth.user ? (
                <Menu placement="bottom-end">
                  <MenuHandler>
                    <Avatar
                      src={auth.user.profile_photo_url || '/images/default-avatar.png'}
                      alt={auth.user.name || 'Avatar Pengguna'}
                      size="sm"
                      variant="circular"
                      className="cursor-pointer"
                      withBorder={true}
                      color="blue-gray"
                    />
                  </MenuHandler>
                  <MenuList className="w-max">
                    <MenuItem className="flex items-center gap-2" disabled>
                      <UserCircleIcon strokeWidth={2} className="h-4 w-4" />
                      <Typography variant="small" color="blue-gray" className="font-normal">
                        {auth.user.name}
                      </Typography>
                    </MenuItem>
                    <MenuItem className="flex items-center gap-2" onClick={handleProfile}>
                       <UserCircleIcon strokeWidth={2} className="h-4 w-4" />
                      <Typography variant="small" color="blue-gray" className="font-normal">
                        Profil Saya
                      </Typography>
                    </MenuItem>
                    <hr className="my-2 border-blue-gray-50" />
                    <MenuItem className="flex items-center gap-2 text-red-500 hover:!bg-red-50 hover:!text-red-600 focus:!bg-red-50 focus:!text-red-600" onClick={handleLogout}>
                      <ArrowLeftIcon strokeWidth={2} className="h-4 w-4" />
                      <Typography variant="small" className="font-normal">
                        Log Out
                      </Typography>
                    </MenuItem>
                  </MenuList>
                </Menu>
              ) : (
                <>
                  <Link href={route('login')}>
                    <Button variant="text" size="sm">
                      <span>Log In</span>
                    </Button>
                  </Link>
                  <Link href={route('register')}>
                    <Button variant="gradient" size="sm">
                      <span>Sign Up</span>
                    </Button>
                  </Link>
                </>
              )}
            </div>

            <IconButton
              variant="text"
              className="h-6 w-6 text-inherit hover:bg-transparent focus:bg-transparent active:bg-transparent lg:hidden"
              ripple={false}
              onClick={() => setOpenNav(!openNav)}
            >
              {openNav ? (
                <XMarkIcon className="h-6 w-6" strokeWidth={2} />
              ) : (
                <Bars3Icon className="h-6 w-6" strokeWidth={2} />
              )}
            </IconButton>
          </div>
        </div>
      </div>

      {/* Mengganti MobileNav dengan Collapse */}
      <Collapse open={openNav} className="lg:hidden">
        <div className="container mx-auto px-4 sm:px-6 py-2">
          {navList} {/* navList akan menggunakan handleNavClick juga */}
          <div className="flex flex-col gap-y-2 mt-4 border-t border-blue-gray-50 pt-4">
            {auth.user ? (
              <>
                <Button fullWidth variant="outlined" size="sm" onClick={() => { handleProfile(); setOpenNav(false); }} className="flex items-center justify-center gap-2">
                  <UserCircleIcon strokeWidth={2} className="h-4 w-4" />
                  Profil Saya
                </Button>
                <Button fullWidth variant="gradient" size="sm" onClick={() => { handleLogout(); setOpenNav(false); }} className="flex items-center justify-center gap-2 !bg-red-500">
                  <ArrowLeftIcon strokeWidth={2} className="h-4 w-4" />
                  Log Out
                </Button>
              </>
            ) : (
              <>
                <Link href={route('login')} className="w-full" onClick={() => setOpenNav(false)}>
                  <Button fullWidth variant="text" size="sm">
                    <span>Log In</span>
                  </Button>
                </Link>
                <Link href={route('register')} className="w-full" onClick={() => setOpenNav(false)}>
                  <Button fullWidth variant="gradient" size="sm">
                    <span>Sign Up</span>
                  </Button>
                </Link>
              </>
            )}
          </div>
        </div>
      </Collapse>
    </MaterialTailwindNavbar>
  );
}

export default AppNavbar;