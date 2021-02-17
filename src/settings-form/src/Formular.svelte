<script lang="ts">
	import type { List, SSLOption, Settings, SSLOptions } from "./interfaces";
	import { append, each } from "svelte/internal";
	import {
		Form,
		FormGroup,
		FormText,
		Input,
		Button,
		Label,
		Table,
	} from "sveltestrap";
	import Server from "./Server.svelte";

	const PlaceholderEmail = "test@gmail.com";
	export let settings: Settings;
	export let sslOptions: SSLOptions;
	let { spam, replyto, addpagename, imap, smtp, lists, allow } = settings;
	$: settings = { spam, replyto, addpagename, imap, smtp, lists, allow };
	let { imap: imapSslOptions, smtp: smtpSslOptions } = sslOptions;

	$: console.log(JSON.stringify(settings));
	let boolToChecked = (val: boolean) => {
		return val ? " checked" : "";
	};
	function handleSubmit() {
		console.log(JSON.stringify(settings));
	}
</script>

<form on:submit|preventDefault={handleSubmit}>
	<fieldset>
		<Server bind:server={imap} name="Imap" sslOptions={imapSslOptions} />
	</fieldset>
	<fieldset>
		<Server bind:server={smtp} name="Smtp" sslOptions={smtpSslOptions} />
	</fieldset>
	<fieldset>
		<legend> Mailinglists </legend>
		<Table>
			<thead>
				<tr>
					<th>Name</th>
					<th>Email</th>
					<th>Folder</th>
					<th>Group ID</th>
					<th />
				</tr>
			</thead>
			<tbody>
				{#each lists as list, i}
					<tr>
						<td>
							<Input
								type="text"
								name="name"
								id="name"
								placeholder="Name"
								bind:value={list.name}
							/>
						</td><td>
							<Input
								type="email"
								name="email"
								id="email"
								placeholder={PlaceholderEmail}
								bind:value={list.email}
							/>
						</td><td>
							<Input
								type="text"
								name="mailbox"
								id="mailbox"
								placeholder="Folder"
								bind:value={list.mailbox}
							/>
						</td><td>
							<!-- TODO: select list groups -->
							<Input
								type="number"
								name="grpid"
								id="grpid"
								placeholder="0"
								bind:value={list.grpId}
							/>
						</td><td>
							<Button
								color="danger"
								on:click={() => {
									lists.splice(i, 1);
									lists = lists;
								}}>Remove</Button
							>
						</td>
					</tr>
				{/each}
			</tbody>
		</Table>
		<Button
			color="success"
			on:click={() => {
				lists = lists.concat({
					name: "",
					email: "",
					mailbox: "",
					grpId: 0,
				});
			}}>Add</Button
		>
	</fieldset>
	<fieldset>
		<legend> Whitelist </legend>
		<Table>
			<thead>
				<tr>
					<th>Email</th>
					<th />
				</tr>
			</thead>
			<tbody>
				{#each allow as ok, i}
					<tr>
						<td>
							<Input
								type="email"
								name="allow"
								id="allow_{i}"
								placeholder={PlaceholderEmail}
								bind:value={ok}
							/>
						</td><td>
							<Button
								color="danger"
								on:click={() => {
									allow.splice(i, 1);
									allow = allow;
								}}>Delete</Button
							>
						</td>
					</tr>
				{/each}
			</tbody>
		</Table>
		<Button
			color="success"
			on:click={() => {
				allow = allow.concat("");
			}}>Add</Button
		>
	</fieldset>
	<fieldset>
		<legend>Other Settings</legend>
		<FormGroup check>
			<Label check>
				<Input type="checkbox" bind:checked={spam} />
				Forward only Emails form registred users
			</Label>
		</FormGroup>
		<FormGroup check>
			<Label check>
				<Input type="checkbox" bind:checked={replyto} />
				Add from email adress as reply-to
			</Label>
		</FormGroup>
		<FormGroup check>
			<Label check>
				<Input type="checkbox" bind:checked={addpagename} />
				Add page name to subject
			</Label>
		</FormGroup>
	</fieldset>
</form>
