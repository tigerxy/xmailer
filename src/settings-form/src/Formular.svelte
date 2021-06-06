<script lang="ts">
  import { FormGroup, Input, Label } from "sveltestrap";
  import type { Group, Settings, SSLOptions } from "./interfaces";
  import WhiteList from "./WhiteList.svelte";
  import MailingLists from "./MailingLists.svelte";
  import Server from "./Server.svelte";

  export let settings: Settings;
  export let sslOptions: SSLOptions;
  export let userAttributes: string[];
  export let groups: Group[];

  let { imap: imapSslOptions, smtp: smtpSslOptions } = sslOptions;

  $: console.log(JSON.stringify(settings));
</script>

<fieldset>
  <Server bind:server={settings.imap} name="Imap" sslOptions={imapSslOptions} />
</fieldset>
<fieldset>
  <Server bind:server={settings.smtp} name="Smtp" sslOptions={smtpSslOptions} />
</fieldset>
<fieldset>
  <MailingLists bind:lists={settings.lists} bind:groups />
</fieldset>
<fieldset>
  <WhiteList bind:allow={settings.allow} />
</fieldset>
<fieldset>
  <legend>Mail Footer</legend>
  <FormGroup>
    <Label for="footer_plain">Plain</Label>
    <Input
      type="textarea"
      name="footer[plain]"
      id="footer_plain"
      bind:value={settings.footer.plain}
    />
  </FormGroup>
  <FormGroup>
    <Label for="footer_html">HTML</Label>
    <Input
      type="textarea"
      name="footer[html]"
      id="footer_html"
      bind:value={settings.footer.html}
    />
  </FormGroup>
</fieldset>
<fieldset>
  <legend>Other Settings</legend>
  <FormGroup>
    <Label for="userAttribute">User attribute for filtering</Label>
    <Input
      type="select"
      name="userAttribute"
      id="userAttribute"
      bind:value={settings.userAttribute}
    >
      {#each userAttributes as attribute}
        <option
          value={attribute}
          selected={settings.userAttribute === attribute}
        >
          {attribute}
        </option>
      {/each}
    </Input>
  </FormGroup>
  <FormGroup check>
    <Label check>
      <Input
        type="checkbox"
        name="spam"
        id="spam"
        bind:checked={settings.spam}
        value={settings.spam ? "1" : "0"}
      />
      Forward only Emails form registred users
    </Label>
  </FormGroup>
  <FormGroup check>
    <Label check>
      <Input
        type="checkbox"
        name="replyTo"
        id="replyTo"
        bind:checked={settings.replyTo}
        value={settings.replyTo ? "1" : "0"}
      />
      Add from email adress as reply-to
    </Label>
  </FormGroup>
  <FormGroup check>
    <Label check>
      <Input
        type="checkbox"
        name="addPageName"
        id="addPageName"
        bind:checked={settings.addPageName}
        value={settings.addPageName ? "1" : "0"}
      />
      Add page name to subject
    </Label>
  </FormGroup>
</fieldset>
