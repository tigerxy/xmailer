import type { Settings, SSLOptions } from "./interfaces";
import App from './Formular.svelte';

declare var settings: Settings;
declare var sslOptions: SSLOptions;

const app = new App({
	target: document.getElementById('settings-form'),
	props: { settings, sslOptions }
});

export default app;
