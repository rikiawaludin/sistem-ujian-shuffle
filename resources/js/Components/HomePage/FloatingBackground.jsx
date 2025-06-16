import React from 'react';

const FloatingBackground = () => {
    return (
        <div className="absolute top-0 left-0 right-0 h-[55vh] overflow-hidden">
            <div className="absolute inset-0 bg-teal-50" />

            {/* Bottom border line tetap dipertahankan sebagai pemisah yang halus */}
            <div className="absolute bottom-0 left-0 right-0 h-px">
                <div className="h-full bg-gradient-to-r from-transparent via-gray-300 to-transparent" />
            </div>
        </div>
    );
};

export default FloatingBackground;