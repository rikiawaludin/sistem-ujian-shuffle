// resources/js/Pages/Ujian/PengerjaanComponents/OpsiMenjodohkan.jsx
import React, { useState, useEffect } from 'react';
import { DragDropContext, Droppable, Draggable } from 'react-beautiful-dnd';
import { Card, Typography } from '@material-tailwind/react';
import { Bars2Icon } from '@heroicons/react/24/solid';

export default function OpsiMenjodohkan({ soalId, opsiList, pasanganList, jawabanUser, onPilihJawaban }) {
    const [kolomKiri] = useState(opsiList);
    const [kolomKanan, setKolomKanan] = useState(pasanganList);

    // useEffect ini sekarang bertugas untuk memulihkan urutan 'kolomKanan' dari 'jawabanUser' (string)
    useEffect(() => {
        if (jawabanUser && typeof jawabanUser === 'string') {
            const answerMap = new Map(jawabanUser.split(',').map(pair => {
                const [leftId, rightId] = pair.split(':');
                return [parseInt(leftId, 10), parseInt(rightId, 10)];
            }));

            const pasanganMap = new Map(pasanganList.map(p => [p.id, p]));
            const restoredKolomKanan = kolomKiri.map(itemKiri => {
                const rightId = answerMap.get(itemKiri.id);
                return pasanganMap.get(rightId);
            }).filter(Boolean); // filter(Boolean) untuk menghapus item undefined jika ada error

            if (restoredKolomKanan.length === pasanganList.length) {
                setKolomKanan(restoredKolomKanan);
            } else {
                setKolomKanan(pasanganList); // Fallback ke urutan acak awal jika restorasi gagal
            }
        } else {
            setKolomKanan(pasanganList); // Jika tidak ada jawaban, gunakan urutan default
        }
    }, [jawabanUser, kolomKiri, pasanganList]);

    const handleOnDragEnd = (result) => {
        if (!result.destination) return;

        const items = Array.from(kolomKanan);
        const [reorderedItem] = items.splice(result.source.index, 1);
        items.splice(result.destination.index, 0, reorderedItem);

        setKolomKanan(items); // Update UI secara lokal

        // PERUBAHAN UTAMA: Buat string jawaban dengan format baru
        const jawabanPairs = kolomKiri.map((itemKiri, index) => {
            const itemKananYangSesuai = items[index];
            return `${itemKiri.id}:${itemKananYangSesuai.id}`;
        });

        // Urutkan berdasarkan ID kiri untuk konsistensi
        jawabanPairs.sort((a, b) => parseInt(a.split(':')[0], 10) - parseInt(b.split(':')[0], 10));
        
        const jawabanString = jawabanPairs.join(',');

        onPilihJawaban(soalId, jawabanString);
    };

    return (
        <div>
            <Typography color="blue-gray" className="font-semibold text-sm mb-4">Jodohkan item di sebelah kiri dengan pasangan yang sesuai di sebelah kanan.</Typography>
            <div className="grid grid-cols-2 gap-4">
                {/* Kolom Kiri - Statis */}
                <div className="space-y-3">
                    {kolomKiri.map((item, index) => (
                        <Card key={`kiri-${item.id}`} className="p-3 bg-blue-gray-50 border flex items-center">
                            <div dangerouslySetInnerHTML={{ __html: item.teks }} />
                        </Card>
                    ))}
                </div>

                {/* Kolom Kanan - Draggable */}
                <DragDropContext onDragEnd={handleOnDragEnd}>
                    <Droppable droppableId={`droppable-${soalId}`}>
                        {(provided) => (
                            <div {...provided.droppableProps} ref={provided.innerRef} className="space-y-3">
                                {kolomKanan.map((item, index) => (
                                    <Draggable key={`kanan-${item.id}`} draggableId={String(item.id)} index={index}>
                                        {(provided) => (
                                            <div ref={provided.innerRef} {...provided.draggableProps} {...provided.dragHandleProps}>
                                                <Card className="p-3 border flex items-center cursor-grab active:cursor-grabbing">
                                                    <div dangerouslySetInnerHTML={{ __html: item.teks }} />
                                                </Card>
                                            </div>
                                        )}
                                    </Draggable>
                                ))}
                                {provided.placeholder}
                            </div>
                        )}
                    </Droppable>
                </DragDropContext>
            </div>
        </div>
    );
}