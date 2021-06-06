<script lang="ts">
  import { Group, List, PlaceholderEmail } from "./interfaces";
  import { Input, Button, Table } from "sveltestrap";

  export let lists: List[];
  export let groups: Group[];
</script>

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
            name="lists[{i}][name]"
            id="name_{i}"
            placeholder="Name"
            bind:value={list.name}
          />
        </td><td>
          <Input
            type="email"
            name="lists[{i}][email]"
            id="email_{i}"
            placeholder={PlaceholderEmail}
            bind:value={list.email}
          />
        </td><td>
          <Input
            type="text"
            name="lists[{i}][mailbox]"
            id="mailbox_{i}"
            placeholder="Folder"
            bind:value={list.mailbox}
          />
        </td><td>
          <Input
            type="select"
            name="lists[{i}][grpId]"
            id="grpId_{i}"
            bind:value={list.grpId}
          >
            {#each groups as group}
              <option value={group.id} selected={list.grpId === group.id}>
                {group.name}
              </option>
            {/each}
          </Input>
        </td><td>
          <Button
            color="danger"
            on:click={(e) => {
              lists.splice(i, 1);
              lists = lists;
              e.preventDefault();
            }}>Remove</Button
          >
        </td>
      </tr>
    {/each}
  </tbody>
</Table>
<Button
  color="success"
  on:click={(e) => {
    lists = lists.concat({
      name: "",
      email: "",
      mailbox: "",
      grpId: 0,
    });
    e.preventDefault();
  }}>Add</Button
>
