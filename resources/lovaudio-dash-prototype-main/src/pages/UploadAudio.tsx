import { useState } from "react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Calendar } from "@/components/ui/calendar";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { cn } from "@/lib/utils";
import { CalendarIcon, Upload, FileAudio, X, Check, AlertCircle } from "lucide-react";
import { format } from "date-fns";
import { Progress } from "@/components/ui/progress";
import { Link } from "react-router-dom";

const UploadAudio = () => {
  const [date, setDate] = useState<Date>();
  const [dragActive, setDragActive] = useState(false);
  const [uploadedFile, setUploadedFile] = useState<File | null>(null);
  const [uploadProgress, setUploadProgress] = useState(0);
  const [queuedFiles, setQueuedFiles] = useState<Array<{ file: File; progress: number; status: 'uploading' | 'completed' | 'error' }>>([]);

  const handleDrag = (e: React.DragEvent) => {
    e.preventDefault();
    e.stopPropagation();
    if (e.type === "dragenter" || e.type === "dragover") {
      setDragActive(true);
    } else if (e.type === "dragleave") {
      setDragActive(false);
    }
  };

  const handleDrop = (e: React.DragEvent) => {
    e.preventDefault();
    e.stopPropagation();
    setDragActive(false);
    
    if (e.dataTransfer.files && e.dataTransfer.files[0]) {
      const file = e.dataTransfer.files[0];
      if (file.type.startsWith('audio/')) {
        setUploadedFile(file);
        simulateUpload(file);
      }
    }
  };

  const handleFileInput = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files && e.target.files[0]) {
      const file = e.target.files[0];
      if (file.type.startsWith('audio/')) {
        setUploadedFile(file);
        simulateUpload(file);
      }
    }
  };

  const simulateUpload = (file: File) => {
    const newQueuedFile = { file, progress: 0, status: 'uploading' as const };
    setQueuedFiles(prev => [...prev, newQueuedFile]);
    
    // Simulate upload progress
    const interval = setInterval(() => {
      setQueuedFiles(prev => prev.map(qf => 
        qf.file === file 
          ? { ...qf, progress: Math.min(qf.progress + 10, 100) }
          : qf
      ));
    }, 200);

    // Complete upload after 2 seconds
    setTimeout(() => {
      clearInterval(interval);
      setQueuedFiles(prev => prev.map(qf => 
        qf.file === file 
          ? { ...qf, progress: 100, status: 'completed' as const }
          : qf
      ));
    }, 2000);
  };

  const removeFile = () => {
    setUploadedFile(null);
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-3xl font-semibold text-foreground">Subir Nuevo Audio</h1>
        <Button variant="outline" asChild>
          <Link to="/audios">Cancelar</Link>
        </Button>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Audio Upload Section */}
        <Card className="shadow-sm border-border/50">
          <CardHeader>
            <CardTitle className="text-lg font-medium">Archivo de Audio</CardTitle>
          </CardHeader>
          <CardContent>
            {!uploadedFile ? (
              <div
                className={cn(
                  "border-2 border-dashed rounded-lg p-8 text-center transition-colors",
                  dragActive
                    ? "border-success bg-success/5"
                    : "border-border hover:border-success/50"
                )}
                onDragEnter={handleDrag}
                onDragLeave={handleDrag}
                onDragOver={handleDrag}
                onDrop={handleDrop}
              >
                <Upload className="mx-auto h-12 w-12 text-muted-foreground mb-4" />
                <p className="text-lg font-medium mb-2">Arrastra tu archivo aquí</p>
                <p className="text-muted-foreground mb-4">
                  o haz clic para seleccionar
                </p>
                <input
                  type="file"
                  accept="audio/*"
                  onChange={handleFileInput}
                  className="hidden"
                  id="audio-upload"
                />
                <Button
                  variant="outline"
                  onClick={() => document.getElementById('audio-upload')?.click()}
                >
                  Seleccionar Archivo
                </Button>
                <p className="text-xs text-muted-foreground mt-2">
                  Formatos soportados: MP3, WAV, AAC (máx. 50MB)
                </p>
              </div>
            ) : (
              <div className="border border-border rounded-lg p-4">
                <div className="flex items-center justify-between">
                  <div className="flex items-center space-x-3">
                    <FileAudio className="h-10 w-10 text-success" />
                    <div>
                      <p className="font-medium">{uploadedFile.name}</p>
                      <p className="text-sm text-muted-foreground">
                        {(uploadedFile.size / 1024 / 1024).toFixed(2)} MB
                      </p>
                    </div>
                  </div>
                  <Button
                    variant="ghost"
                    size="sm"
                    onClick={removeFile}
                    className="h-8 w-8 p-0 text-destructive hover:text-destructive"
                  >
                    <X className="h-4 w-4" />
                  </Button>
                </div>
              </div>
            )}
          </CardContent>
        </Card>

        {/* Audio Information Form */}
        <Card className="shadow-sm border-border/50">
          <CardHeader>
            <CardTitle className="text-lg font-medium">Información del Audio</CardTitle>
          </CardHeader>
          <CardContent className="space-y-6">
            <div className="space-y-2">
              <Label htmlFor="name">Nombre del Audio *</Label>
              <Input id="name" placeholder="Ej: Meditación Matinal" />
            </div>

            <div className="space-y-2">
              <Label htmlFor="description">Descripción</Label>
              <Textarea 
                id="description" 
                placeholder="Describe brevemente el contenido del audio..."
                rows={3}
              />
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label>Autor *</Label>
                <Select>
                  <SelectTrigger>
                    <SelectValue placeholder="Seleccionar autor" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="juan-perez">Juan Pérez</SelectItem>
                    <SelectItem value="maria-garcia">María García</SelectItem>
                    <SelectItem value="carlos-ruiz">Carlos Ruiz</SelectItem>
                    <SelectItem value="ana-lopez">Ana López</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div className="space-y-2">
                <Label>Serie *</Label>
                <Select>
                  <SelectTrigger>
                    <SelectValue placeholder="Seleccionar serie" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="meditacion-principiantes">Meditación para Principiantes</SelectItem>
                    <SelectItem value="mindfulness-diario">Mindfulness Diario</SelectItem>
                    <SelectItem value="relajacion-profunda">Relajación Profunda</SelectItem>
                    <SelectItem value="gestion-estres">Gestión del Estrés</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>

            <div className="space-y-2">
              <Label>Categoría *</Label>
              <Select>
                <SelectTrigger>
                  <SelectValue placeholder="Seleccionar categoría" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="relajacion">Relajación</SelectItem>
                  <SelectItem value="sueno">Sueño</SelectItem>
                  <SelectItem value="mindfulness">Mindfulness</SelectItem>
                  <SelectItem value="bienestar">Bienestar</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label>Fecha</Label>
                <Popover>
                  <PopoverTrigger asChild>
                    <Button
                      variant="outline"
                      className={cn(
                        "w-full justify-start text-left font-normal",
                        !date && "text-muted-foreground"
                      )}
                    >
                      <CalendarIcon className="mr-2 h-4 w-4" />
                      {date ? format(date, "PPP") : <span>Seleccionar fecha</span>}
                    </Button>
                  </PopoverTrigger>
                  <PopoverContent className="w-auto p-0">
                    <Calendar
                      mode="single"
                      selected={date}
                      onSelect={setDate}
                      initialFocus
                      className="p-3 pointer-events-auto"
                    />
                  </PopoverContent>
                </Popover>
              </div>

              <div className="space-y-2">
                <Label>Estado</Label>
                <Select defaultValue="normal">
                  <SelectTrigger>
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="normal">Normal</SelectItem>
                    <SelectItem value="pendiente">Pendiente</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* File Queue */}
        {queuedFiles.length > 0 && (
          <Card className="shadow-sm border-border/50">
            <CardHeader>
              <CardTitle className="text-lg font-medium">Cola de Archivos</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {queuedFiles.map((queuedFile, index) => (
                  <div key={index} className="border border-border rounded-lg p-4">
                    <div className="flex items-center justify-between mb-2">
                      <div className="flex items-center space-x-3">
                        <FileAudio className="h-8 w-8 text-primary" />
                        <div>
                          <p className="font-medium">{queuedFile.file.name}</p>
                          <p className="text-sm text-muted-foreground">
                            {(queuedFile.file.size / 1024 / 1024).toFixed(2)} MB
                          </p>
                        </div>
                      </div>
                      <div className="flex items-center space-x-2">
                        {queuedFile.status === 'completed' && (
                          <Check className="h-5 w-5 text-success" />
                        )}
                        {queuedFile.status === 'error' && (
                          <AlertCircle className="h-5 w-5 text-destructive" />
                        )}
                      </div>
                    </div>
                    <div className="space-y-1">
                      <div className="flex justify-between text-sm">
                        <span>Progreso</span>
                        <span>{queuedFile.progress}%</span>
                      </div>
                      <Progress value={queuedFile.progress} className="h-2" />
                    </div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>
        )}
      </div>

      {/* Action Buttons */}
      <div className="flex items-center justify-end space-x-4">
        <Button variant="outline" asChild>
          <Link to="/audios">Cancelar</Link>
        </Button>
        <Button variant="success" className="min-w-[120px]">
          Guardar Audio
        </Button>
      </div>
    </div>
  );
};

export default UploadAudio;