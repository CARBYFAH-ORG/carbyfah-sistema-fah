<!-- services\fah-admin-frontend\src\components\formularios\ModalMapaCoordenadas.vue -->
<template>
  <Dialog
    v-model:visible="visible"
    :modal="true"
    :closable="true"
    :dismissable-mask="false"
    class="modal-mapa-pantalla-completa"
    :style="{ width: '100vw', height: '100vh' }"
    header="Seleccionar Ubicación en el Mapa"
    @hide="cerrarModal"
    :maximizable="false"
    :draggable="false"
    :resizable="false"
    position="center"
  >
    <div class="contenedor-mapa-completo">
      <!-- Contenedor del mapa pantalla completa -->
      <div
        ref="mapaContainer"
        class="mapa-container-completo"
        :class="{ 'mapa-cargando': cargandoMapa }"
      >
        <div v-if="cargandoMapa" class="overlay-carga-completo">
          <ProgressSpinner size="60" strokeWidth="4" />
          <span class="texto-carga-completo">Cargando mapa militar...</span>
        </div>
      </div>
    </div>

    <template #footer>
      <div class="footer-completo-con-info">
        <!-- Instrucciones rapidas en el footer -->
        <div class="instrucciones-footer">
          <div class="instruccion-mini">
            <i class="pi pi-map-marker"></i>
            <span>Haga clic para seleccionar</span>
          </div>
          <div class="instruccion-mini">
            <i class="pi pi-search"></i>
            <span>Zoom para precisión</span>
          </div>
        </div>

        <!-- Coordenadas seleccionadas en el footer -->
        <div class="coordenadas-footer" v-if="coordenadasSeleccionadas">
          <div class="coordenada-mini">
            <label>LAT:</label>
            <span>{{ coordenadasSeleccionadas.latitud }}</span>
          </div>
          <div class="coordenada-mini">
            <label>LNG:</label>
            <span>{{ coordenadasSeleccionadas.longitud }}</span>
          </div>
          <div class="coordenada-mini">
            <label>ALT:</label>
            <span>{{ coordenadasSeleccionadas.altitud }}m</span>
          </div>
        </div>

        <!-- Botones de accion -->
        <div class="botones-accion">
          <Button
            label="✖ CANCELAR"
            icon="pi pi-times"
            class="boton-cancelar-completo"
            @click="cerrarModal"
          />
          <Button
            label="✅ CONFIRMAR"
            icon="pi pi-check"
            class="boton-confirmar-completo"
            :disabled="!coordenadasSeleccionadas"
            @click="confirmarCoordenadas"
          />
        </div>
      </div>
    </template>
  </Dialog>
</template>

<script>
import { ref, onMounted, onUnmounted, watch, nextTick } from "vue";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import ProgressSpinner from "primevue/progressspinner";

