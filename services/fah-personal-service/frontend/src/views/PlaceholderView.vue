<template>
  <div class="placeholder-view">
    <Card class="placeholder-card">
      <template #content>
        <div class="placeholder-content">
          <i class="pi pi-cog placeholder-icon"></i>
          <h2>{{ titulo }}</h2>
          <p>{{ mensaje }}</p>
          <div class="placeholder-info">
            <small>Este módulo será implementado próximamente</small>
          </div>
        </div>
      </template>
    </Card>
  </div>
</template>

<script>
import { computed } from "vue";
import { useRoute } from "vue-router";

export default {
  name: "PlaceholderView",
  setup() {
    const route = useRoute();

    const titulo = computed(() => route.meta.title || "Módulo en Desarrollo");
    const mensaje = computed(
      () => route.meta.placeholder || "Esta funcionalidad está en desarrollo"
    );

    return {
      titulo,
      mensaje,
    };
  },
};
</script>

<style scoped>
.placeholder-view {
  padding: 2rem;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 400px;
}

.placeholder-card {
  max-width: 500px;
  width: 100%;
}

.placeholder-content {
  text-align: center;
  padding: 2rem;
}

.placeholder-icon {
  font-size: 4rem;
  color: #6b7280;
  margin-bottom: 1rem;
}

.placeholder-content h2 {
  color: #1e3a5f;
  margin-bottom: 1rem;
}

.placeholder-content p {
  color: #6b7280;
  margin-bottom: 1.5rem;
}

.placeholder-info small {
  color: #9ca3af;
  font-style: italic;
}
</style>
