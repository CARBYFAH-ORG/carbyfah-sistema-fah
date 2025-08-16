<template>
  <header class="header-personal">
    <div class="header-content">
      <!-- Logo y título -->
      <div class="header-brand">
        <div class="logo">🚁</div>
        <div class="title-section">
          <h1>FAH Personal</h1>
          <span>Sistema de Gestión de Personal</span>
        </div>
      </div>

      <!-- Usuario info -->
      <div class="user-section">
        <span class="welcome-text">Bienvenido</span>
        <div class="user-info">
          <span class="user-name">Usuario</span>
          <button class="logout-btn" @click="handleLogout">Salir</button>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
const handleLogout = () => {
  console.log("Logout desde header");
};
</script>

<style scoped>
.header-personal {
  background: #1e3a5f;
  color: white;
  height: 70px;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.header-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 100%;
  padding: 0 24px;
}

.header-brand {
  display: flex;
  align-items: center;
  gap: 12px;
}

.logo {
  font-size: 32px;
}

.title-section h1 {
  font-size: 20px;
  font-weight: 600;
  margin: 0;
}

.title-section span {
  font-size: 14px;
  color: #d4af37;
}

.user-section {
  display: flex;
  align-items: center;
  gap: 12px;
}

.welcome-text {
  color: #d4af37;
  font-size: 14px;
}

.user-name {
  font-weight: 600;
}

.logout-btn {
  background: #d4af37;
  color: #1e3a5f;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s ease;
}

.logout-btn:hover {
  background: #f0c674;
}
</style>
