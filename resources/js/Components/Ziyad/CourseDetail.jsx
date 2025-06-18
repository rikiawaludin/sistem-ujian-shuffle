
import { useState } from "react";
import { useParams, useNavigate } from "react-router-dom";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { 
  ArrowLeft, 
  BookOpen, 
  FileText, 
  BarChart3, 
  Users, 
  Plus, 
  Settings,
  Calendar,
  Clock,
  CheckCircle,
  AlertCircle,
  Edit,
  Trash2,
  Eye
} from "lucide-react";
import { toast } from "@/hooks/use-toast";

// Mock data untuk detail mata kuliah
const courseData = {
  1: {
    id: 1,
    name: "Algoritma dan Struktur Data",
    code: "CS101",
    semester: "Ganjil 2024/2025",
    students: 45,
    schedule: "Senin, Rabu - 08:00-09:40",
    description: "Mata kuliah ini membahas konsep dasar algoritma dan struktur data yang efisien.",
    questionBank: [
      { id: 1, type: "Multiple Choice", question: "Apa itu Big O Notation?", difficulty: "Medium", lastModified: "2024-01-15" },
      { id: 2, type: "Essay", question: "Jelaskan perbedaan Stack dan Queue", difficulty: "Hard", lastModified: "2024-01-14" },
      { id: 3, type: "Multiple Choice", question: "Kompleksitas Binary Search adalah?", difficulty: "Easy", lastModified: "2024-01-13" }
    ],
    exams: [
      { id: 1, title: "Quiz 1 - Introduction", type: "Quiz", status: "active", startDate: "2024-01-20", endDate: "2024-01-21", participants: 45 },
      { id: 2, title: "UTS - Mid Semester", type: "UTS", status: "scheduled", startDate: "2024-02-15", endDate: "2024-02-15", participants: 0 },
      { id: 3, title: "Quiz 2 - Sorting", type: "Quiz", status: "completed", startDate: "2024-01-10", endDate: "2024-01-11", participants: 43 }
    ],
    results: [
      { id: 1, examTitle: "Quiz 1 - Introduction", completed: 45, pending: 0, avgScore: 82.5, needsGrading: 5 },
      { id: 3, examTitle: "Quiz 2 - Sorting", completed: 43, pending: 2, avgScore: 78.2, needsGrading: 8 }
    ]
  }
};

