import { registerEventListeners} from "mmuo"
import * as bootstrap from '~bootstrap';
import axios from 'axios';
import '@fortawesome/fontawesome-free/css/all.css'

window.addEventListener("DOMContentLoaded", function() {
    registerEventListeners()
}, false);

try {
window.bootstrap =  bootstrap;
} catch (e) {}

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = axios

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';