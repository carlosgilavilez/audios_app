import { NavLink, useLocation } from "react-router-dom";
import {
  Sidebar,
  SidebarContent,
  SidebarGroup,
  SidebarGroupContent,
  SidebarGroupLabel,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from "@/components/ui/sidebar";
import { LayoutDashboard, Users, Music, Headphones, List } from "lucide-react";

const AppSidebar = () => {
  const location = useLocation();
  const currentPath = location.pathname;

  const menuItems = [
    {
      title: "Dashboard",
      url: "/",
      icon: LayoutDashboard,
    },
    {
      title: "Autores",
      url: "/authors",
      icon: Users,
    },
    {
      title: "Series",
      url: "/series",
      icon: Music,
    },
    {
      title: "Audios",
      url: "/audios",
      icon: Headphones,
    },
  ];

  const publicMenuItems = [
    {
      title: "Lista de audios",
      url: "/public",
      icon: List,
    },
  ];

  const isActive = (path: string) => currentPath === path;
  
  const getNavClasses = (isActive: boolean) =>
    isActive 
      ? "bg-sidebar-accent text-sidebar-primary font-medium" 
      : "hover:bg-sidebar-accent/50 text-sidebar-foreground";

  return (
    <Sidebar className="border-sidebar-border">
      <SidebarContent>
        <div className="px-4 py-6">
          <h2 className="text-lg font-semibold text-sidebar-primary">Audio Manager</h2>
          <p className="text-sm text-sidebar-foreground/60">Sistema de Gestión</p>
        </div>
        
        <SidebarGroup>
          <SidebarGroupLabel>Administración</SidebarGroupLabel>
          <SidebarGroupContent>
            <SidebarMenu>
              {menuItems.map((item) => (
                <SidebarMenuItem key={item.title}>
                  <SidebarMenuButton asChild className={getNavClasses(isActive(item.url))}>
                    <NavLink to={item.url} end>
                      <item.icon className="mr-2 h-4 w-4" />
                      <span>{item.title}</span>
                    </NavLink>
                  </SidebarMenuButton>
                </SidebarMenuItem>
              ))}
            </SidebarMenu>
          </SidebarGroupContent>
        </SidebarGroup>

        <SidebarGroup>
          <SidebarGroupLabel>Público</SidebarGroupLabel>
          <SidebarGroupContent>
            <SidebarMenu>
              {publicMenuItems.map((item) => (
                <SidebarMenuItem key={item.title}>
                  <SidebarMenuButton asChild className={getNavClasses(isActive(item.url))}>
                    <NavLink to={item.url} end>
                      <item.icon className="mr-2 h-4 w-4" />
                      <span>{item.title}</span>
                    </NavLink>
                  </SidebarMenuButton>
                </SidebarMenuItem>
              ))}
            </SidebarMenu>
          </SidebarGroupContent>
        </SidebarGroup>
      </SidebarContent>
    </Sidebar>
  );
};

export default AppSidebar;