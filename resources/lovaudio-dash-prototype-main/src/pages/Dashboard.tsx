import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Users, Headphones, Music, Plus } from "lucide-react";
import { Link } from "react-router-dom";

const Dashboard = () => {
  const stats = [
    {
      title: "Autores",
      value: "24",
      icon: Users,
      description: "Autores registrados"
    },
    {
      title: "Series",
      value: "12",
      icon: Music,
      description: "Series creadas"
    },
    {
      title: "Audios",
      value: "156",
      icon: Headphones,
      description: "Audios subidos"
    }
  ];

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-3xl font-semibold text-foreground">Panel de Administración</h1>
      </div>

      {/* Statistics Cards */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        {stats.map((stat) => (
          <Card key={stat.title} className="shadow-sm border-border/50">
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium text-muted-foreground">
                {stat.title}
              </CardTitle>
              <stat.icon className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-foreground">{stat.value}</div>
              <p className="text-xs text-muted-foreground mt-1">{stat.description}</p>
            </CardContent>
          </Card>
        ))}
      </div>

      {/* Quick Actions */}
      <Card className="shadow-sm border-border/50">
        <CardHeader>
          <CardTitle className="text-lg font-medium">Acciones Rápidas</CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <Button asChild variant="success" className="h-12">
              <Link to="/authors/new" className="flex items-center gap-2">
                <Plus className="h-4 w-4" />
                Nuevo Autor
              </Link>
            </Button>
            <Button asChild variant="success" className="h-12">
              <Link to="/series/new" className="flex items-center gap-2">
                <Plus className="h-4 w-4" />
                Nueva Serie
              </Link>
            </Button>
            <Button asChild variant="success" className="h-12">
              <Link to="/audios/new" className="flex items-center gap-2">
                <Plus className="h-4 w-4" />
                Subir Audio
              </Link>
            </Button>
          </div>
        </CardContent>
      </Card>

      {/* Recent Activity */}
      <Card className="shadow-sm border-border/50">
        <CardHeader>
          <CardTitle className="text-lg font-medium">Actividad Reciente</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="space-y-3">
            <div className="flex items-center justify-between py-2 border-b border-border/30">
              <div>
                <p className="text-sm font-medium">Audio "Meditación Matinal" subido</p>
                <p className="text-xs text-muted-foreground">Por Juan Pérez • Hace 2 horas</p>
              </div>
            </div>
            <div className="flex items-center justify-between py-2 border-b border-border/30">
              <div>
                <p className="text-sm font-medium">Nueva serie "Mindfulness" creada</p>
                <p className="text-xs text-muted-foreground">Por María García • Hace 4 horas</p>
              </div>
            </div>
            <div className="flex items-center justify-between py-2">
              <div>
                <p className="text-sm font-medium">Autor "Carlos Ruiz" agregado</p>
                <p className="text-xs text-muted-foreground">Por Admin • Hace 1 día</p>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
};

export default Dashboard;