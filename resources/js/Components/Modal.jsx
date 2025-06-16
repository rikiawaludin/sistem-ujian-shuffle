import React from "react";
import {
    Dialog,
    DialogHeader,
    DialogBody,
    DialogFooter,
    IconButton,
} from "@material-tailwind/react";
import { XMarkIcon } from "@heroicons/react/24/solid";

export default function Modal({ isOpen, onClose, title, children }) {
    return (
        <Dialog open={isOpen} handler={onClose} size="lg">
            <DialogHeader className="justify-between">
                <h2 className="text-2xl font-bold text-blue-gray-800">{title}</h2>
                <IconButton color="blue-gray" size="sm" variant="text" onClick={onClose}>
                    <XMarkIcon strokeWidth={2} className="h-5 w-5" />
                </IconButton>
            </DialogHeader>
            <DialogBody divider className="h-[60vh] overflow-y-auto">
                <div className="space-y-4">
                    {children}
                </div>
            </DialogBody>
        </Dialog>
    );
}