import {
  HomeIcon,
  UserCircleIcon,
  TableCellsIcon,
  BellIcon,
  ArrowRightOnRectangleIcon,
  UserPlusIcon,
} from "@heroicons/react/24/solid";

const iconStyle = "h-5 w-5 text-inherit";

export const routes = [ // Saya mengganti nama variabel menjadi 'routes' agar lebih umum
  {
    layout: "dashboard", // Ini bisa Anda gunakan untuk mengelompokkan rute jika diperlukan
    pages: [
      {
        icon: <HomeIcon className={iconStyle} />,
        name: "Dashboard",
        path: "/dashboard", // Path ini akan dihandle oleh router Inertia/React
      },
      {
        icon: <UserCircleIcon className={iconStyle} />,
        name: "Profile",
        path: "/profile",
      },
      {
        icon: <TableCellsIcon className={iconStyle} />,
        name: "Tables",
        path: "/tables",
      },
      {
        icon: <BellIcon className={iconStyle} />,
        name: "Notifications",
        path: "/notifications",
      },
    ],
  },
  {
    title: "AUTH PAGES",
    layout: "auth",
    pages: [
      {
        icon: <ArrowRightOnRectangleIcon className={iconStyle} />,
        name: "Sign In",
        path: "/login", // Sesuaikan dengan rute login Laravel Anda
      },
      {
        icon: <UserPlusIcon className={iconStyle} />,
        name: "Sign Up",
        path: "/register", // Sesuaikan dengan rute register Laravel Anda
      },
    ],
  },
];

export default routes;