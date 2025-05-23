import React, { createContext, useContext, useReducer, useMemo } from "react";
import PropTypes from "prop-types";

// 1. Buat Context
const MaterialTailwind = createContext(null);
MaterialTailwind.displayName = "MaterialTailwindContext";

// 2. Definisikan Reducer (untuk mengelola state)
function reducer(state, action) {
  switch (action.type) {
    case "OPEN_SIDENAV": {
      return { ...state, openSidenav: action.value };
    }
    case "SIDENAV_TYPE": {
      return { ...state, sidenavType: action.value };
    }
    case "SIDENAV_COLOR": {
      return { ...state, sidenavColor: action.value };
    }
    case "OPEN_CONFIGURATOR": {
      return { ...state, openConfigurator: action.value };
    }
    case "FIXED_NAVBAR": {
      return { ...state, fixedNavbar: action.value };
    }
    default: {
      throw new Error(`Unhandled action type: ${action.type}`);
    }
  }
}

// 3. Buat Provider Component
export function MaterialTailwindControllerProvider({ children }) {
  const initialState = {
    openSidenav: false,
    sidenavType: "dark", // Default ke dark sesuai keinginan Anda
    sidenavColor: "blue", // Warna default, bisa Anda sesuaikan
    transparentNavbar: true,
    fixedNavbar: false,
    openConfigurator: false,
  };

  const [controller, dispatch] = useReducer(reducer, initialState);
  const value = useMemo(() => [controller, dispatch], [controller, dispatch]);

  return (
    <MaterialTailwind.Provider value={value}>
      {children}
    </MaterialTailwind.Provider>
  );
}

// 4. Buat Custom Hook untuk menggunakan context
export function useMaterialTailwindController() {
  const context = useContext(MaterialTailwind);
  if (!context) {
    throw new Error(
      "useMaterialTailwindController must be used within a MaterialTailwindControllerProvider"
    );
  }
  return context;
}

// Prop types untuk Provider
MaterialTailwindControllerProvider.propTypes = {
  children: PropTypes.node.isRequired,
};

// 5. Action Creators (fungsi untuk mempermudah dispatch)
export const setOpenSidenav = (dispatch, value) =>
  dispatch({ type: "OPEN_SIDENAV", value });
export const setSidenavType = (dispatch, value) =>
  dispatch({ type: "SIDENAV_TYPE", value });
export const setSidenavColor = (dispatch, value) =>
  dispatch({ type: "SIDENAV_COLOR", value });
export const setOpenConfigurator = (dispatch, value) =>
  dispatch({ type: "OPEN_CONFIGURATOR", value });
export const setFixedNavbar = (dispatch, value) =>
  dispatch({ type: "FIXED_NAVBAR", value });