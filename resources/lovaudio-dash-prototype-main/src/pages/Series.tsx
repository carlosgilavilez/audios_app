import { useState } from "react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Plus, Search, Edit, Trash2 } from "lucide-react";
import { Link } from "react-router-dom";

const Series = () => {
  const [searchTerm, setSearchTerm] = useState("");

  // Mock data
  const series = [
    { id: 1, name: "Meditación para Principiantes", comment: "Serie introductoria de 10 sesiones de meditación" },
    { id: 2, name: "Mindfulness Diario", comment: "Prácticas de atención plena para cada día" },
    { id: 3, name: "Relajación Profunda", comment: "Técnicas avanzadas de relajación y descanso" },
    { id: 4, name: "Gestión del Estrés", comment: "Herramientas para manejar la ansiedad y el estrés" },
    { id: 5, name: "Sueño Reparador", comment: "Audios para mejorar la calidad del descanso nocturno" }
  ];

  const filteredSeries = series.filter(serie =>
    serie.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
    serie.comment.toLowerCase().includes(searchTerm.toLowerCase())
  );

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-3xl font-semibold text-foreground">Gestión de Series</h1>
        <Button asChild variant="success">
          <Link to="/series/new" className="flex items-center gap-2">
            <Plus className="h-4 w-4" />
            Nueva Serie
          </Link>
        </Button>
      </div>

      <Card className="shadow-sm border-border/50">
        <CardHeader>
          <CardTitle className="text-lg font-medium">Lista de Series</CardTitle>
          <div className="flex items-center space-x-2 pt-2">
            <div className="relative flex-1 max-w-sm">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
              <Input
                placeholder="Buscar series..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="pl-9"
              />
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>ID</TableHead>
                <TableHead>Nombre</TableHead>
                <TableHead>Comentario</TableHead>
                <TableHead className="text-right">Acciones</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {filteredSeries.map((serie) => (
                <TableRow key={serie.id}>
                  <TableCell className="font-medium">{serie.id}</TableCell>
                  <TableCell className="font-medium">{serie.name}</TableCell>
                  <TableCell className="text-muted-foreground">{serie.comment}</TableCell>
                  <TableCell className="text-right">
                    <div className="flex items-center justify-end space-x-2">
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

export default Series;