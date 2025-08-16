# Etapa de desarrollo
FROM node:18-alpine as development

# Configurar directorio de trabajo
WORKDIR /app

# Copiar archivos de dependencias
COPY package*.json ./

# Instalar dependencias
RUN npm install

# Copiar código fuente
COPY . .

# Exponer puerto para desarrollo
EXPOSE 5173

# Comando de desarrollo
CMD ["npm", "run", "dev"]

# Etapa de producción
FROM node:18-alpine as build

WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Etapa final - Nginx para servir
FROM nginx:1.25-alpine as production

# Copiar archivos construidos
COPY --from=build /app/dist /usr/share/nginx/html

# Configurar Nginx
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf

# Exponer puerto
EXPOSE 5173

CMD ["nginx", "-g", "daemon off;"]