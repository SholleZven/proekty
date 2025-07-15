import { createApp } from 'vue';
// import TableComponent from './pages/TableComponent.vue';
import router from '../router';
import App from '../App.vue';

// createApp(TableComponent).use(router).mount('#app');
createApp(App).use(router).mount('#app');
