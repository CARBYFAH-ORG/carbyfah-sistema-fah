<template>
  <div
    class="min-h-screen bg-gradient-to-br from-blue-900 via-blue-800 to-slate-900 flex items-center justify-center p-4 relative overflow-hidden"
  >
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10">
      <div
        class="absolute top-10 left-10 w-32 h-32 border-2 border-yellow-400 rounded-full"
      ></div>
      <div
        class="absolute top-32 right-20 w-24 h-24 border border-yellow-400 rounded-full"
      ></div>
      <div
        class="absolute bottom-20 left-32 w-16 h-16 border border-yellow-400 rounded-full"
      ></div>
      <div
        class="absolute bottom-40 right-10 w-20 h-20 border-2 border-yellow-400 rounded-full"
      ></div>
    </div>

    <!-- Login Container -->
    <div class="w-full max-w-md z-10">
      <!-- FAH Logo Section -->
      <div class="text-center mb-8">
        <!-- Logo Real FAH -->
        <div
          class="w-24 h-24 mx-auto mb-4 rounded-full shadow-2xl overflow-hidden border-4 border-yellow-400"
        >
          <img
            src="/src/assets/images/logo-fah.png"
            alt="Fuerza Aérea Hondureña"
            class="w-full h-full object-cover"
          />
        </div>

        <!-- System Title -->
        <h1 class="text-yellow-400 text-2xl font-bold mb-2 tracking-wider">
          CARBYFAH
        </h1>
        <p class="text-blue-200 text-sm mb-1 font-semibold">
          Centro Automatizado de Recursos
        </p>
        <p class="text-blue-300 text-xs mb-6">Fuerza Aérea Hondureña</p>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleLogin" class="space-y-6">
        <!-- Username Field -->
        <div class="relative">
          <div class="flex items-center gap-6">
            <!-- User Icon -->
            <div
              class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-lg"
            >
              <i class="pi pi-user text-lg text-gray-800"></i>
            </div>

            <!-- Input -->
            <div class="flex-1">
              <input
                id="username"
                v-model="credentials.username"
                type="text"
                placeholder="Usuario"
                :class="[
                  'w-full px-4 py-3 text-base rounded-full border-0 bg-gray-600/80 text-white placeholder:text-gray-300',
                  'focus:outline-none focus:ring-0 focus:bg-gray-600 transition-all duration-300',
                  errors.username ? 'bg-red-600/80 focus:bg-red-600' : '',
                ]"
                required
              />
            </div>
          </div>

          <!-- Error Message -->
          <div
            v-if="errors.username"
            class="mt-2 text-red-300 text-sm text-center"
          >
            {{ errors.username }}
          </div>
        </div>

        <!-- Password Field -->
        <div class="relative">
          <div class="flex items-center gap-6">
            <!-- Password Input -->
            <div class="flex-1 relative">
              <input
                id="password"
                v-model="credentials.password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="Contraseña"
                :class="[
                  'w-full px-4 pr-12 py-3 text-base rounded-full border-0 bg-gray-600/80 text-white placeholder:text-gray-300',
                  'focus:outline-none focus:ring-0 focus:bg-gray-600 transition-all duration-300',
                  errors.password ? 'bg-red-600/80 focus:bg-red-600' : '',
                ]"
                required
              />

              <!-- Eye Icon inside input -->
              <button
                type="button"
                @click="togglePasswordVisibility"
                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-300 hover:text-white transition-colors duration-200"
              >
                <i
                  :class="showPassword ? 'pi pi-eye-slash' : 'pi pi-eye'"
                  class="text-lg"
                ></i>
              </button>
            </div>

            <!-- Lock Icon -->
            <div
              class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-lg"
            >
              <i class="pi pi-lock text-lg text-gray-800"></i>
            </div>
          </div>

          <!-- Error Message -->
          <div
            v-if="errors.password"
            class="mt-2 text-red-300 text-sm text-center"
          >
            {{ errors.password }}
          </div>
        </div>

        <!-- Login Button -->
        <div class="pt-6">
          <Button
            type="submit"
            :loading="loading"
            :disabled="loading"
            class="w-full"
            unstyled
          >
            <div
              class="w-full bg-white hover:bg-gray-100 active:bg-gray-200 text-gray-900 font-bold text-lg py-4 rounded-full transition-all duration-300 transform hover:scale-105 active:scale-95 shadow-xl"
            >
              <div
                v-if="loading"
                class="flex items-center justify-center gap-2"
              >
                <i class="pi pi-spinner animate-spin text-lg"></i>
                <span>ACCEDIENDO...</span>
              </div>
              <span v-else>INGRESAR</span>
            </div>
          </Button>
        </div>
      </form>

      <!-- Additional Info -->
      <div class="mt-10 text-center space-y-3">
        <button
          class="text-white/70 hover:text-white text-sm transition-colors duration-200"
        >
          ¿Olvidaste tu contraseña?
        </button>

        <div class="text-white/50 text-xs space-y-1">
          <p>Fuerza Aérea Hondureña © 2025</p>
          <p>CARBYFAH v1.0 - Sistema Enterprise</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive } from "vue";
import { useRouter } from "vue-router";
import { useToast } from "primevue/usetoast";
import axios from "axios";

export default {
  name: "LoginView",
  setup() {
    const router = useRouter();
    const toast = useToast();

    const loading = ref(false);
    const showPassword = ref(false);
    const credentials = reactive({
      username: "carby", // ✅ Pre-llenar para testing
      password: "2301", // ✅ Pre-llenar para testing
    });
    const errors = reactive({});

    const togglePasswordVisibility = () => {
      showPassword.value = !showPassword.value;
    };

    const handleLogin = async () => {
      // Limpiar errores
      Object.keys(errors).forEach((key) => delete errors[key]);

      // Validación básica
      if (!credentials.username) {
        errors.username = "El usuario es obligatorio";
        return;
      }
      if (!credentials.password) {
        errors.password = "La contraseña es obligatoria";
        return;
      }

      loading.value = true;

      try {
        // ✅ CORREGIDO - Usar proxy en lugar de URL directa
        const response = await axios.post("/api/auth/login", {
          username: credentials.username,
          password: credentials.password,
        });

        console.log("Response from auth service:", response.data);

        if (response.data.success) {
          // Guardar token
          localStorage.setItem("fah_token", response.data.data.token);
          localStorage.setItem(
            "fah_user",
            JSON.stringify(response.data.data.usuario)
          );

          toast.add({
            severity: "success",
            summary: "Login Exitoso",
            detail: `Bienvenido ${response.data.data.usuario.username}`,
            life: 3000,
          });

          // Redirigir al dashboard
          await router.push("/dashboard");
        }
      } catch (error) {
        console.error("Error de login completo:", error);
        console.error("Error response:", error.response);

        let errorMessage = "Error de conexión con el servidor";

        if (error.response) {
          // El servidor respondió con error
          errorMessage =
            error.response.data?.message ||
            `Error ${error.response.status}: ${error.response.statusText}`;
        } else if (error.request) {
          // La petición se hizo pero no hubo respuesta
          errorMessage = "No se pudo conectar con el servidor de autenticación";
        }

        toast.add({
          severity: "error",
          summary: "Error de Acceso",
          detail: errorMessage,
          life: 5000,
        });
      } finally {
        loading.value = false;
      }
    };

    return {
      credentials,
      errors,
      loading,
      showPassword,
      handleLogin,
      togglePasswordVisibility,
    };
  },
};
</script>

<style>
/* Importar estilos externos organizados */
@import "@/styles/views/login-view.css";
</style>