const CourseDetail = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const [activeTab, setActiveTab] = useState("overview");

  const course = courseData[parseInt(id || "1") as keyof typeof courseData];

  if (!course) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-center">
          <h1 className="text-2xl font-bold mb-4">Mata Kuliah Tidak Ditemukan</h1>
          <Button onClick={() => navigate("/")}>Kembali ke Dashboard</Button>
        </div>
      </div>
    );
  }

  const getDifficultyColor = (difficulty: string) => {
    switch (difficulty) {
      case 'Easy': return 'bg-green-100 text-green-800';
      case 'Medium': return 'bg-yellow-100 text-yellow-800';
      case 'Hard': return 'bg-red-100 text-red-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  const getExamStatusColor = (status: string) => {
    switch (status) {
      case 'active': return 'bg-green-100 text-green-800';
      case 'scheduled': return 'bg-blue-100 text-blue-800';
      case 'completed': return 'bg-gray-100 text-gray-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  const handleGradeEssay = (examId: number) => {
    navigate(`/course/${id}/grade/${examId}`);
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
                onClick={() => navigate("/")}
                className="text-white hover:bg-white/20"
              >
                <ArrowLeft className="h-4 w-4 mr-2" />
                Kembali
              </Button>
              <div>
                <h1 className="text-2xl font-bold">{course.name}</h1>
                <p className="text-blue-100">{course.code} • {course.semester}</p>
              </div>
            </div>
            <div className="text-right">
              <p className="text-blue-100">{course.schedule}</p>
              <p className="text-sm text-blue-200">{course.students} Mahasiswa</p>
            </div>
          </div>
        </div>
      </div>

      {/* Main Content */}
      <div className="max-w-7xl mx-auto p-6">
        <Tabs value={activeTab} onValueChange={setActiveTab} className="space-y-6">
          <TabsList className="grid w-full grid-cols-4 bg-white shadow-md">
            <TabsTrigger value="overview" className="flex items-center space-x-2">
              <BarChart3 className="h-4 w-4" />
              <span>Overview</span>
            </TabsTrigger>
            <TabsTrigger value="questions" className="flex items-center space-x-2">
              <BookOpen className="h-4 w-4" />
              <span>Bank Soal</span>
            </TabsTrigger>
            <TabsTrigger value="exams" className="flex items-center space-x-2">
              <FileText className="h-4 w-4" />
              <span>Ujian</span>
            </TabsTrigger>
            <TabsTrigger value="results" className="flex items-center space-x-2">
              <Users className="h-4 w-4" />
              <span>Hasil & Penilaian</span>
            </TabsTrigger>
          </TabsList>

          <TabsContent value="overview" className="space-y-6">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
              <Card className="bg-white shadow-md">
                <CardHeader className="pb-3">
                  <CardTitle className="text-lg flex items-center">
                    <BookOpen className="h-5 w-5 mr-2 text-blue-600" />
                    Bank Soal
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="text-3xl font-bold text-blue-600 mb-2">
                    {course.questionBank.length}
                  </div>
                  <p className="text-sm text-gray-600">Total Soal Tersedia</p>
                  <div className="mt-4 space-y-2">
                    <div className="flex justify-between text-sm">
                      <span>Multiple Choice</span>
                      <span className="font-medium">
                        {course.questionBank.filter(q => q.type === "Multiple Choice").length}
                      </span>
                    </div>
                    <div className="flex justify-between text-sm">
                      <span>Essay</span>
                      <span className="font-medium">
                        {course.questionBank.filter(q => q.type === "Essay").length}
                      </span>
                    </div>
                  </div>
                </CardContent>
              </Card>

              <Card className="bg-white shadow-md">
                <CardHeader className="pb-3">
                  <CardTitle className="text-lg flex items-center">
                    <FileText className="h-5 w-5 mr-2 text-green-600" />
                    Ujian
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="text-3xl font-bold text-green-600 mb-2">
                    {course.exams.length}
                  </div>
                  <p className="text-sm text-gray-600">Total Ujian</p>
                  <div className="mt-4 space-y-2">
                    <div className="flex justify-between text-sm">
                      <span>Aktif</span>
                      <span className="font-medium text-green-600">
                        {course.exams.filter(e => e.status === "active").length}
                      </span>
                    </div>
                    <div className="flex justify-between text-sm">
                      <span>Terjadwal</span>
                      <span className="font-medium text-blue-600">
                        {course.exams.filter(e => e.status === "scheduled").length}
                      </span>
                    </div>
                    <div className="flex justify-between text-sm">
                      <span>Selesai</span>
                      <span className="font-medium text-gray-600">
                        {course.exams.filter(e => e.status === "completed").length}
                      </span>
                    </div>
                  </div>
                </CardContent>
              </Card>

              <Card className="bg-white shadow-md">
                <CardHeader className="pb-3">
                  <CardTitle className="text-lg flex items-center">
                    <Users className="h-5 w-5 mr-2 text-purple-600" />
                    Mahasiswa
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="text-3xl font-bold text-purple-600 mb-2">
                    {course.students}
                  </div>
                  <p className="text-sm text-gray-600">Terdaftar</p>
                  <div className="mt-4">
                    <div className="flex justify-between text-sm mb-2">
                      <span>Kehadiran Rata-rata</span>
                      <span className="font-medium">87%</span>
                    </div>
                    <div className="w-full bg-gray-200 rounded-full h-2">
                      <div className="bg-purple-600 h-2 rounded-full" style={{ width: '87%' }}></div>
                    </div>
                  </div>
                </CardContent>
              </Card>
            </div>

            <Card className="bg-white shadow-md">
              <CardHeader>
                <CardTitle>Deskripsi Mata Kuliah</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-gray-700">{course.description}</p>
              </CardContent>
            </Card>
          </TabsContent>

          <TabsContent value="questions" className="space-y-6">
            <div className="flex justify-between items-center">
              <h3 className="text-xl font-bold text-gray-800">Bank Soal</h3>
              <Button className="bg-blue-600 hover:bg-blue-700">
                <Plus className="h-4 w-4 mr-2" />
                Tambah Soal
              </Button>
            </div>

            <div className="space-y-4">
              {course.questionBank.map((question) => (
                <Card key={question.id} className="bg-white shadow-md hover:shadow-lg transition-shadow">
                  <CardContent className="p-6">
                    <div className="flex items-start justify-between">
                      <div className="flex-1">
                        <div className="flex items-center space-x-3 mb-2">
                          <Badge variant="outline" className="text-xs">
                            {question.type}
                          </Badge>
                          <Badge className={`text-xs ${getDifficultyColor(question.difficulty)}`}>
                            {question.difficulty}
                          </Badge>
                        </div>
                        <h4 className="font-medium text-gray-800 mb-2">
                          {question.question}
                        </h4>
                        <p className="text-sm text-gray-500">
                          Terakhir diubah: {question.lastModified}
                        </p>
                      </div>
                      <div className="flex space-x-2">
                        <Button variant="outline" size="sm">
                          <Eye className="h-4 w-4" />
                        </Button>
                        <Button variant="outline" size="sm">
                          <Edit className="h-4 w-4" />
                        </Button>
                        <Button variant="outline" size="sm" className="text-red-600 hover:text-red-700">
                          <Trash2 className="h-4 w-4" />
                        </Button>
                      </div>
                    </div>
                  </CardContent>
                </Card>
              ))}
            </div>
          </TabsContent>

          <TabsContent value="exams" className="space-y-6">
            <div className="flex justify-between items-center">
              <h3 className="text-xl font-bold text-gray-800">Pengaturan Ujian</h3>
              <Button className="bg-green-600 hover:bg-green-700">
                <Plus className="h-4 w-4 mr-2" />
                Buat Ujian Baru
              </Button>
            </div>

            <div className="space-y-4">
              {course.exams.map((exam) => (
                <Card key={exam.id} className="bg-white shadow-md hover:shadow-lg transition-shadow">
                  <CardContent className="p-6">
                    <div className="flex items-start justify-between">
                      <div className="flex-1">
                        <div className="flex items-center space-x-3 mb-2">
                          <h4 className="text-lg font-semibold text-gray-800">
                            {exam.title}
                          </h4>
                          <Badge className={`${getExamStatusColor(exam.status)}`}>
                            {exam.status === 'active' ? 'Aktif' : 
                             exam.status === 'scheduled' ? 'Terjadwal' : 'Selesai'}
                          </Badge>
                        </div>
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-600">
                          <div className="flex items-center">
                            <Calendar className="h-4 w-4 mr-2" />
                            Mulai: {exam.startDate}
                          </div>
                          <div className="flex items-center">
                            <Clock className="h-4 w-4 mr-2" />
                            Selesai: {exam.endDate}
                          </div>
                          <div className="flex items-center">
                            <Users className="h-4 w-4 mr-2" />
                            Peserta: {exam.participants}
                          </div>
                        </div>
                      </div>
                      <div className="flex space-x-2">
                        <Button variant="outline" size="sm">
                          <Settings className="h-4 w-4" />
                        </Button>
                        <Button variant="outline" size="sm">
                          <BarChart3 className="h-4 w-4" />
                        </Button>
                      </div>
                    </div>
                  </CardContent>
                </Card>
              ))}
            </div>
          </TabsContent>

          <TabsContent value="results" className="space-y-6">
            <div className="flex justify-between items-center">
              <h3 className="text-xl font-bold text-gray-800">Hasil Ujian & Penilaian</h3>
            </div>

            <div className="space-y-4">
              {course.results.map((result) => (
                <Card key={result.id} className="bg-white shadow-md hover:shadow-lg transition-shadow">
                  <CardContent className="p-6">
                    <div className="flex items-start justify-between mb-4">
                      <div>
                        <h4 className="text-lg font-semibold text-gray-800 mb-2">
                          {result.examTitle}
                        </h4>
                        <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                          <div className="bg-green-50 p-3 rounded-lg text-center">
                            <div className="text-xl font-bold text-green-600">{result.completed}</div>
                            <div className="text-gray-600">Selesai</div>
                          </div>
                          <div className="bg-yellow-50 p-3 rounded-lg text-center">
                            <div className="text-xl font-bold text-yellow-600">{result.pending}</div>
                            <div className="text-gray-600">Pending</div>
                          </div>
                          <div className="bg-blue-50 p-3 rounded-lg text-center">
                            <div className="text-xl font-bold text-blue-600">{result.avgScore}</div>
                            <div className="text-gray-600">Rata-rata</div>
                          </div>
                          <div className="bg-red-50 p-3 rounded-lg text-center">
                            <div className="text-xl font-bold text-red-600">{result.needsGrading}</div>
                            <div className="text-gray-600">Perlu Dinilai</div>
                          </div>
                        </div>
                      </div>
                    </div>
                    
                    <div className="flex space-x-3">
                      <Button variant="outline" size="sm">
                        <BarChart3 className="h-4 w-4 mr-2" />
                        Lihat Detail
                      </Button>
                      <Button variant="outline" size="sm">
                        <FileText className="h-4 w-4 mr-2" />
                        Export Nilai
                      </Button>
                      {result.needsGrading > 0 && (
                        <Button 
                          size="sm" 
                          className="bg-orange-600 hover:bg-orange-700"
                          onClick={() => handleGradeEssay(result.id)}
                        >
                          <Edit className="h-4 w-4 mr-2" />
                          Nilai Essay ({result.needsGrading})
                        </Button>
                      )}
                    </div>
                  </CardContent>
                </Card>
              ))}
            </div>
          </TabsContent>
        </Tabs>
      </div>
    </div>
  );
};

export default CourseDetail;
