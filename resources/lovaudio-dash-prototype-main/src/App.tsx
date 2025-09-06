import { Toaster } from "@/components/ui/toaster";
import { Toaster as Sonner } from "@/components/ui/sonner";
import { TooltipProvider } from "@/components/ui/tooltip";
import { SidebarProvider, SidebarTrigger } from "@/components/ui/sidebar";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { BrowserRouter, Routes, Route } from "react-router-dom";
import AppSidebar from "@/components/AppSidebar";
import Dashboard from "./pages/Dashboard";
import Authors from "./pages/Authors";
import Series from "./pages/Series";
import Audios from "./pages/Audios";
import UploadAudio from "./pages/UploadAudio";
import PublicAudios from "./pages/PublicAudios";
import NotFound from "./pages/NotFound";

const queryClient = new QueryClient();

const App = () => (
  <QueryClientProvider client={queryClient}>
    <TooltipProvider>
      <Toaster />
      <Sonner />
      <BrowserRouter>
        <SidebarProvider>
          <div className="min-h-screen flex w-full">
            <AppSidebar />
            <div className="flex-1 flex flex-col">
              <header className="h-14 border-b border-border bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
                <div className="flex h-14 items-center px-4">
                  <SidebarTrigger className="mr-4" />
                  <div className="flex items-center space-x-4">
                    <h1 className="font-semibold text-foreground">Sistema de Gestión de Audios</h1>
                  </div>
                </div>
              </header>
              <main className="flex-1 p-6 bg-background">
                <Routes>
                  <Route path="/" element={<Dashboard />} />
                  <Route path="/authors" element={<Authors />} />
                  <Route path="/series" element={<Series />} />
                  <Route path="/audios" element={<Audios />} />
                  <Route path="/audios/new" element={<UploadAudio />} />
                  <Route path="/public" element={<PublicAudios />} />
                  <Route path="*" element={<NotFound />} />
                </Routes>
              </main>
            </div>
          </div>
        </SidebarProvider>
      </BrowserRouter>
    </TooltipProvider>
  </QueryClientProvider>
);

export default App;
