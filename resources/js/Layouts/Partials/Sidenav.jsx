import PropTypes from "prop-types";
import { Link, usePage } from '@inertiajs/react';
import { XMarkIcon } from "@heroicons/react/24/outline";
import {
  Button,
  IconButton,
  Typography,
} from "@material-tailwind/react";
import { useMaterialTailwindController, setOpenSidenav } from '@/Context/MaterialTailwindContext'; // Pastikan path ini benar

export function Sidenav({ brandName, routesData }) {
  const [controller, dispatch] = useMaterialTailwindController();
  const { sidenavColor, sidenavType, openSidenav } = controller; // 'openSidenav' sudah ada di sini
  const { url } = usePage();

  const sidenavTypes = {
    dark: "bg-gradient-to-br from-gray-800 to-gray-900",
    white: "bg-white shadow-sm",
    transparent: "bg-transparent",
  };

  const currentSidenavType = sidenavType === "dark" ? "dark" : "white";

  return (
    <aside
      className={`${sidenavTypes[currentSidenavType]} ${
        openSidenav ? "translate-x-0" : "-translate-x-80"
      } fixed inset-y-0 left-0 z-50 my-4 ml-4 h-[calc(100vh-32px)] w-72 rounded-xl 
         transition-transform duration-300 border border-blue-gray-100`}
    >
      <div className="relative">
        <Link href={route('dashboard')} className="py-6 px-8 text-center block">
          <Typography
            variant="h6"
            color={currentSidenavType === "dark" ? "white" : "blue-gray"}
          >
            {brandName}
          </Typography>
        </Link>
        <IconButton
          variant="text"
          color={currentSidenavType === "dark" ? "white" : "blue-gray"}
          size="sm"
          ripple={false}
          className="absolute right-0 top-0 grid rounded-br-none rounded-tl-none"
          onClick={() => setOpenSidenav(dispatch, false)}
        >
          <XMarkIcon strokeWidth={2.5} className="h-5 w-5" />
        </IconButton>
      </div>
      <div className="m-4">
        {routesData.map(({ layout, title, pages }, key) => (
          <ul key={key} className="mb-4 flex flex-col gap-1">
            {title && (
              <li className="mx-3.5 mt-4 mb-2">
                <Typography
                  variant="small"
                  color={currentSidenavType === "dark" ? "white" : "blue-gray"}
                  className="font-black uppercase opacity-75"
                >
                  {title}
                </Typography>
              </li>
            )}
            {pages.map(({ icon, name, path }) => {
              const isActive = url.startsWith(path);
              return (
                <li key={name}>
                  <Link href={path}>
                    <Button
                      variant={isActive ? "filled" : "text"}
                      color={
                        isActive
                          ? "gray"
                          : currentSidenavType === "dark"
                          ? "white"
                          : "blue-gray"
                      }
                      className="flex items-center gap-4 px-4 capitalize w-full"
                    >
                      {icon}
                      <Typography
                        color="inherit"
                        className="font-medium capitalize"
                      >
                        {name}
                      </Typography> {/* Diperbaiki: tag ini sebelumnya belum ditutup */}
                    </Button>
                  </Link>
                </li>
              );
            })}
          </ul>
        ))}
      </div>
    </aside>
  );
}

Sidenav.defaultProps = {
  brandName: "Sistem Ujian Anda",
  routesData: [],
};

Sidenav.propTypes = {
  brandName: PropTypes.string,
  routesData: PropTypes.arrayOf(PropTypes.object).isRequired,
};

export default Sidenav;
