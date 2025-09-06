import { useState } from "react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Badge } from "@/components/ui/badge";
import { Plus, Search, Edit, Trash2, Filter, Play } from "lucide-react";
import { Link } from "react-router-dom";

const Audios = () => {
  const [searchTerm, setSearchTerm] = useState("");
  const [statusFilter, setStatusFilter] = useState("all");
  const [authorFilter, setAuthorFilter] = useState("all");
  const [seriesFilter, setSeriesFilter] = useState("all");
  const [categoryFilter, setCategoryFilter] = useState("all");

  // Mock data
  const audios = [
    { 
      id: 1, 
      name: "Respiración Consciente", 
      author: "Juan Pérez", 
      series: "Meditación para Principiantes", 
      category: "Relajación",
      status: "Normal", 
      date: "2024-01-15" 
    },
    { 
      id: 2, 
      name: "Relajación Nocturna", 
      author: "María García", 
      series: "Sueño Reparador", 
      category: "Sueño",
      status: "Pendiente", 
      date: "2024-01-14" 
    },
    { 
      id: 3, 
      name: "Mindfulness Matinal", 
      author: "Juan Pérez", 
      series: "Mindfulness Diario", 
      category: "Mindfulness",
      status: "Normal", 
      date: "2024-01-13" 
    },
    { 
      id: 4, 
      name: "Gestión de Ansiedad", 
      author: "Ana López", 
      series: "Gestión del Estrés", 
      category: "Bienestar",
      status: "Normal", 
      date: "2024-01-12" 
    },
    { 
      id: 5, 
      name: "Meditación Profunda", 
      author: "Carlos Ruiz", 
      series: "Relajación Profunda", 
      category: "Relajación",
      status: "Pendiente", 
      date: "2024-01-11" 
    }
  ];

  const filteredAudios = audios.filter(audio => {
    const matchesSearch = audio.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         audio.author.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         audio.series.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         audio.category.toLowerCase().includes(searchTerm.toLowerCase());
    const matchesStatus = statusFilter === "all" || audio.status === statusFilter;
    const matchesAuthor = authorFilter === "all" || audio.author === authorFilter;
    const matchesSeries = seriesFilter === "all" || audio.series === seriesFilter;
    const matchesCategory = categoryFilter === "all" || audio.category === categoryFilter;
    
    return matchesSearch && matchesStatus && matchesAuthor && matchesSeries && matchesCategory;
  });

  const getStatusBadge = (status: string) => {
    if (status === "Normal") {
      return <Badge variant="default" className="bg-success/10 text-success border-success/20">Normal</Badge>;
    } else if (status === "Pendiente") {
      return <Badge variant="secondary" className="bg-warning/10 text-warning border-warning/20">Pendiente</Badge>;
    }
    return <Badge variant="outline">{status}</Badge>;
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-3xl font-semibold text-foreground">Gestión de Audios</h1>
        <Button asChild variant="success">
          <Link to="/audios/new" className="flex items-center gap-2">
            <Plus className="h-4 w-4" />
            Subir Audio
          </Link>
        </Button>
      </div>

      <Card className="shadow-sm border-border/50">
        <CardHeader>
          <CardTitle className="text-lg font-medium flex items-center gap-2">
            <Filter className="h-5 w-5" />
            Filtros y Búsqueda
          </CardTitle>
          <div className="grid grid-cols-1 md:grid-cols-5 gap-4 pt-2">
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
                <SelectValue placeholder="Autor" />
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
                <SelectValue placeholder="Serie" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">Todas las series</SelectItem>
                <SelectItem value="Meditación para Principiantes">Meditación para Principiantes</SelectItem>
                <SelectItem value="Sueño Reparador">Sueño Reparador</SelectItem>
                <SelectItem value="Mindfulness Diario">Mindfulness Diario</SelectItem>
                <SelectItem value="Gestión del Estrés">Gestión del Estrés</SelectItem>
              </SelectContent>
            </Select>
            <Select value={categoryFilter} onValueChange={setCategoryFilter}>
              <SelectTrigger>
                <SelectValue placeholder="Categoría" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">Todas las categorías</SelectItem>
                <SelectItem value="Relajación">Relajación</SelectItem>
                <SelectItem value="Sueño">Sueño</SelectItem>
                <SelectItem value="Mindfulness">Mindfulness</SelectItem>
                <SelectItem value="Bienestar">Bienestar</SelectItem>
              </SelectContent>
            </Select>
            <Select value={statusFilter} onValueChange={setStatusFilter}>
              <SelectTrigger>
                <SelectValue placeholder="Estado" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">Todos los estados</SelectItem>
                <SelectItem value="Normal">Normal</SelectItem>
                <SelectItem value="Pendiente">Pendiente</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>ID</TableHead>
                <TableHead>Nombre</TableHead>
                <TableHead>Autor</TableHead>
                <TableHead>Serie</TableHead>
                <TableHead>Categoría</TableHead>
                <TableHead>Estado</TableHead>
                <TableHead>Fecha</TableHead>
                <TableHead className="text-right">Acciones</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {filteredAudios.map((audio) => (
                <TableRow key={audio.id}>
                  <TableCell className="font-medium">{audio.id}</TableCell>
                  <TableCell className="font-medium">{audio.name}</TableCell>
                  <TableCell>{audio.author}</TableCell>
                  <TableCell className="text-muted-foreground">{audio.series}</TableCell>
                  <TableCell>
                    <Badge variant="outline" className="bg-primary/10 text-primary border-primary/20">
                      {audio.category}
                    </Badge>
                  </TableCell>
                  <TableCell>{getStatusBadge(audio.status)}</TableCell>
                  <TableCell className="text-muted-foreground">{audio.date}</TableCell>
                  <TableCell className="text-right">
                    <div className="flex items-center justify-end space-x-2">
                      <Button 
                        variant="outline" 
                        size="sm" 
                        className="h-8 w-8 p-0 text-success hover:text-success"
                        onClick={() => console.log(`Playing audio: ${audio.name}`)}
                      >
                        <Play className="h-4 w-4" />
                      </Button>
                      <Button variant="outline" size="sm" className="h-8 w-8 p-0">
                        <Edit className="h-4 w-4" />
                      </Button>
                      <Button variant="outline" size="sm" className="h-8 w-8 p-0 text-destructive hover:text-destructive">
                        <Trash2 className="h-4 w-4" />
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  );
};

export default Audios;