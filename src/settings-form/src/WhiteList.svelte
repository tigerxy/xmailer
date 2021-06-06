<script lang="ts">
  import { PlaceholderEmail } from "./interfaces";
  import { Input, Button, Table } from "sveltestrap";

  export let allow: string[];
</script>

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
            name="allow[{i}]"
            id="allow_{i}"
            placeholder={PlaceholderEmail}
            bind:value={ok}
          />
        </td><td>
          <Button
            color="danger"
            on:click={(e) => {
              allow.splice(i, 1);
              allow = allow;
              e.preventDefault();
            }}>Delete</Button
          >
        </td>
      </tr>
    {/each}
  </tbody>
</Table>
<Button
  color="success"
  on:click={(e) => {
    allow = allow.concat("");
    e.preventDefault();
  }}>Add</Button
>
