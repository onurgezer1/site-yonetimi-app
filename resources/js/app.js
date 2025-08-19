import './bootstrap';
import { createApp } from 'vue';
import Alpine from 'alpinejs';

// Initialize Alpine.js
window.Alpine = Alpine;
Alpine.start();

const app = createApp({});

// Global error handler
app.config.errorHandler = (err, vm, info) => {
    console.error('Vue error:', err, info);
};

app.mount('#app');