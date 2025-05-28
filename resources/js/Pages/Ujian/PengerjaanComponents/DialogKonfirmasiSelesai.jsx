import React from 'react';
import { Button, Dialog, DialogHeader, DialogBody, DialogFooter, Typography } from "@material-tailwind/react";

export default function DialogKonfirmasiSelesai({ open, onClose, onConfirm }) {
  return (
    <Dialog open={open} handler={onClose} size="xs">
      <DialogHeader>
        <Typography variant="h5" color="blue-gray">Konfirmasi Selesai Ujian</Typography>
      </DialogHeader>
      <DialogBody divider className="text-gray-700">
        Apakah Anda yakin ingin menyelesaikan dan mengumpulkan ujian ini? Jawaban tidak bisa diubah lagi setelah dikumpulkan.
      </DialogBody>
      <DialogFooter>
        <Button 
          variant="text" 
          color="blue-gray" 
          onClick={onClose} 
          className="mr-1"
        >
          <span>Batal</span>
        </Button>
        <Button 
          variant="gradient" 
          color="green" 
          onClick={onConfirm}
        >
          <span>Ya, Kumpulkan</span>
        </Button>
      </DialogFooter>
    </Dialog>
  );
}