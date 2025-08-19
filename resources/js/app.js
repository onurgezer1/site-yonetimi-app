import './bootstrap';
import { createApp } from 'vue';

const app = createApp({});

// Global error handler
app.config.errorHandler = (err, vm, info) => {
    console.error('Vue error:', err, info);
};

app.mount('#app');