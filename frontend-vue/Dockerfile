# source/frontend-vue/Dockerfile

# Stage 1: Build Vue
FROM node:20-alpine as build-stage
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Stage 2: Serve dengan Nginx
FROM nginx:alpine as production-stage
# Copy hasil build dari Stage 1 ke folder Nginx
COPY --from=build-stage /app/dist /usr/share/nginx/html

# Copy config nginx custom (jika kamu punya file nginx.conf khusus di folder vue)
# Jika tidak, default nginx akan jalan, tapi untuk SPA butuh config khusus (lihat bawah)
COPY nginx-spa.conf /etc/nginx/conf.d/default.conf

EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
