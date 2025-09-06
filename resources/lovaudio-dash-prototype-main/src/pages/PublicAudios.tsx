import { useState } from "react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Badge } from "@/components/ui/badge";
import { Play, Search, Calendar, User, Music } from "lucide-react";

const PublicAudios = () => {
  const [searchTerm, setSearchTerm] = useState("");
  const [authorFilter, setAuthorFilter] = useState("all");
  const [seriesFilter, setSeriesFilter] = useState("all");
  const [categoryFilter, setCategoryFilter] = useState("all");
  const [dateFilter, setDateFilter] = useState("all");

  // Mock data
  const audios = [
    { 
      id: 1, 
      name: "Respiración Consciente", 
      author: "Juan Pérez", 
      series: "Meditación para Principiantes", 
      category: "Relajación",
      status: "Normal", 
      date: "15 Ene 2024",
      duration: "12:30",
      description: "Una práctica guiada de respiración consciente para calmar la mente."
    },
    { 
      id: 2, 
      name: "Relajación Nocturna", 
      author: "María García", 
      series: "Sueño Reparador", 
      category: "Sueño",
      status: "Normal", 
      date: "14 Ene 2024",
      duration: "25:45",
      description: "Audio especialmente diseñado para relajarte antes de dormir."
    },
    { 
      id: 3, 
      name: "Mindfulness Matinal", 
      author: "Juan Pérez", 
      series: "Mindfulness Diario", 
      category: "Mindfulness",
      status: "Normal", 
      date: "13 Ene 2024",
      duration: "15:20",
      description: "Comienza tu día con una práctica de atención plena."
    },
    { 
      id: 4, 
      name: "Gestión de Ansiedad", 
      author: "Ana López", 
      series: "Gestión del Estrés", 
      category: "Bienestar",
      status: "Normal", 
      date: "12 Ene 2024",
      duration: "18:15",
      description: "Técnicas efectivas para manejar momentos de ansiedad."
    },
    { 
      id: 5, 
      name: "Meditación Profunda", 
      author: "Carlos Ruiz", 
      series: "Relajación Profunda", 
      category: "Relajación",
      status: "Normal", 
      date: "11 Ene 2024",
      duration: "30:00",
      description: "Sesión extendida de meditación para una relajación profunda."
    },
    { 
      id: 6, 
      name: "Concentración y Foco", 
      author: "María García", 
      series: "Mindfulness Diario", 
      category: "Mindfulness",
      status: "Normal", 
      date: "10 Ene 2024",
      duration: "20:30",
      description: "Mejora tu capacidad de concentración con esta práctica."
    }
  ];

  const filteredAudios = audios.filter(audio => {
    // Solo mostrar audios con estado "Normal"
    if (audio.status !== "Normal") return false;
    
    const matchesSearch = audio.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         audio.author.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         audio.series.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         audio.category.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         audio.description.toLowerCase().includes(searchTerm.toLowerCase());
    const matchesAuthor = authorFilter === "all" || audio.author === authorFilter;
    const matchesSeries = seriesFilter === "all" || audio.series === seriesFilter;
    const matchesCategory = categoryFilter === "all" || audio.category === categoryFilter;
    const matchesDate = dateFilter === "all" || 
      (dateFilter === "recent" && new Date(audio.date).getTime() > Date.now() - 7 * 24 * 60 * 60 * 1000) ||
      (dateFilter === "month" && new Date(audio.date).getTime() > Date.now() - 30 * 24 * 60 * 60 * 1000);
    
    return matchesSearch && matchesAuthor && matchesSeries && matchesCategory && matchesDate;
  });

  return (
    <div className="space-y-6">
      <div className="text-center py-8">
        <h1 className="text-4xl font-bold text-foreground mb-2">Biblioteca de Audios</h1>
        <p className="text-lg text-muted-foreground">Descubre nuestra colección de audios de meditación y bienestar</p>
      </div>

      {/* Search and Filters */}
      <Card className="shadow-sm border-border/50">
        <CardContent className="pt-6">
          <div className="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div className="relative">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
              <Input
                placeholder="Buscar audios..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="pl-9"
              />
            </div>
            <Select value={authorFilter} onValueChange={setAuthorFilter}>
              <SelectTrigger>
                <SelectValue placeholder="Filtrar por autor" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">Todos los autores</SelectItem>
                <SelectItem value="Juan Pérez">Juan Pérez</SelectItem>
                <SelectItem value="María García">María García</SelectItem>
                <SelectItem value="Ana López">Ana López</SelectItem>
                <SelectItem value="Carlos Ruiz">Carlos Ruiz</SelectItem>
              </SelectContent>
            </Select>
            <Select value={seriesFilter} onValueChange={setSeriesFilter}>
              <SelectTrigger>
                <SelectValue placeholder="Filtrar por serie" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">Todas las series</SelectItem>
                <SelectItem value="Meditación para Principiantes">Meditación para Principiantes</SelectItem>
                <SelectItem value="Sueño Reparador">Sueño Reparador</SelectItem>
                <SelectItem value="Mindfulness Diario">Mindfulness Diario</SelectItem>
                <SelectItem value="Gestión del Estrés">Gestión del Estrés</SelectItem>
                <SelectItem value="Relajación Profunda">Relajación Profunda</SelectItem>
              </SelectContent>
            </Select>
            <Select value={categoryFilter} onValueChange={setCategoryFilter}>
              <SelectTrigger>
                <SelectValue placeholder="Filtrar por categoría" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">Todas las categorías</SelectItem>
                <SelectItem value="Relajación">Relajación</SelectItem>
                <SelectItem value="Sueño">Sueño</SelectItem>
                <SelectItem value="Mindfulness">Mindfulness</SelectItem>
                <SelectItem value="Bienestar">Bienestar</SelectItem>
              </SelectContent>
            </Select>
            <Select value={dateFilter} onValueChange={setDateFilter}>
              <SelectTrigger>
                <SelectValue placeholder="Filtrar por fecha" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">Todas las fechas</SelectItem>
                <SelectItem value="recent">Última semana</SelectItem>
                <SelectItem value="month">Último mes</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </CardContent>
      </Card>

      {/* Audio Cards Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {filteredAudios.map((audio) => (
          <Card key={audio.id} className="shadow-md border-border/50 hover:shadow-lg transition-shadow">
            <CardHeader className="pb-3">
              <div className="flex items-start justify-between">
                <div className="space-y-1 flex-1">
                  <CardTitle className="text-lg font-semibold leading-tight">{audio.name}</CardTitle>
                  <div className="flex items-center text-sm text-muted-foreground">
                    <User className="h-3 w-3 mr-1" />
                    {audio.author}
                  </div>
                </div>
                <Badge variant="outline" className="bg-success/10 text-success border-success/20 ml-2">
                  {audio.duration}
                </Badge>
              </div>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="space-y-2">
                <div className="flex items-center text-sm text-muted-foreground">
                  <Music className="h-3 w-3 mr-1" />
                  {audio.series}
                </div>
                <div className="flex items-center text-sm text-muted-foreground">
                  <Calendar className="h-3 w-3 mr-1" />
                  {audio.date}
                </div>
                <Badge variant="outline" className="bg-primary/10 text-primary border-primary/20 text-xs">
                  {audio.category}
                </Badge>
              </div>
              
              <p className="text-sm text-muted-foreground leading-relaxed">
                {audio.description}
              </p>
              
              <Button 
                variant="success" 
                className="w-full"
                onClick={() => console.log(`Playing audio: ${audio.name}`)}
              >
                <Play className="h-4 w-4 mr-2" />
                Reproducir
              </Button>
            </CardContent>
          </Card>
        ))}
      </div>

      {filteredAudios.length === 0 && (
        <div className="text-center py-12">
          <div className="text-muted-foreground">
            <Music className="h-16 w-16 mx-auto mb-4 opacity-50" />
            <p className="text-lg font-medium mb-2">No se encontraron audios</p>
            <p className="text-sm">Intenta ajustar tus filtros de búsqueda</p>
          </div>
        </div>
      )}
    </div>
  );
};

export default PublicAudios;