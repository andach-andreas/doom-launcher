import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// import select2 from 'select2';
// select2();
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();
