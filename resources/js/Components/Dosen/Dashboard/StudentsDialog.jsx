import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
} from "@/components/ui/dialog"; // Pastikan path ini sesuai
import { StudentsTable } from "./StudentsTable";

export function StudentsDialog({ subject, open, onOpenChange }) {
  if (!subject) return null;

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="sm:max-w-[625px]">
        <DialogHeader>
          <DialogTitle>Daftar Mahasiswa: {subject.name}</DialogTitle>
          <DialogDescription>
            Berikut adalah daftar mahasiswa yang telah mengikuti ujian mata kuliah ini.
          </DialogDescription>
        </DialogHeader>
        <div className="max-h-[60vh] overflow-y-auto pr-4">
          <StudentsTable students={subject.students} />
        </div>
      </DialogContent>
    </Dialog>
  );
}