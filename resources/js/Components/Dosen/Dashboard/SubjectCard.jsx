import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { List } from 'lucide-react';

export function SubjectCard({ subject, onShowStudents }) {
  return (
    <Card className="flex flex-col">
      <CardHeader>
        {/* Judul Kartu sekarang adalah Nama Ujian */}
        <CardTitle>{subject.name}</CardTitle>
        {/* Deskripsi Kartu adalah Nama Mata Kuliah */}
        <CardDescription>{subject.subjectName}</CardDescription>
      </CardHeader>

      <CardContent className="flex-grow">
        {/* Karena kartu ini sudah spesifik untuk satu ujian, kita hanya butuh info Total Mahasiswa */}
        <div className="flex flex-col h-full space-y-1 p-4 rounded-lg bg-muted/50 items-center justify-center text-center">
          <span className="text-sm text-muted-foreground">Total Mahasiswa</span>
          <span className="font-semibold text-3xl">{subject.totalStudents}</span>
        </div>
      </CardContent>

      <CardFooter>
        <Button onClick={() => onShowStudents(subject)} className="w-full">
          <List className="mr-2 h-4 w-4" /> Lihat Daftar Mahasiswa
        </Button>
      </CardFooter>
    </Card>
  );
}


// export function SubjectCard({ subject, onShowStudents }) {
//   return (
//     <Card className="flex flex-col">
//       <CardHeader>
//         <CardTitle>{subject.name}</CardTitle>
//         {/* Deskripsi kartu sekarang adalah nama mata kuliah */}
//         <CardDescription>{subject.subjectName}</CardDescription>
//       </CardHeader>

//       {/* Konten dengan dua kotak: Semester dan Total Mahasiswa */}
//       <CardContent className="grid grid-cols-2 gap-4 flex-grow">
        
//         {/* KOTAK KIRI: SEMESTER */}
//         <div className="flex flex-col space-y-1.5 p-4 rounded-lg bg-muted/50 text-center justify-center">
//           <span className="text-sm text-muted-foreground">Semester</span>
//           <span className="font-semibold text-lg leading-tight pt-1" title={subject.semester}>
//             {subject.semester}
//           </span>
//         </div>

//         {/* KOTAK KANAN: TOTAL MAHASISWA */}
//         <div className="flex flex-col space-y-1 p-4 rounded-lg bg-muted/50 items-center justify-center text-center">
//           <span className="text-sm text-muted-foreground">Total Mahasiswa</span>
//           <span className="font-semibold text-3xl">{subject.totalStudents}</span>
//         </div>

//       </CardContent>

//       <CardFooter>
//         <Button onClick={() => onShowStudents(subject)} className="w-full">
//           <List className="mr-2 h-4 w-4" /> Lihat Daftar Mahasiswa
//         </Button>
//       </CardFooter>
//     </Card>
//   );
// }