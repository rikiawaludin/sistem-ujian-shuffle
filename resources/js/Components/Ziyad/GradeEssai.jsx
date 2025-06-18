
import { useState } from "react";
import { useParams, useNavigate } from "react-router-dom";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Textarea } from "@/components/ui/textarea";
import { Badge } from "@/components/ui/badge";
import { Separator } from "@/components/ui/separator";
import { ArrowLeft, Save, SkipForward, User, Clock, CheckCircle } from "lucide-react";
import { toast } from "@/hooks/use-toast";

// Mock data untuk penilaian essay
const essayData = {
  1: [
    {
      id: 1,
      studentName: "Ahmad Rizki",
      studentId: "2021001",
      question: "Jelaskan perbedaan antara Stack dan Queue beserta implementasinya dalam pemrograman. Berikan contoh penggunaan masing-masing struktur data tersebut!",
      answer: "Stack adalah struktur data LIFO (Last In First Out) dimana elemen yang terakhir masuk akan keluar pertama. Sedangkan Queue adalah struktur data FIFO (First In First Out) dimana elemen yang pertama masuk akan keluar pertama.\n\nImplementasi Stack:\n- Push: menambah elemen ke atas stack\n- Pop: mengeluarkan elemen dari atas stack\n- Top/Peek: melihat elemen teratas tanpa mengeluarkannya\n\nImplementasi Queue:\n- Enqueue: menambah elemen ke belakang queue\n- Dequeue: mengeluarkan elemen dari depan queue\n- Front: melihat elemen terdepan\n\nContoh penggunaan Stack: undo/redo operations, function call management, expression evaluation.\nContoh penggunaan Queue: task scheduling, breadth-first search, printer queue.",
      maxScore: 100,
      currentScore: null,
      feedback: "",
      timeSpent: "45 menit",
      submittedAt: "2024-01-15 14:30"
    },
    {
      id: 2,
      studentName: "Siti Nurhaliza",
      studentId: "2021002",
      question: "Jelaskan perbedaan antara Stack dan Queue beserta implementasinya dalam pemrograman. Berikan contoh penggunaan masing-masing struktur data tersebut!",
      answer: "Stack itu seperti tumpukan piring, yang terakhir ditaruh akan diambil pertama. Queue seperti antrian di bank, yang pertama datang dilayani pertama.\n\nStack pakai push untuk masukin data dan pop untuk keluarin data. Queue pakai enqueue untuk masukin dan dequeue untuk keluarin.\n\nStack bisa dipake untuk undo di aplikasi, atau untuk ngecek bracket matching. Queue bisa dipake untuk antrian print atau BFS algorithm.",
      maxScore: 100,
      currentScore: null,
      feedback: "",
      timeSpent: "25 menit",
      submittedAt: "2024-01-15 13:45"
    }
  ]
};

