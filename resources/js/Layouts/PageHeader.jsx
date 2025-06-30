// resources/js/Components/Layout/PageHeader.jsx

import React from 'react';
import { Typography } from "@material-tailwind/react";

export default function PageHeader({ title, subtitle, icon, children }) {
    return (
        <div className="bg-gradient-to-r from-blue-600 to-indigo-700 text-white p-6 shadow-lg rounded-2xl mb-8">
            <div className="max-w-7xl mx-auto">
                <div className="flex items-center justify-between">

                    {/* Bagian Kiri: Ikon dan Judul */}
                    <div className="flex items-center space-x-4">
                        {icon && (
                            <div className="bg-white/20 p-3 rounded-full">
                                {/* Meng-clone ikon untuk menambahkan kelas CSS */}
                                {React.cloneElement(icon, { className: "h-8 w-8" })}
                            </div>
                        )}
                        <div>
                            <Typography variant="h4" className="font-bold text-white">
                                {title}
                            </Typography>
                            {subtitle && (
                                <Typography className="text-blue-100">
                                    {subtitle}
                                </Typography>
                            )}
                        </div>
                    </div>

                    {/* Bagian Kanan: Untuk Tombol Aksi (seperti tombol 'Kembali') */}
                    <div className="flex-shrink-0">
                        {children}
                    </div>

                </div>
            </div>
        </div>
    );
}