import * as React from "react"
import { format } from "date-fns"
import { Calendar as CalendarIcon } from "lucide-react"

import { cn } from "@/lib/utils"
import { Button } from "@/Components/ui/button"
import { Calendar } from "@/Components/ui/calendar"
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/Components/ui/popover"

export function DateTimePicker({ value, onChange }) {
  const initialDate = value ? new Date(value) : null;
  const [date, setDate] = React.useState(initialDate);
  const [isOpen, setIsOpen] = React.useState(false);

  // Fungsi ini akan menjadi pusat logika
  const handleSelect = (selectedDay) => {
    const newDate = selectedDay || date; // Gunakan tanggal yang baru dipilih, atau tanggal yang sudah ada
    if (!newDate) return;

    // Ambil waktu dari state 'date' yang sudah ada jika ada, jika tidak, default ke 00:00
    const currentHour = date ? date.getHours() : 0;
    const currentMinute = date ? date.getMinutes() : 0;

    const combinedDate = new Date(
      newDate.getFullYear(),
      newDate.getMonth(),
      newDate.getDate(),
      currentHour,
      currentMinute
    );
    
    setDate(combinedDate);
    onChange(combinedDate); // Kirim update ke parent
    setIsOpen(false); // <-- KUNCI UX: Tutup popover secara otomatis
  };

  const handleTimeChange = (e) => {
    const { name, value } = e.target;
    const newDate = date ? new Date(date) : new Date();

    if (name === 'hour') {
      newDate.setHours(parseInt(value, 10));
    } else if (name === 'minute') {
      newDate.setMinutes(parseInt(value, 10));
    }

    setDate(newDate);
    onChange(newDate);
  };
  
  // Update state jika prop 'value' dari parent berubah
  React.useEffect(() => {
    if (value) {
      setDate(new Date(value));
    } else {
      setDate(null);
    }
  }, [value]);

  return (
    <Popover open={isOpen} onOpenChange={setIsOpen}>
      <PopoverTrigger asChild>
        <Button
          variant={"outline"}
          className={cn(
            "w-full justify-start text-left font-normal",
            !date && "text-muted-foreground"
          )}
        >
          <CalendarIcon className="mr-2 h-4 w-4" />
          {date ? format(date, "d MMMM yyyy, HH:mm") : <span>Pilih tanggal & waktu</span>}
        </Button>
      </PopoverTrigger>
      <PopoverContent className="w-auto p-0">
        <Calendar
          mode="single"
          selected={date}
          onSelect={handleSelect} // Gunakan handleSelect untuk menutup otomatis
          initialFocus
        />
        <div className="p-3 border-t border-border">
          <div className="flex items-center justify-center space-x-2">
             <label htmlFor="hour" className="text-sm">Jam:</label>
             <select
                name="hour"
                value={date ? date.getHours().toString().padStart(2, '0') : '00'}
                onChange={handleTimeChange}
                className="px-2 py-1 border rounded"
              >
                {Array.from({ length: 24 }, (_, i) => (
                  <option key={i} value={i}>{i.toString().padStart(2, '0')}</option>
                ))}
              </select>
              <span>:</span>
              <select
                name="minute"
                value={date ? date.getMinutes().toString().padStart(2, '0') : '00'}
                onChange={handleTimeChange}
                className="px-2 py-1 border rounded"
              >
                {Array.from({ length: 60 }, (_, i) => (
                  <option key={i} value={i}>{i.toString().padStart(2, '0')}</option>
                ))}
              </select>
          </div>
        </div>
      </PopoverContent>
    </Popover>
  )
}