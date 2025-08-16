<template>
  <div class="acceso-denegado-view">
    <Card class="error-card">
      <template #content>
        <div class="error-content">
          <i class="pi pi-lock error-icon"></i>
          <h1>Acceso Denegado</h1>
          <p>No tienes permisos para acceder a esta sección del sistema.</p>
          <div class="error-actions">
            <Button
              label="Ir al Dashboard"
              icon="pi pi-home"
              @click="irAlDashboard"
              class="p-button-outlined"
            />
            <Button
              label="Volver Atrás"
              icon="pi pi-arrow-left"
              @click="volverAtras"
              class="p-button-text"
            />
          </div>
        </div>
      </template>
    </Card>
  </div>
</template>

<script>
import { useRouter } from "vue-router";

export default {
  name: "AccesoDenegadoView",
  setup() {
    const router = useRouter();

    const irAlDashboard = () => {
      router.push("/dashboard");
    };

    const volverAtras = () => {
      router.go(-1);
    };

    return {
      irAlDashboard,
      volverAtras,
    };
  },
};
</script>

<style scoped>
.acceso-denegado-view {
  padding: 2rem;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: #f3f4f6;
}

.error-card {
  max-width: 500px;
  width: 100%;
}

.error-content {
  text-align: center;
  padding: 2rem;
}

.error-icon {
  font-size: 4rem;
  color: #ef4444;
  margin-bottom: 1rem;
}

.error-content h1 {
  color: #1e3a5f;
  margin-bottom: 1rem;
  font-size: 2rem;
}

.error-content p {
  color: #6b7280;
  margin-bottom: 2rem;
  font-size: 1.1rem;
}

.error-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
}
</style>
