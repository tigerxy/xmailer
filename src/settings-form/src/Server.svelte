<script lang="ts">
  import type { Server, SSLOption } from "./interfaces";
  import { FormGroup, Input, Label, Col, Row } from "sveltestrap";

  export let server: Server;
  export let name: string;
  export let sslOptions: Array<SSLOption>;
  let nameL = name.toLowerCase();
</script>

<legend>{name}</legend>
<Row>
  <Col md="6">
    <FormGroup>
      <Label class="control-label" for="host">{name} Host</Label>
      <Input
        type="text"
        name="{nameL}_host"
        id="host"
        placeholder="Host"
        bind:value={server.host}
      />
    </FormGroup>
  </Col>
  <Col md="3">
    <FormGroup>
      <Label class="control-label" for="ssl">{name} SSL</Label>
      <Input type="select" name="{nameL}_ssl" id="ssl" bind:value={server.ssl}>
        {#each sslOptions as option (option.id)}
          <option value={option.id} selected={server.ssl === option.id}>
            {option.description}
          </option>
        {/each}
      </Input>
    </FormGroup>
  </Col>
  <Col md="3">
    <FormGroup>
      <Label class="control-label" for="port">{name} Port</Label>
      <Input
        type="number"
        name="{nameL}_port"
        id="port"
        placeholder=""
        bind:value={server.port}
      />
    </FormGroup>
  </Col>
</Row>
<Row>
  <Col md="6">
    <FormGroup>
      <Label class="control-label" for="user">{name} User</Label>
      <Input
        type="email"
        name="{nameL}_user"
        id="user"
        placeholder="Username"
        bind:value={server.user}
      />
    </FormGroup>
  </Col>
  <Col md="6">
    <FormGroup>
      <Label class="control-label" for="password">{name} Password</Label>
      <Input
        type="password"
        name="{nameL}_password"
        id="password"
        placeholder="******"
        bind:value={server.password}
      />
    </FormGroup>
  </Col>
</Row>
