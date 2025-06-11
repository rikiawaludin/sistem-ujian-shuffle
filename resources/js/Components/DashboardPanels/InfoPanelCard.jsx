import React from "react";
import { Card, CardBody, Typography } from "@material-tailwind/react";

export default function InfoPanelCard({ icon, title, count }) {
    return (
        <Card className="text-center shadow-lg border border-blue-gray-50 hover:shadow-blue-gray-500/20 transition-shadow">
            <CardBody className="px-6 py-8">
                <div className="mx-auto mb-4 grid h-14 w-14 place-items-center rounded-full bg-gradient-to-tr from-blue-600 to-blue-400 text-white shadow-lg shadow-blue-500/40">
                    {icon}
                </div>
                <Typography variant="h2" color="blue-gray" className="mb-2 font-bold">
                    {count}
                </Typography>
                <Typography className="font-normal text-blue-gray-500">
                    {title}
                </Typography>
            </CardBody>
        </Card>
    );
}