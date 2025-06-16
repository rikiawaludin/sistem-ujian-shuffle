import React from 'react';
import { Card, CardBody, Typography, Button } from '@material-tailwind/react';
import { BookOpenIcon, ClockIcon, CheckCircleIcon, CalendarDaysIcon } from '@heroicons/react/24/solid';

export default function UjianListItem({ ujian }) {
    const getStatusInfo = () => {
        switch (ujian.status) {
            case 'Aktif':
                return { color: 'blue', icon: <PlayCircleIcon className="w-5 h-5" />, buttonText: 'Kerjakan' };
            case 'Mendatang':
                return { color: 'gray', icon: <CalendarDaysIcon className="w-5 h-5" />, buttonText: 'Detail' };
            case 'Selesai':
                return { color: 'green', icon: <CheckCircleIcon className="w-5 h-5" />, buttonText: 'Lihat Hasil' };
            default:
                return { color: 'gray', icon: <ClockIcon className="w-5 h-5" />, buttonText: 'Info' };
        }
    };

    const { color, icon, buttonText } = getStatusInfo();

    return (
        <Card variant="outlined" className="w-full">
            <CardBody className="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 p-4">
                <div className="flex items-start gap-4">
                    <div className={`p-2 rounded-lg bg-${color}-50 text-${color}-600`}>
                        {icon}
                    </div>
                    <div>
                        <Typography variant="h6" color="blue-gray" className="mb-1">{ujian.judul}</Typography>
                        <div className="flex items-center gap-2 text-sm text-gray-600">
                            <BookOpenIcon className="w-4 h-4" />
                            <span>{ujian.matakuliah}</span>
                        </div>
                        <div className="flex items-center gap-2 text-sm text-gray-600 mt-1">
                            <ClockIcon className="w-4 h-4" />
                            <span>{ujian.durasi} menit</span>
                        </div>
                    </div>
                </div>
                <div className="w-full md:w-auto mt-4 md:mt-0 flex-shrink-0">
                    <Button color={color} size="sm" className="w-full md:w-auto" disabled={ujian.status === 'Mendatang'}>
                        {buttonText}
                    </Button>
                </div>
            </CardBody>
        </Card>
    );
}