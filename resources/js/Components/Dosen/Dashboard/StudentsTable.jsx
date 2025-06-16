import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table"; // Pastikan path ini sesuai dengan struktur proyek Anda

export function StudentsTable({ students }) {
  return (
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>Email Mahasiswa</TableHead>
          <TableHead className="text-right">Nilai</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        {students.map((student) => (
          <TableRow key={student.id}>
            <TableCell className="font-medium">{student.name}</TableCell>
            <TableCell className="text-right">{student.score}</TableCell>
          </TableRow>
        ))}
      </TableBody>
    </Table>
  );
}