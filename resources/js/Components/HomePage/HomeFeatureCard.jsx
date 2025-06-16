import React from 'react';

// DIUBAH: Properti 'buttonText' dihapus dari daftar props
export default function HomeFeatureCard({ icon, title, description, variant = 'primary' }) {

    // Fungsi ini tidak lagi dipakai untuk background, tapi kita bisa biarkan untuk referensi
    // atau jika ingin dikembangkan lagi nanti. Namun, untuk lebih bersih, kita hapus saja.
    // const getVariantClasses = () => { ... };

    // Warna ikon tetap bervariasi sesuai variant untuk memberikan aksen
    const getIconColor = () => {
        switch (variant) {
            case 'primary': return 'text-blue-600';
            case 'secondary': return 'text-green-600';
            case 'tertiary': return 'text-purple-600';
            default: return 'text-blue-600';
        }
    };

    // DIUBAH: Fungsi getButtonClasses() sudah tidak diperlukan dan dihapus.

    return (
        // DIUBAH: Dulu ada space-y-6, dikurangi jadi space-y-4 karena tombol hilang.
        // Background kartu dibuat statis menjadi putih dengan border.
        <div className="relative p-8 h-full rounded-2xl border bg-white border-gray-200 backdrop-blur-sm shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2">
            <div className="flex flex-col items-center text-center space-y-4">
                <div className={`p-4 rounded-full bg-white shadow-md ${getIconColor()}`}>
                    {React.cloneElement(icon, { className: "w-8 h-8" })}
                </div>

                <div className="space-y-3">
                    <h3 className="text-2xl font-bold text-gray-800">{title}</h3>
                    <p className="text-gray-600 leading-relaxed">{description}</p>
                </div>

                {/* DIUBAH: Elemen <a> (tombol) yang ada di sini telah dihapus seluruhnya. */}

            </div>
        </div>
    );
}