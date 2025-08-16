<template>
  <div class="dashboard-personal">
    <!-- Header -->
    <div class="dashboard-header">
      <h1>Dashboard Personal FAH</h1>
      <p>Sistema de gestión de personal - FA-1</p>
    </div>

    <!-- Métricas Principales -->
    <div class="metricas-grid">
      <!-- Total Personal -->
      <Card class="metrica-card">
        <template #content>
          <div class="metrica-contenido">
            <div class="metrica-icono total">
              <i class="pi pi-users"></i>
            </div>
            <div class="metrica-datos">
              <h3>3,644</h3>
              <p>Total Personal</p>
              <small>87% ocupación</small>
            </div>
          </div>
        </template>
      </Card>

      <!-- Vacantes -->
      <Card class="metrica-card">
        <template #content>
          <div class="metrica-contenido">
            <div class="metrica-icono vacantes">
              <i class="pi pi-exclamation-triangle"></i>
            </div>
            <div class="metrica-datos">
              <h3>518</h3>
              <p>Vacantes</p>
              <small>13% déficit</small>
            </div>
          </div>
        </template>
      </Card>

      <!-- Alertas -->
      <Card class="metrica-card">
        <template #content>
          <div class="metrica-contenido">
            <div class="metrica-icono alertas">
              <i class="pi pi-bell"></i>
            </div>
            <div class="metrica-datos">
              <h3>12</h3>
              <p>Alertas Críticas</p>
              <small>Requieren atención</small>
            </div>
          </div>
        </template>
      </Card>

      <!-- Estado Operativo -->
      <Card class="metrica-card">
        <template #content>
          <div class="metrica-contenido">
            <div class="metrica-icono operativo">
              <i class="pi pi-check-circle"></i>
            </div>
            <div class="metrica-datos">
              <h3>92%</h3>
              <p>Personal Operativo</p>
              <small>Disponible para misiones</small>
            </div>
          </div>
        </template>
      </Card>
    </div>

    <!-- Contenido Principal -->
    <div class="dashboard-contenido">
      <Card class="info-card">
        <template #header>
          <h2>Sistema Personal FAH</h2>
        </template>
        <template #content>
          <div class="info-contenido">
            <p>
              <strong
                >✅ Microservicio Personal funcionando correctamente</strong
              >
            </p>
            <p>
              Este es el sistema independiente de gestión de personal de la FAH.
              Conectado desde el admin-frontend principal.
            </p>

            <div class="funcionalidades">
              <h3>Funcionalidades Disponibles:</h3>
              <ul>
                <li>📊 Dashboard adaptativo por nivel</li>
                <li>👥 Gestión de personal completa</li>
                <li>🔒 Sistema de niveles jerárquicos</li>
                <li>💬 CARBYCHAT integrado</li>
                <li>📋 Sistema de solicitudes</li>
                <li>📈 Reportes y estadísticas</li>
              </ul>
            </div>

            <div class="acciones-rapidas">
              <h3>Acciones Rápidas:</h3>
              <div class="botones-grid">
                <Button
                  label="Ver Personal"
                  icon="pi pi-users"
                  class="p-button-outlined"
                  @click="mostrarMensaje('Personal')"
                />
                <Button
                  label="Nueva Solicitud"
                  icon="pi pi-plus"
                  class="p-button-outlined"
                  @click="mostrarMensaje('Solicitud')"
                />
                <Button
                  label="CARBYCHAT"
                  icon="pi pi-comment"
                  class="p-button-outlined"
                  @click="mostrarMensaje('CARBYCHAT')"
                />
                <Button
                  label="Reportes"
                  icon="pi pi-chart-bar"
                  class="p-button-outlined"
                  @click="mostrarMensaje('Reportes')"
                />
              </div>
            </div>
          </div>
        </template>
      </Card>
    </div>

    <!-- Toast -->
    <Toast />
  </div>
</template>

<script>
import { onMounted } from "vue";
import { useToast } from "primevue/usetoast";

export default {
  name: "DashboardPersonalView",
  setup() {
    const toast = useToast();

    const mostrarMensaje = (modulo) => {
      toast.add({
        severity: "info",
        summary: `${modulo} FAH`,
        detail: `Módulo ${modulo} en desarrollo...`,
        life: 3000,
      });
    };

    onMounted(() => {
      console.log("✅ Dashboard Personal cargado correctamente");

      toast.add({
        severity: "success",
        summary: "Sistema Personal FAH",
        detail: "Microservicio cargado exitosamente",
        life: 4000,
      });
    });

    return {
      mostrarMensaje,
    };
  },
};
</script>

<style scoped>
.dashboard-personal {
  padding: 1.5rem;
  min-height: 100vh;
  background: #f8f9fa;
}

.dashboard-header {
  margin-bottom: 2rem;
  text-align: center;
}

.dashboard-header h1 {
  font-size: 2rem;
  font-weight: 600;
  color: #1e3a5f;
  margin: 0 0 0.5rem 0;
}

.dashboard-header p {
  color: #6c757d;
  margin: 0;
  font-size: 1.1rem;
}

.metricas-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.metrica-card {
  border: none;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.metrica-contenido {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
}

.metrica-icono {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
}

.metrica-icono.total {
  background: #0ea5e9;
}
.metrica-icono.vacantes {
  background: #ef4444;
}
.metrica-icono.alertas {
  background: #f59e0b;
}
.metrica-icono.operativo {
  background: #10b981;
}

.metrica-datos h3 {
  font-size: 1.8rem;
  font-weight: 700;
  color: #1e3a5f;
  margin: 0 0 0.25rem 0;
}

.metrica-datos p {
  font-weight: 500;
  color: #374151;
  margin: 0 0 0.25rem 0;
}

.metrica-datos small {
  color: #6b7280;
  font-size: 0.875rem;
}

.dashboard-contenido {
  max-width: 800px;
  margin: 0 auto;
}

.info-card {
  border: none;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.info-contenido {
  padding: 1rem;
}

.info-contenido h3 {
  color: #1e3a5f;
  margin: 1.5rem 0 1rem 0;
}

.info-contenido ul {
  margin-bottom: 1.5rem;
}

.info-contenido li {
  margin-bottom: 0.5rem;
  color: #374151;
}

.botones-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-top: 1rem;
}

@media (max-width: 768px) {
  .metricas-grid {
    grid-template-columns: 1fr;
  }

  .botones-grid {
    grid-template-columns: 1fr;
  }
}
</style>