export default {
  name: "ModalMapaCoordenadas",

  components: {
    Dialog,
    Button,
    ProgressSpinner,
  },

  props: {
    modelValue: {
      type: Boolean,
      default: false,
    },
    coordenadasIniciales: {
      type: Object,
      default: () => ({
        latitud: 14.0818,
        longitud: -87.2068,
        altitud: 0,
      }),
    },
  },

  emits: ["update:modelValue", "coordenadas-seleccionadas"],

  setup(props, { emit }) {
    const visible = ref(false);
    const mapaContainer = ref(null);
    const cargandoMapa = ref(true);
    const coordenadasSeleccionadas = ref(null);

    // Variables del mapa
    let mapa = null;
    let marcadorCoordenadas = null;
    let capaVectorMarcador = null;

    // Script de OpenLayers cargado dinamicamente
    const cargarOpenLayers = () => {
      return new Promise((resolve, reject) => {
        if (window.ol) {
          resolve();
          return;
        }

        // Cargar CSS de OpenLayers
        const linkCSS = document.createElement("link");
        linkCSS.rel = "stylesheet";
        linkCSS.href = "https://cdn.jsdelivr.net/npm/ol@7.5.2/ol.css";
        document.head.appendChild(linkCSS);

        // Cargar JavaScript de OpenLayers
        const script = document.createElement("script");
        script.src = "https://cdn.jsdelivr.net/npm/ol@7.5.2/dist/ol.js";
        script.onload = () => {
          resolve();
        };
        script.onerror = () => {
          reject(new Error("No se pudo cargar OpenLayers"));
        };
        document.head.appendChild(script);
      });
    };

    // Obtener altitud usando API de elevacion
    const obtenerAltitud = async (latitud, longitud) => {
      try {
        const response = await fetch(
          `https://api.open-elevation.com/api/v1/lookup?locations=${latitud},${longitud}`
        );
        const data = await response.json();

        if (data.results && data.results.length > 0) {
          return Math.round(data.results[0].elevation);
        }

        return 0;
      } catch (error) {
        return 0;
      }
    };

    // Inicializar el mapa
    const inicializarMapa = async () => {
      if (!mapaContainer.value || mapa) return;

      try {
        cargandoMapa.value = true;
        await cargarOpenLayers();

        // Obtener clases de OpenLayers
        const Map = window.ol.Map;
        const View = window.ol.View;
        const TileLayer = window.ol.layer.Tile;
        const VectorLayer = window.ol.layer.Vector;
        const OSM = window.ol.source.OSM;
        const XYZ = window.ol.source.XYZ;
        const VectorSource = window.ol.source.Vector;
        const Point = window.ol.geom.Point;
        const Feature = window.ol.Feature;
        const Style = window.ol.style.Style;
        const CircleStyle = window.ol.style.Circle;
        const Fill = window.ol.style.Fill;
        const Stroke = window.ol.style.Stroke;
        const fromLonLat = window.ol.proj.fromLonLat;
        const toLonLat = window.ol.proj.toLonLat;

        // Crear capas base
        const capaOSM = new TileLayer({
          source: new OSM(),
          title: "Mapa Base",
        });

        const capaSatelital = new TileLayer({
          source: new XYZ({
            url: "https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}",
            attributions:
              "Esri, Maxar, GeoEye, Earthstar Geographics, CNES/Airbus DS, USDA, USGS, AeroGRID, IGN, and the GIS User Community",
          }),
          title: "Vista Satelital",
          visible: false,
        });

        // Crear capa para el marcador con estilo mas visible
        capaVectorMarcador = new VectorLayer({
          source: new VectorSource(),
          style: new Style({
            image: new CircleStyle({
              radius: 15,
              fill: new Fill({ color: "#ff0000" }),
              stroke: new Stroke({ color: "#ffffff", width: 4 }),
            }),
          }),
        });

        // Crear el mapa
        mapa = new Map({
          target: mapaContainer.value,
          layers: [capaOSM, capaSatelital, capaVectorMarcador],
          view: new View({
            center: fromLonLat([
              props.coordenadasIniciales.longitud,
              props.coordenadasIniciales.latitud,
            ]),
            zoom: 15,
          }),
        });

        // Evento de clic en el mapa
        mapa.on("click", async (evento) => {
          const coordenadas = toLonLat(evento.coordinate);
          const longitud = parseFloat(coordenadas[0].toFixed(6));
          const latitud = parseFloat(coordenadas[1].toFixed(6));

          // Obtener altitud
          const altitud = await obtenerAltitud(latitud, longitud);

          // Actualizar coordenadas seleccionadas
          coordenadasSeleccionadas.value = {
            latitud,
            longitud,
            altitud,
          };

          // Limpiar marcador anterior
          capaVectorMarcador.getSource().clear();

          // Crear nuevo marcador
          const marcador = new Feature({
            geometry: new Point(evento.coordinate),
          });

          capaVectorMarcador.getSource().addFeature(marcador);
        });

        // Marcar coordenadas iniciales si existen
        if (
          props.coordenadasIniciales.latitud &&
          props.coordenadasIniciales.longitud
        ) {
          const coordenadaInicial = fromLonLat([
            props.coordenadasIniciales.longitud,
            props.coordenadasIniciales.latitud,
          ]);

          const marcadorInicial = new Feature({
            geometry: new Point(coordenadaInicial),
          });

          capaVectorMarcador.getSource().addFeature(marcadorInicial);

          coordenadasSeleccionadas.value = { ...props.coordenadasIniciales };
        }
      } catch (error) {
        console.error("Error inicializando mapa:", error);
      } finally {
        cargandoMapa.value = false;
      }
    };

    // Limpiar mapa
    const limpiarMapa = () => {
      if (mapa) {
        mapa.setTarget(null);
        mapa = null;
        marcadorCoordenadas = null;
        capaVectorMarcador = null;
      }
    };

    // Confirmar coordenadas
    const confirmarCoordenadas = () => {
      if (coordenadasSeleccionadas.value) {
        emit("coordenadas-seleccionadas", coordenadasSeleccionadas.value);
        cerrarModal();
      }
    };

    // Cerrar modal
    const cerrarModal = () => {
      visible.value = false;
      emit("update:modelValue", false);
    };

    // Watchers
    watch(
      () => props.modelValue,
      (nuevoValor) => {
        visible.value = nuevoValor;
        if (nuevoValor) {
          nextTick(() => {
            inicializarMapa();
          });
        } else {
          limpiarMapa();
        }
      }
    );

    // Lifecycle
    onUnmounted(() => {
      limpiarMapa();
    });

    return {
      visible,
      mapaContainer,
      cargandoMapa,
      coordenadasSeleccionadas,
      confirmarCoordenadas,
      cerrarModal,
    };
  },
};
</script>

<style scoped>
/* Modal pantalla completa */
.modal-mapa-pantalla-completa {
  font-family: "Inter", -apple-system, BlinkMacSystemFont, sans-serif;
}

/* Contenedor principal que llena toda la pantalla */
.contenedor-mapa-completo {
  display: flex;
  flex-direction: column;
  height: 85vh;
  width: 100%;
  background: #1e293b;
  border-radius: 0;
  overflow: hidden;
}

/* Contenedor del mapa que llena toda la pantalla disponible */
.mapa-container-completo {
  flex: 1;
  position: relative;
  background: #1e293b;
  width: 100%;
  height: 100%;
}

