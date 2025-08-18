<template>
  <div class="custom-dropdown" :class="{ 'dropdown-open': isOpen }">
    <div
      class="dropdown-trigger"
      @click="toggleDropdown"
      :class="{ 'trigger-active': isOpen }"
    >
      <span class="dropdown-value">
        {{ selectedLabel || placeholder }}
      </span>
      <i
        class="pi pi-chevron-down dropdown-icon"
        :class="{ 'icon-rotated': isOpen }"
      ></i>
    </div>

    <Transition name="dropdown-fade">
      <div v-if="isOpen" class="dropdown-panel">
        <div class="dropdown-options">
          <div
            v-for="option in options"
            :key="option[optionValue]"
            class="dropdown-option"
            :class="{ 'option-selected': option[optionValue] === modelValue }"
            @click="selectOption(option)"
          >
            {{ option[optionLabel] }}
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from "vue";

export default {
  name: "CustomDropdown",
  props: {
    modelValue: null,
    options: { type: Array, default: () => [] },
    optionLabel: { type: String, default: "label" },
    optionValue: { type: String, default: "value" },
    placeholder: { type: String, default: "Seleccionar..." },
  },
  emits: ["update:modelValue", "change"],
  setup(props, { emit }) {
    const isOpen = ref(false);

    const selectedLabel = computed(() => {
      const selected = props.options.find(
        (opt) => opt[props.optionValue] === props.modelValue
      );
      return selected ? selected[props.optionLabel] : "";
    });

    const toggleDropdown = () => {
      isOpen.value = !isOpen.value;
    };

    const selectOption = (option) => {
      const value = option[props.optionValue];
      emit("update:modelValue", value);
      emit("change", { value });
      isOpen.value = false;
    };

    const closeDropdown = (event) => {
      if (!event.target.closest(".custom-dropdown")) {
        isOpen.value = false;
      }
    };

    onMounted(() => {
      document.addEventListener("click", closeDropdown);
    });

    onUnmounted(() => {
      document.removeEventListener("click", closeDropdown);
    });

    return {
      isOpen,
      selectedLabel,
      toggleDropdown,
      selectOption,
    };
  },
};
</script>

<style scoped>
.custom-dropdown {
  position: relative;
  width: 100%;
}

.dropdown-trigger {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
  background: #ffffff;
  border: 2px solid #e9ecef;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  min-height: 48px;
  font-weight: 500;
  color: #495057;
}

.dropdown-trigger:hover {
  border-color: #1e3a5f;
  box-shadow: 0 2px 8px rgba(30, 58, 95, 0.1);
}

.trigger-active {
  border-color: #1e3a5f !important;
  box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.1) !important;
}

.dropdown-value {
  flex: 1;
  text-align: left;
  font-size: 14px;
}

.dropdown-icon {
  color: #1e3a5f;
  font-size: 14px;
  transition: transform 0.3s ease;
}

.icon-rotated {
  transform: rotate(180deg);
}

.dropdown-panel {
  position: absolute;
  top: calc(100% + 4px);
  left: 0;
  right: 0;
  background: linear-gradient(
    135deg,
    rgba(30, 58, 95, 0.95),
    rgba(30, 58, 95, 0.98)
  );
  backdrop-filter: blur(10px);
  border: 2px solid rgba(30, 58, 95, 0.3);
  border-radius: 12px;
  box-shadow: 0 12px 30px rgba(30, 58, 95, 0.25), 0 4px 12px rgba(0, 0, 0, 0.1);
  z-index: 1000;
  overflow: hidden;
}

.dropdown-options {
  max-height: 200px;
  overflow-y: auto;
}

.dropdown-option {
  padding: 14px 18px;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 14px;
  font-weight: 500;
  color: #ffffff;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.dropdown-option:last-child {
  border-bottom: none;
}

.dropdown-option:hover {
  background: rgba(212, 175, 55, 0.2);
  color: #d4af37;
  transform: translateX(4px);
  padding-left: 22px;
}

.option-selected {
  background: rgba(212, 175, 55, 0.3) !important;
  color: #d4af37 !important;
  font-weight: 600;
}

.option-selected::before {
  content: "✓";
  margin-right: 8px;
  color: #d4af37;
  font-weight: bold;
}

/* Animaciones */
.dropdown-fade-enter-active,
.dropdown-fade-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.dropdown-fade-enter-from {
  opacity: 0;
  transform: translateY(-10px) scale(0.95);
}

.dropdown-fade-leave-to {
  opacity: 0;
  transform: translateY(-5px) scale(0.98);
}

/* Scrollbar personalizado */
.dropdown-options::-webkit-scrollbar {
  width: 6px;
}

.dropdown-options::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.1);
}

.dropdown-options::-webkit-scrollbar-thumb {
  background: rgba(212, 175, 55, 0.5);
  border-radius: 3px;
}

.dropdown-options::-webkit-scrollbar-thumb:hover {
  background: rgba(212, 175, 55, 0.7);
}
</style>
