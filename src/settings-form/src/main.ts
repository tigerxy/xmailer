import type { Group, Settings, SSLOptions } from "./interfaces";
import App from './Formular.svelte';

declare var settings: Settings;
declare var sslOptions: SSLOptions;
declare var userAttributes: string[];
declare var groups: Group[];

const app = new App({
	target: document.getElementById('settings-form'),
	props: { settings, sslOptions, userAttributes, groups }
});

export default app;
