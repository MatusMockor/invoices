<template>
  <div>
    <div class="space-y-3">
      <div v-for="note in notes" :key="note.id" class="p-3 rounded border border-gray-200 dark:border-gray-700">
        <div class="text-sm text-gray-500 dark:text-gray-400">{{ formatDate(note.created_at) }}</div>
        <div class="mt-1 whitespace-pre-line">{{ note.body }}</div>
        <button class="mt-2 text-red-600 text-sm" @click="remove(note.id)">Delete</button>
      </div>
    </div>

    <div class="mt-4">
      <textarea v-model="body" rows="3" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600" placeholder="Add a note..."></textarea>
      <div class="flex justify-end mt-2">
        <button @click="add" class="px-3 py-2 bg-indigo-600 text-white rounded">Add Note</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ContactNotes',
  props: {
    noteableType: { type: String, required: true },
    noteableId: { type: [String, Number], required: true }
  },
  data() {
    return {
      notes: [],
      body: ''
    }
  },
  mounted() {
    this.fetchNotes();
  },
  methods: {
    async fetchNotes() {
      const params = new URLSearchParams({
        noteable_type: this.noteableType,
        noteable_id: this.noteableId
      });
      const res = await fetch(`/notes?${params.toString()}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
      if (res.ok) {
        this.notes = await res.json();
      }
    },
    async add() {
      if (!this.body.trim()) return;
      const res = await fetch('/notes', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
          noteable_type: this.noteableType,
          noteable_id: this.noteableId,
          body: this.body
        })
      });
      if (res.ok) {
        this.body = '';
        await this.fetchNotes();
      }
    },
    async remove(id) {
      const res = await fetch(`/notes/${id}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
      });
      if (res.ok) {
        this.notes = this.notes.filter(n => n.id !== id);
      }
    },
    formatDate(d) {
      try { return new Date(d).toLocaleString(); } catch(e) { return d; }
    }
  }
}
</script>