const GradeEssay = () => {
  const { courseId, examId } = useParams();
  const navigate = useNavigate();
  const [currentIndex, setCurrentIndex] = useState(0);
  const [scores, setScores] = useState<{[key: number]: number}>({});
  const [feedbacks, setFeedbacks] = useState<{[key: number]: string}>({});
  
  const essays = essayData[parseInt(examId || "1") as keyof typeof essayData] || [];
  const currentEssay = essays[currentIndex];

  if (!currentEssay) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-center">
          <h1 className="text-2xl font-bold mb-4">Tidak ada essay yang perlu dinilai</h1>
          <Button onClick={() => navigate(`/course/${courseId}`)}>Kembali ke Mata Kuliah</Button>
        </div>
      </div>
    );
  }

  const handleScoreChange = (score: number) => {
    setScores({...scores, [currentEssay.id]: score});
  };

  const handleFeedbackChange = (feedback: string) => {
    setFeedbacks({...feedbacks, [currentEssay.id]: feedback});
  };

  const handleSaveAndNext = () => {
    const score = scores[currentEssay.id];
    const feedback = feedbacks[currentEssay.id] || "";

    if (score === undefined || score < 0 || score > currentEssay.maxScore) {
      toast({
        title: "Error",
        description: `Nilai harus antara 0 - ${currentEssay.maxScore}`,
        variant: "destructive",
      });
      return;
    }

    // Simulate saving
    toast({
      title: "Berhasil",
      description: `Penilaian untuk ${currentEssay.studentName} telah disimpan`,
    });

    // Move to next essay
    if (currentIndex < essays.length - 1) {
      setCurrentIndex(currentIndex + 1);
    } else {
      toast({
        title: "Selesai",
        description: "Semua essay telah dinilai",
      });
      navigate(`/course/${courseId}`);
    }
  };

  const handleSkip = () => {
    if (currentIndex < essays.length - 1) {
      setCurrentIndex(currentIndex + 1);
    } else {
      navigate(`/course/${courseId}`);
    }
  };

  const getScoreColor = (score: number, maxScore: number) => {
    const percentage = (score / maxScore) * 100;
    if (percentage >= 80) return "text-green-600";
    if (percentage >= 60) return "text-yellow-600";
    return "text-red-600";
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
      {/* Header */}
      <div className="bg-gradient-to-r from-blue-600 to-indigo-700 text-white p-6 shadow-lg">
        <div className="max-w-7xl mx-auto">
          <div className="flex items-center justify-between">
            <div className="flex items-center space-x-4">
              <Button 
                variant="ghost" 
                size="sm" 
                onClick={() => navigate(`/course/${courseId}`)}
                className="text-white hover:bg-white/20"
              >
                <ArrowLeft className="h-4 w-4 mr-2" />
                Kembali
              </Button>
              <div>
                <h1 className="text-2xl font-bold">Penilaian Essay</h1>
                <p className="text-blue-100">
                  {currentIndex + 1} dari {essays.length} jawaban
                </p>
              </div>
            </div>
            <div className="text-right">
              <div className="text-blue-100">Progress Penilaian</div>
              <div className="text-2xl font-bold">
                {Math.round(((currentIndex) / essays.length) * 100)}%
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Main Content */}
      <div className="max-w-6xl mx-auto p-6">
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Left Column - Student Info & Question */}
          <div className="lg:col-span-1 space-y-6">
            {/* Student Info */}
            <Card className="bg-white shadow-md">
              <CardHeader>
                <CardTitle className="flex items-center text-lg">
                  <User className="h-5 w-5 mr-2 text-blue-600" />
                  Informasi Mahasiswa
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-3">
                <div>
                  <div className="text-sm text-gray-600">Nama</div>
                  <div className="font-medium">{currentEssay.studentName}</div>
                </div>
                <div>
                  <div className="text-sm text-gray-600">NIM</div>
                  <div className="font-medium">{currentEssay.studentId}</div>
                </div>
                <div>
                  <div className="text-sm text-gray-600">Waktu Pengerjaan</div>
                  <div className="font-medium flex items-center">
                    <Clock className="h-4 w-4 mr-1" />
                    {currentEssay.timeSpent}
                  </div>
                </div>
                <div>
                  <div className="text-sm text-gray-600">Waktu Submit</div>
                  <div className="font-medium">{currentEssay.submittedAt}</div>
                </div>
              </CardContent>
            </Card>

            {/* Question */}
            <Card className="bg-white shadow-md">
              <CardHeader>
                <CardTitle className="text-lg">Soal Essay</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="bg-blue-50 p-4 rounded-lg">
                  <p className="text-gray-800 leading-relaxed">
                    {currentEssay.question}
                  </p>
                </div>
                <div className="mt-3 flex items-center justify-between text-sm text-gray-600">
                  <span>Nilai Maksimal</span>
                  <Badge variant="outline">{currentEssay.maxScore} poin</Badge>
                </div>
              </CardContent>
            </Card>

            {/* Quick Scoring */}
            <Card className="bg-white shadow-md">
              <CardHeader>
                <CardTitle className="text-lg">Penilaian Cepat</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="grid grid-cols-2 gap-2">
                  {[90, 80, 70, 60].map((score) => (
                    <Button
                      key={score}
                      variant="outline"
                      size="sm"
                      onClick={() => handleScoreChange(score)}
                      className={scores[currentEssay.id] === score ? "bg-blue-100" : ""}
                    >
                      {score}
                    </Button>
                  ))}
                </div>
              </CardContent>
            </Card>
          </div>

          {/* Right Column - Answer & Grading */}
          <div className="lg:col-span-2 space-y-6">
            {/* Student Answer */}
            <Card className="bg-white shadow-md">
              <CardHeader>
                <CardTitle className="text-lg">Jawaban Mahasiswa</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="bg-gray-50 p-4 rounded-lg min-h-[300px]">
                  <pre className="whitespace-pre-wrap text-gray-800 font-sans leading-relaxed">
                    {currentEssay.answer}
                  </pre>
                </div>
              </CardContent>
            </Card>

            {/* Grading Section */}
            <Card className="bg-white shadow-md">
              <CardHeader>
                <CardTitle className="text-lg">Penilaian</CardTitle>
              </CardHeader>
              <CardContent className="space-y-6">
                {/* Score Input */}
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Nilai (0 - {currentEssay.maxScore})
                  </label>
                  <div className="flex items-center space-x-4">
                    <input
                      type="number"
                      min="0"
                      max={currentEssay.maxScore}
                      value={scores[currentEssay.id] || ""}
                      onChange={(e) => handleScoreChange(parseInt(e.target.value) || 0)}
                      className="flex h-10 w-32 rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                      placeholder="0"
                    />
                    {scores[currentEssay.id] !== undefined && (
                      <div className={`text-2xl font-bold ${getScoreColor(scores[currentEssay.id], currentEssay.maxScore)}`}>
                        {Math.round((scores[currentEssay.id] / currentEssay.maxScore) * 100)}%
                      </div>
                    )}
                  </div>
                </div>

                <Separator />

                {/* Feedback */}
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Feedback untuk Mahasiswa (Opsional)
                  </label>
                  <Textarea
                    value={feedbacks[currentEssay.id] || ""}
                    onChange={(e) => handleFeedbackChange(e.target.value)}
                    placeholder="Berikan feedback konstruktif untuk membantu mahasiswa memahami kekurangan dalam jawabannya..."
                    className="min-h-[120px]"
                  />
                </div>

                {/* Action Buttons */}
                <div className="flex justify-between pt-4">
                  <Button
                    variant="outline"
                    onClick={handleSkip}
                  >
                    <SkipForward className="h-4 w-4 mr-2" />
                    Lewati
                  </Button>
                  
                  <Button
                    onClick={handleSaveAndNext}
                    className="bg-green-600 hover:bg-green-700"
                  >
                    <Save className="h-4 w-4 mr-2" />
                    {currentIndex < essays.length - 1 ? "Simpan & Lanjut" : "Simpan & Selesai"}
                  </Button>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>

        {/* Progress Bar */}
        <Card className="bg-white shadow-md mt-6">
          <CardContent className="p-4">
            <div className="flex items-center justify-between mb-2">
              <span className="text-sm font-medium">Progress Penilaian</span>
              <span className="text-sm text-gray-600">
                {currentIndex + 1} / {essays.length}
              </span>
            </div>
            <div className="w-full bg-gray-200 rounded-full h-2">
              <div 
                className="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                style={{ width: `${((currentIndex + 1) / essays.length) * 100}%` }}
              ></div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
};

export default GradeEssay;
