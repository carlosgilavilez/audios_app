import { useState } from "react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Plus, Search, Edit, Trash2 } from "lucide-react";
import { Link } from "react-router-dom";

const Authors = () => {
  const [searchTerm, setSearchTerm] = useState("");

  // Mock data
  const authors = [
    { id: 1, name: "Juan Pérez", comment: "Especialista en meditación y mindfulness" },
    { id: 2, name: "María García", comment: "Terapeuta de sonido y relajación" },
    { id: 3, name: "Carlos Ruiz", comment: "Instructor de yoga y bienestar" },
    { id: 4, name: "Ana López", comment: "Psicóloga especializada en ansiedad" },
    { id: 5, name: "Roberto Silva", comment: "Coach de desarrollo personal" }
  ];

  const filteredAuthors = authors.filter(author =>
    author.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
    author.comment.toLowerCase().includes(searchTerm.toLowerCase())
  );

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-3xl font-semibold text-foreground">Gestión de Autores</h1>
        <Button asChild variant="success">
          <Link to="/authors/new" className="flex items-center gap-2">
            <Plus className="h-4 w-4" />
            Nuevo Autor
          </Link>
        </Button>
      </div>

      <Card className="shadow-sm border-border/50">
        <CardHeader>
          <CardTitle className="text-lg font-medium">Lista de Autores</CardTitle>
          <div className="flex items-center space-x-2 pt-2">
            <div className="relative flex-1 max-w-sm">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
              <Input
                placeholder="Buscar autores..."
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
              {filteredAuthors.map((author) => (
                <TableRow key={author.id}>
                  <TableCell className="font-medium">{author.id}</TableCell>
                  <TableCell className="font-medium">{author.name}</TableCell>
                  <TableCell className="text-muted-foreground">{author.comment}</TableCell>
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

export default Authors;