.mapa-cargando {
  background: linear-gradient(45deg, #1e293b, #334155);
}

.overlay-carga-completo {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: rgba(15, 23, 42, 0.95);
  z-index: 1000;
}

.texto-carga-completo {
  color: #f1f5f9;
  margin-top: 20px;
  font-size: 18px;
  font-weight: 600;
}

/* Footer completo con toda la informacion */
.footer-completo-con-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 20px;
  background: linear-gradient(90deg, #1e293b 0%, #334155 50%, #1e293b 100%);
  border-top: 3px solid #059669;
  gap: 20px;
}

/* Instrucciones mini en el footer */
.instrucciones-footer {
  display: flex;
  gap: 25px;
  align-items: center;
}

.instruccion-mini {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #94a3b8;
  font-size: 13px;
  font-weight: 500;
}

.instruccion-mini i {
  color: #10b981;
  font-size: 16px;
}

/* Coordenadas compactas en el footer */
.coordenadas-footer {
  display: flex;
  gap: 15px;
  align-items: center;
  background: rgba(15, 23, 42, 0.8);
  padding: 10px 15px;
  border-radius: 8px;
  border: 2px solid #334155;
}

.coordenada-mini {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
}

.coordenada-mini label {
  color: #94a3b8;
  font-size: 10px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.coordenada-mini span {
  color: #f1f5f9;
  font-size: 13px;
  font-weight: 700;
  font-family: "Courier New", monospace;
  background: rgba(59, 130, 246, 0.2);
  padding: 4px 8px;
  border-radius: 4px;
  border: 1px solid rgba(59, 130, 246, 0.4);
}

/* Botones de accion en el footer */
.botones-accion {
  display: flex;
  gap: 15px;
}

.boton-cancelar-completo {
  background: linear-gradient(135deg, #dc2626, #ef4444) !important;
  border: 2px solid #fca5a5 !important;
  color: white !important;
  font-weight: 700 !important;
  font-size: 14px !important;
  border-radius: 10px !important;
  padding: 12px 20px !important;
  transition: all 0.3s ease !important;
  box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4) !important;
  letter-spacing: 0.5px !important;
  min-width: 130px !important;
}

.boton-cancelar-completo:hover {
  background: linear-gradient(135deg, #b91c1c, #dc2626) !important;
  border-color: #f87171 !important;
  transform: translateY(-2px) !important;
  box-shadow: 0 6px 18px rgba(220, 38, 38, 0.6) !important;
}

.boton-confirmar-completo {
  background: linear-gradient(135deg, #059669, #10b981) !important;
  border: 2px solid #6ee7b7 !important;
  color: white !important;
  font-weight: 700 !important;
  font-size: 14px !important;
  border-radius: 10px !important;
  padding: 12px 20px !important;
  transition: all 0.3s ease !important;
  box-shadow: 0 4px 12px rgba(5, 150, 105, 0.4) !important;
  letter-spacing: 0.5px !important;
  min-width: 150px !important;
}

.boton-confirmar-completo:hover {
  background: linear-gradient(135deg, #047857, #059669) !important;
  border-color: #34d399 !important;
  transform: translateY(-2px) !important;
  box-shadow: 0 6px 18px rgba(5, 150, 105, 0.6) !important;
}

.boton-confirmar-completo:disabled {
  background: linear-gradient(135deg, #6b7280, #9ca3af) !important;
  border-color: #d1d5db !important;
  transform: none !important;
  box-shadow: none !important;
  cursor: not-allowed !important;
}

/* Estilos del mapa OpenLayers mejorados */
:deep(.ol-viewport) {
  border-radius: 0;
  height: 100% !important;
}

:deep(.ol-control) {
  background: rgba(15, 23, 42, 0.95) !important;
  border-radius: 8px !important;
  border: 2px solid #334155 !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3) !important;
}

:deep(.ol-control button) {
  background: rgba(59, 130, 246, 0.9) !important;
  color: white !important;
  border-radius: 6px !important;
  font-size: 18px !important;
  font-weight: 700 !important;
  padding: 10px !important;
  border: 2px solid rgba(59, 130, 246, 0.5) !important;
  width: 40px !important;
  height: 40px !important;
}

:deep(.ol-control button:hover) {
  background: rgba(59, 130, 246, 1) !important;
  border-color: #3b82f6 !important;
  transform: scale(1.15) !important;
}

:deep(.ol-zoom) {
  top: 20px !important;
  left: 20px !important;
}

:deep(.ol-attribution) {
  bottom: 10px !important;
  right: 10px !important;
  background: rgba(15, 23, 42, 0.8) !important;
  color: #94a3b8 !important;
  font-size: 11px !important;
  padding: 6px 10px !important;
  border-radius: 6px !important;
}

/* Responsive para moviles */
@media (max-width: 768px) {
  .footer-completo-con-info {
    flex-direction: column;
    gap: 15px;
    padding: 15px;
  }

  .instrucciones-footer {
    justify-content: center;
  }

  .coordenadas-footer {
    gap: 10px;
  }

  .botones-accion {
    width: 100%;
    justify-content: center;
  }
}
</style>
