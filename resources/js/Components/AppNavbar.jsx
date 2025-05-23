// resources/js/Components/AppNavbar.jsx
import React from "react";
import { Link, usePage, router } from '@inertiajs/react'; // Impor 'router' dari Inertia
import {
  Navbar as MaterialTailwindNavbar,
  MobileNav,
  Typography,
  Button,
  IconButton,
  Menu,             // <-- Tambahkan Impor Menu
  MenuHandler,      // <-- Tambahkan Impor MenuHandler
  MenuList,         // <-- Tambahkan Impor MenuList
  MenuItem,         // <-- Tambahkan Impor MenuItem
  Avatar,           // <-- Tambahkan Impor Avatar
} from "@material-tailwind/react";
import { Bars3Icon, XMarkIcon, UserCircleIcon, ArrowLeftOnRectangleIcon } from "@heroicons/react/24/outline"; // Contoh ikon untuk dropdown

// Pastikan path ini benar ke file routes navbar Anda
import navMenuItems from "@/Layouts/NavbarRoutes"; // CONTOH PATH, SESUAIKAN!

export function AppNavbar() {
  const [openNav, setOpenNav] = React.useState(false);
  const { props } = usePage();
  const { auth } = props;

  React.useEffect(() => {
    window.addEventListener(
      "resize",
      () => window.innerWidth >= 960 && setOpenNav(false),
    );
  }, []);

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
          <Link
            href={path}
            className="flex items-center transition-colors hover:text-gray-600"
          >
            {name}
          </Link>
        </Typography>
      ))}
    </ul>
  );

  // Fungsi untuk menangani logout
  const handleLogout = () => {
    router.post(route('logout'));
  };

  // Fungsi untuk navigasi ke profil
  const handleProfile = () => {
    // Ganti 'profile.show' dengan nama rute profil Anda yang sebenarnya jika berbeda
    router.get(route('profile.show')); 
  };

  return (
    <MaterialTailwindNavbar 
      className="sticky top-0 z-50 h-max max-w-full rounded-none px-4 py-2 lg:px-8 lg:py-3 border-b border-blue-gray-100"
    >
      <div className="max-w-screen-xl mx-auto">
        <div className="flex items-center w-full"> 
          <div className="flex-shrink-0">
            <Link
              href={route('dashboard')}
              className="cursor-pointer flex items-center"
            >
              <img 
                src="/images/stmik.png" // GANTI DENGAN NAMA FILE LOGO ANDA!
                alt="Logo STMIK Bandung" 
                className="h-8 w-auto" 
              />
            </Link>
          </div>

          <div className="hidden lg:flex flex-grow justify-center">
            {navList}
          </div>

          <div className="flex items-center gap-x-2 flex-shrink-0">
            {/* User Avatar dan Dropdown Menu untuk layar besar */}
            <div className="hidden lg:flex lg:items-center lg:gap-x-1">
              {auth.user ? (
                <Menu placement="bottom-end">
                  <MenuHandler>
                    <Avatar
                      src={auth.user.profile_photo_url || '/images/default-avatar.png'} // Gunakan foto profil user atau default
                      alt={auth.user.name}
                      size="sm"
                      variant="circular"
                      className="cursor-pointer"
                      withBorder={true}
                      color="blue-gray" // Warna border jika foto tidak ada/gagal load
                    />
                  </MenuHandler>
                  <MenuList className="w-max">
                    <MenuItem className="flex items-center gap-2" disabled>
                      <UserCircleIcon strokeWidth={2} className="h-4 w-4" />
                      <Typography variant="small" color="blue-gray" className="font-normal">
                        {auth.user.name}
                      </Typography>
                    </MenuItem>
                    {/* Anda bisa menambahkan link profil di sini jika ada */}
                    <MenuItem className="flex items-center gap-2" onClick={handleProfile}>
                       <UserCircleIcon strokeWidth={2} className="h-4 w-4" /> {/* Atau ikon profil lain */}
                      <Typography variant="small" color="blue-gray" className="font-normal">
                        Profil Saya
                      </Typography>
                    </MenuItem>
                    <hr className="my-2 border-blue-gray-50" />
                    <MenuItem className="flex items-center gap-2 text-red-500 hover:!bg-red-50 hover:!text-red-600 focus:!bg-red-50 focus:!text-red-600" onClick={handleLogout}>
                      <ArrowLeftOnRectangleIcon strokeWidth={2} className="h-4 w-4" />
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

            {/* Tombol Hamburger (hanya tampil di layar kecil) */}
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

      {/* Navigasi untuk Tampilan Mobile */}
      <MobileNav open={openNav}>
        <div className="container mx-auto px-4 sm:px-6 py-2">
          {navList}
          {/* Tombol Otentikasi untuk mobile */}
          <div className="flex flex-col gap-y-2 mt-4 border-t border-blue-gray-50 pt-4">
            {auth.user ? (
              <>
                <Button fullWidth variant="outlined" size="sm" onClick={handleProfile} className="flex items-center justify-center gap-2">
                  <UserCircleIcon strokeWidth={2} className="h-4 w-4" />
                  Profil Saya
                </Button>
                <Button fullWidth variant="gradient" size="sm" onClick={handleLogout} className="flex items-center justify-center gap-2 !bg-red-500">
                  <ArrowLeftOnRectangleIcon strokeWidth={2} className="h-4 w-4" />
                  Log Out
                </Button>
              </>
            ) : (
              <>
                <Link href={route('login')} className="w-full">
                  <Button fullWidth variant="text" size="sm">
                    <span>Log In</span>
                  </Button>
                </Link>
                <Link href={route('register')} className="w-full">
                  <Button fullWidth variant="gradient" size="sm">
                    <span>Sign Up</span>
                  </Button>
                </Link>
              </>
            )}
          </div>
        </div>
      </MobileNav>
    </MaterialTailwindNavbar>
  );
}

export default AppNavbar;